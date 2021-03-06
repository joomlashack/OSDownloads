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

defined('_JEXEC') or die();

$includePath = __DIR__ . '/admin/library/Installer/include.php';
if (file_exists($includePath)) {
    require_once $includePath;
} else {
    require_once __DIR__ . '/library/Installer/include.php';
}

use Alledia\Installer\AbstractScript;
use Joomla\Registry\Registry;

class AbstractOSDownloadsInstallerScript extends AbstractScript
{
    /**
     * @param string            $type
     * @param JInstallerAdapter $parent
     *
     * @return void
     * @throws Exception
     */
    public function postFlight($type, $parent)
    {
        try {
            parent::postFlight($type, $parent);

            $this->checkParamStructure();
            $this->checkAndCreateDefaultCategory();
            $this->fixOrderingParamForMenus();
            $this->fixDownloadsViewParams();
            $this->fixItemViewParams();

            if ($type == 'update') {
                $this->moveLayouts();
            }

        } catch (Exception $e) {
            $this->setMessage($e->getMessage(), 'error');

        } catch (Throwable $e) {
            $this->setMessage($e->getMessage(), 'error');
        }

        // To catch any new messages that may have been queued
        $this->showMessages();
    }

    /**
     * @return void
     */
    protected function checkAndCreateDefaultCategory()
    {
        $db = JFactory::getDBO();

        // Make sure we have at least one category
        $query = $db->getQuery(true)
            ->select('count(*)')
            ->from('#__categories')
            ->where(
                array(
                    'extension = ' . $db->quote('com_osdownloads'),
                    'published >= 0'
                )
            );
        $db->setQuery($query);
        $total = (int)$db->loadResult();

        if ($total === 0) {
            /** @var JTableCategory $row */
            $row = JTable::getInstance('category');

            $data = array(
                'title'     => 'General',
                'parent_id' => 1,
                'extension' => 'com_osdownloads',
                'published' => 1,
                'language'  => '*'
            );

            $row->setLocation($data['parent_id'], 'last-child');
            $row->bind($data);
            if ($row->check()) {
                $row->store();
                $row->rebuildPath();
                $this->setMessage(JText::_('COM_OSDOWNLOADS_INSTALL_GENERAL_CATEGORY_CREATED'));
            } else {
                $this->setMessage(JText::_('COM_OSDOWNLOADS_INSTALL_GENERAL_CATEGORY_WARNING'), 'notice');
            }

        } else {
            // Make sure to fix the undefined language
            $query = $db->getQuery(true)
                ->update('#__categories')
                ->set($db->quoteName('language') . '=' . $db->quote('*'))
                ->where(
                    array(
                        $db->quoteName('extension') . ' = ' . $db->quote('com_osdownloads'),
                        sprintf(
                            '(%1$s IS NULL OR %1$s = %2$s)',
                            $db->quoteName('language'),
                            $db->quote('')
                        )
                    )
                );
            $db->setQuery($query)->execute();

            // Rebuild paths where needed
            $query = $db->getQuery(true)
                ->select('id')
                ->from('#__categories')
                ->where(
                    array(
                        $db->quoteName('extension') . '=' . $db->quote('com_osdownloads'),
                        $db->quoteName('path') . '=' . $db->quote('')
                    )
                );

            if ($ids = $db->setQuery($query)->loadColumn()) {
                /** @var JTableCategory $category */
                $category = JTable::getInstance('Category');

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
        require JPATH_SITE . '/administrator/components/com_osdownloads/include.php';

        $db = JFactory::getDbo();

        $query = $db->getQuery(true)
            ->select('link')
            ->select('params')
            ->select('id')
            ->from('#__menu')
            ->where('link = ' . $db->quote('index.php?option=com_osdownloads&view=downloads'));
        $db->setQuery($query);
        $menus = $db->loadObjectList();

        if (!empty($menus)) {
            foreach ($menus as $menu) {
                $params          = @json_decode($menu->params);
                $legacyOrderings = array(
                    'ordering',
                    'name',
                    'downloaded',
                    'created_time',
                    'modified_time'
                );

                if (isset($params->ordering) && in_array($params->ordering, $legacyOrderings)) {
                    $params->ordering = 'doc.' . $params->ordering;
                    $params           = json_encode($params);

                    $query = $db->getQuery(true)
                        ->update('#__menu')
                        ->set('params = ' . $db->quote($params))
                        ->where('id = ' . $menu->id);
                    $db->setQuery($query);
                    $db->execute();
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
        $db = JFactory::getDbo();

        // Look for menu items for Category view
        $query    = $db->getQuery(true)
            ->select(
                array(
                    'id',
                    'params',
                )
            )
            ->from('#__menu')
            ->where('link = ' . $db->quote('index.php?option=com_osdownloads&view=downloads'));
        $menuList = $db->setQuery($query)->loadObjectList();

        if (!empty($menuList)) {
            foreach ($menuList as $menu) {
                $params = json_decode($menu->params);

                // Does it have the old param and multiple categories selected?
                if (isset($params->category_id) && is_array($params->category_id) && !empty($params->category_id)) {
                    // Get the first category for the new param. If empty, use 0, the root category
                    $id = (int)$params->category_id[0];

                    unset($params->category_id);

                    // Update the link adding the selected category
                    $link = 'index.php?option=com_osdownloads&view=downloads&id=' . $id;

                    $query = $db->getQuery(true)
                        ->update('#__menu')
                        ->where('id = ' . (int)$menu->id)
                        ->set('link = ' . $db->quote($link))
                        ->set('params = ' . $db->quote(json_encode($params)));
                    $db->setQuery($query)->execute();

                    try {
                        JFactory::getApplication()->enqueueMessage(
                            sprintf(
                                JText::_('Only one category is allowed for the OSDOwnloads Category Files view. The params for menu item %s were upgraded.'),
                                $menu->id
                            ),
                            'warning'
                        );

                    } catch (Exception $e) {
                        // Fail silently
                    }
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
        $db = JFactory::getDbo();

        // Look for menu items for Item view
        $query    = $db->getQuery(true)
            ->select(
                array(
                    'id',
                    'params',
                )
            )
            ->from('#__menu')
            ->where('link = ' . $db->quote('index.php?option=com_osdownloads&view=item'));
        $menuList = $db->setQuery($query)->loadObjectList();

        if (!empty($menuList)) {
            foreach ($menuList as $menu) {
                $params = json_decode($menu->params);

                // Does it have the old param?
                if (isset($params->document_id) && !empty($params->document_id)) {
                    $id = (int)$params->document_id;

                    unset($params->document_id);

                    // Update the link adding the selected category
                    $link = 'index.php?option=com_osdownloads&view=item&id=' . $id;

                    $query = $db->getQuery(true)
                        ->update('#__menu')
                        ->where('id = ' . (int)$menu->id)
                        ->set('link = ' . $db->quote($link))
                        ->set('params = ' . $db->quote(json_encode($params)));
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
        /** @var JTableExtension $table */
        $table = JTable::getInstance('Extension');
        $table->load(array('element' => 'com_osdownloads', 'type' => 'component'));

        $data   = json_decode($table->params);
        $params = new Registry($data);

        $parameterMap = $this->getParameterChangeMap();
        foreach ($parameterMap as $oldKey => $newKey) {
            if ($value = $params->get($oldKey)) {
                $params->set($newKey, $value);
                $params->set($oldKey, null);
            }
        }

        if ($data != $params->toObject()) {
            $table->params = $params->toString();
            $table->store();
        }
    }

    /**
     * Return array mapping old Parameter kesy to new
     *
     * @return array
     */
    protected function getParameterChangeMap()
    {
        $parameterMap = array(
            'connect_mailchimp' => 'mailinglist.mailchimp.enable',
            'mailchimp_api'     => 'mailinglist.mailchimp.api',
            'list_id'           => 'mailinglist.mailchimp.list_id'
        );

        return $parameterMap;
    }

    /**
     * Update layout overrides for new location (free and pro)
     *
     * @since v1.13.14
     */
    public function moveLayouts()
    {
        $renames = array(
            'download_button' => 'download',
            'social_download' => 'social'
        );

        $files = JFolder::files(
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
                JFolder::create($dir);
            }

            switch ($layout) {
                case 'download_button':
                    $script = file_get_contents($file);
                    $script = str_replace("'social_download'", "'buttons.social'", $script);
                    JFile::write($file, $script);
                    // Fall through

                default:
                    JFile::move($file, $newPath);
                    break;
            }
        }
    }
}
