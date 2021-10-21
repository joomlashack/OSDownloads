<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2005-2021 Joomlashack.com. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 *
 * This file is part of OSDownloads.
 *
 * OSDownloads is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * OSDownloads is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OSDownloads.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Alledia\OSDownloads\Free\Installer;

use Alledia\Installer\TraitInstallerCheck;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Category;
use Joomla\CMS\Table\Extension;
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;

defined('_JEXEC') or die();

$includePath = realpath(__DIR__ . '/../../../library/Installer/include.php');

require_once $includePath;

class AbstractScript extends \Alledia\Installer\AbstractScript
{
    use TraitInstallerCheck;

    /**
     * @inheritDoc
     */
    public function __construct($parent)
    {
        if ($this->checkInheritance($parent)) {
            parent::__construct($parent);
        }
    }

    /**
     * @inheritDoc
     */
    protected function customPostFlight($type, $parent)
    {
        if ($type != 'uninstall') {
            $include = JPATH_ADMINISTRATOR . '/components/com_osdownloads/include.php';
            if (is_file($include) && include $include) {
                $this->checkParamStructure();
                $this->checkAndCreateDefaultCategory();
                $this->fixOrderingParamForMenus();
                $this->fixDownloadsViewParams();
                $this->fixItemViewParams();
                $this->fixDatabase();
                $this->clearProData();

                if ($type == 'update') {
                    $this->moveLayouts();
                }
            }
        }
    }

    /**
     * @return void
     */
    protected function checkAndCreateDefaultCategory()
    {
        $db = $this->dbo;

        // Make sure we have at least one category
        $query = $db->getQuery(true)
            ->select('count(*)')
            ->from('#__categories')
            ->where([
                'extension = ' . $db->quote('com_osdownloads'),
                'published >= 0'
            ]);

        $total = (int)$db->setQuery($query)->loadResult();

        if ($total === 0) {
            /** @var Category $row */
            $row = Table::getInstance('category');

            $data = [
                'title'     => 'General',
                'parent_id' => 1,
                'extension' => 'com_osdownloads',
                'published' => 1,
                'language'  => '*'
            ];

            $row->setLocation($data['parent_id'], 'last-child');
            $row->bind($data);
            if ($row->check()) {
                $row->store();
                $row->rebuildPath();
                $this->sendMessage(Text::_('COM_OSDOWNLOADS_INSTALL_GENERAL_CATEGORY_CREATED'));

            } else {
                $this->sendMessage(Text::_('COM_OSDOWNLOADS_INSTALL_GENERAL_CATEGORY_WARNING'), 'notice');
            }

        } else {
            // Make sure to fix the undefined language
            $query = $db->getQuery(true)
                ->update('#__categories')
                ->set($db->quoteName('language') . '=' . $db->quote('*'))
                ->where([
                    $db->quoteName('extension') . ' = ' . $db->quote('com_osdownloads'),
                    sprintf(
                        '(%1$s IS NULL OR %1$s = %2$s)',
                        $db->quoteName('language'),
                        $db->quote('')
                    )
                ]);
            $db->setQuery($query)->execute();

            // Rebuild paths where needed
            $query = $db->getQuery(true)
                ->select('id')
                ->from('#__categories')
                ->where([
                    $db->quoteName('extension') . '=' . $db->quote('com_osdownloads'),
                    $db->quoteName('path') . '=' . $db->quote('')
                ]);

            if ($ids = $db->setQuery($query)->loadColumn()) {
                /** @var Category $category */
                $category = Table::getInstance('Category');

                foreach ($ids as $id) {
                    $category->rebuildPath($id);
                }
            }
        }
    }

    /**
     * Fix old values for the ordering param in menus, adding the table prefix.
     *
     * @return void
     */
    protected function fixOrderingParamForMenus()
    {
        $db = $this->dbo;

        $query = $db->getQuery(true)
            ->select('link')
            ->select('params')
            ->select('id')
            ->from('#__menu')
            ->where('link = ' . $db->quote('index.php?option=com_osdownloads&view=downloads'));
        $db->setQuery($query);
        $menus = $db->loadObjectList();

        if ($menus) {
            foreach ($menus as $menu) {
                $params          = new Registry($menu->params);
                $legacyOrderings = [
                    'ordering',
                    'name',
                    'downloaded',
                    'created_time',
                    'modified_time'
                ];

                $ordering = $params->get('ordering');
                if (in_array($ordering, $legacyOrderings)) {
                    $params->set('ordering', 'doc.' . $ordering);

                    $query = $db->getQuery(true)
                        ->update('#__menu')
                        ->set('params = ' . $db->quote($params->toString()))
                        ->where('id = ' . $menu->id);

                    $db->setQuery($query)->execute();
                }
            }
        }
    }

