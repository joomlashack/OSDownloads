<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2005-2025 Joomlashack.com. All rights reserved
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
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
 * along with OSDownloads.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace Alledia\OSDownloads\Free\Installer;

use Alledia\Installer\AbstractScript as AllediaAbstractScript;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Category;
use Joomla\CMS\Table\Table;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die();

$includePath = realpath(__DIR__ . '/../../Installer/include.php');
require_once $includePath;

// phpcs:enable PSR1.Files.SideEffects
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace

class AbstractScript extends AllediaAbstractScript
{
    /**
     * @inheritDoc
     */
    protected function customPostFlight(string $type, InstallerAdapter $parent): void
    {
        if ($type != 'uninstall') {
            $include = JPATH_ADMINISTRATOR . '/components/com_osdownloads/include.php';
            if (is_file($include) && include $include) {
                $this->checkAndCreateDefaultCategory();
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
                'published >= 0',
            ]);

        $total = (int)$db->setQuery($query)->loadResult();

        if ($total === 0) {
            /** @var Category $category */
            $category = Table::getInstance('category');

            $data = [
                'title'     => 'General',
                'parent_id' => 1,
                'extension' => 'com_osdownloads',
                'published' => 1,
                'language'  => '*',
            ];

            $category->setLocation($data['parent_id'], 'last-child');
            if ($category->bind($data) && $category->check() && $category->store()) {
                $category->rebuildPath();
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
                    ),
                ]);
            $db->setQuery($query)->execute();

            // Rebuild paths where needed
            $query = $db->getQuery(true)
                ->select('id')
                ->from('#__categories')
                ->where([
                    $db->quoteName('extension') . '=' . $db->quote('com_osdownloads'),
                    $db->quoteName('path') . '=' . $db->quote(''),
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