    /**
     * Detect legacy settings and fix the downloads view params. If legacy data
     * is found, warn the user. We call legacy data, the param category_id with
     * multiple values, in the downloads view. Represented issues for SEF
     * URLs, so we refactored allowing only one category.
     *
     * @return void
     */
    protected function fixDownloadsViewParams()
    {
        $db = $this->dbo;

        // Look for menu items for Category view
        $query = $db->getQuery(true)
            ->select([
                'id',
                'params',
            ])
            ->from('#__menu')
            ->where('link = ' . $db->quote('index.php?option=com_osdownloads&view=downloads'));

        $menuList = $db->setQuery($query)->loadObjectList();
        if ($menuList) {
            foreach ($menuList as $menu) {
                $params = new Registry($menu->params);

                // Does it have the old param and multiple categories selected?
                $categories = (array)$params->get('category_id');
                if (count($categories) > 1) {
                    // Get the first category for the new param. If empty, use 0, the root category
                    $categoryId = (int)array_shift($categories);
                    $params->remove('category_id');

                    // Update the link adding the selected category
                    $link = 'index.php?option=com_osdownloads&view=downloads&id=' . $categoryId;

                    $query = $db->getQuery(true)
                        ->update('#__menu')
                        ->where('id = ' . (int)$menu->id)
                        ->set('link = ' . $db->quote($link))
                        ->set('params = ' . $db->quote($params->toString()));
                    $db->setQuery($query)->execute();

                    $this->sendMessage(
                        Text::sprintf('COM_OSDOWNLOADS_ERROR_INSTALLER_CATEGORY', $menu->id),
                        'warning'
                    );
                }
            }
        }
    }

    /**
     * Detect legacy settings and fix the item view params
     *
     * @return void
     */
    protected function fixItemViewParams()
    {
        $db = $this->dbo;

        // Look for menu items for Item view
        $query = $db->getQuery(true)
            ->select([
                'id',
                'params',
            ])
            ->from('#__menu')
            ->where('link = ' . $db->quote('index.php?option=com_osdownloads&view=item'));

        $menuList = $db->setQuery($query)->loadObjectList();
        if ($menuList) {
            foreach ($menuList as $menu) {
                $params = new Registry($menu->params);

                // Does it have the old param?
                $documentId = (int)$params->get('document_id');
                if ($documentId) {
                    $params->remove('document_id');

                    // Update the link adding the selected category
                    $link = 'index.php?option=com_osdownloads&view=item&id=' . $documentId;

                    $query = $db->getQuery(true)
                        ->update('#__menu')
                        ->where('id = ' . (int)$menu->id)
                        ->set('link = ' . $db->quote($link))
                        ->set('params = ' . $db->quote($params->toString()));
                    $db->setQuery($query)->execute();
                }
            }
        }
    }

    /**
     * Adjust component parameters as needed
     *
     * @TODO: Move to AbstractInstaller script
     */
    protected function checkParamStructure()
    {
        /** @var Extension $table */
        $table = Table::getInstance('Extension');
        $table->load(['element' => 'com_osdownloads', 'type' => 'component']);

        $params  = new Registry($table->get('params'));
        $current = $params->toObject();

        $parameterMap = $this->getParameterChangeMap();
        foreach ($parameterMap as $oldKey => $newKey) {
            if ($value = $params->get($oldKey)) {
                $params->set($newKey, $value);
                $params->remove($oldKey);
            }
        }

        if ($current != $params->toObject()) {
            $table->params = $params->toString();
            $table->store();
        }
    }

    /**
     * Return array mapping old Parameter keys to new
     *
     * @return array
     */
    protected function getParameterChangeMap()
    {
        return [
            'connect_mailchimp' => 'mailinglist.mailchimp.enable',
            'mailchimp_api'     => 'mailinglist.mailchimp.api',
            'list_id'           => 'mailinglist.mailchimp.list_id'
        ];
    }

    /**
     * Update layout overrides for new location (free and pro)
     *
     * @since v1.13.14
     */
    protected function moveLayouts()
    {
        $renames = [
            'download_button' => 'download',
        ];

        $files = Folder::files(
            JPATH_SITE . '/templates',
            sprintf('(%s)\.php', join('|', array_keys($renames))),
            true,
            true
        );

        foreach ($files as $file) {
            $dir     = dirname($file) . '/buttons/';
            $layout  = basename($file, '.php');
            $newPath = $dir . $renames[$layout] . '.php';

            if (!is_dir($dir)) {
                Folder::create($dir);
            }

            File::move($file, $newPath);
        }
    }

    /**
     * Apply any needed database fixes
     */
    protected function fixDatabase()
    {
        /*
         * There is an odd issue in Joomla 4 that reports database errors
         * when there aren't any. Stupid.
         */
        $oldUpdates = Folder::files(
            JPATH_ADMINISTRATOR . '/components/com_osdownloads/sql/updates',
            '1\.4\.9\.sql',
            true,
            true
        );

        foreach ($oldUpdates as $oldUpdate) {
            File::delete($oldUpdate);
        }
    }

    /**
     * Clear out any pro data that may have hung around after a pro install
     *
     * @return void
     */
    protected function clearProData()
    {
        if ($this->getLicense()->isPro()) {
            // only do this with Free installs
            return;
        }

        $db = $this->dbo;

        $context = $db->quoteName('context') . ' LIKE ' . $db->quote('com_osdownloads.%');

        $query = $db->getQuery(true)
            ->delete('#__fields')
            ->where($context);
        $db->setQuery($query)->execute();

        $query = $db->getQuery(true)
            ->delete('#__fields_groups')
            ->where($context);
        $db->setQuery($query)->execute();

        $fieldId = 'field_id NOT IN (SELECT id FROM jos_fields)';

        $db->getQuery(true)
            ->delete('#__fields_values')
            ->where($fieldId);
        $db->setQuery($query)->execute();

        $db->getQuery(true)
            ->delete('#__fields_categories')
            ->where($fieldId);
        $db->setQuery($query)->execute();
    }
}
