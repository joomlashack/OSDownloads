<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2005-2023 Joomlashack.com. All rights reserved
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

namespace Alledia\OSDownloads\Free\Helper;

use Alledia\OSDownloads\Factory;
use ContentHelperRoute;
use Exception;
use Joomla\CMS\Access\Access;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\HTML\Helpers\Sidebar;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die();

// phpcs:enable PSR1.Files.SideEffects

abstract class Helper
{
    /**
     * @param string $vName
     *
     * @return void
     * @throws Exception
     */
    public static function addSubmenu(string $vName): void
    {
        Sidebar::addEntry(
            Text::_('COM_OSDOWNLOADS_SUBMENU_FILES'),
            'index.php?option=com_osdownloads&view=files',
            $vName == 'files'
        );

        Sidebar::addEntry(
            Text::_('COM_OSDOWNLOADS_SUBMENU_CATEGORIES'),
            'index.php?option=com_categories&view=categories&extension=com_osdownloads',
            $vName == 'categories'
        );
        if ($vName == 'categories') {
            ToolbarHelper::title(
                Text::sprintf(
                    'COM_CATEGORIES_CATEGORIES_TITLE',
                    Text::_('COM_OSDOWNLOADS')
                ),
                'osdownloads-categories'
            );
        }

        Sidebar::addEntry(
            Text::_('COM_OSDOWNLOADS_SUBMENU_EMAILS'),
            'index.php?option=com_osdownloads&view=emails',
            $vName == 'emails'
        );

        // Load responsive CSS
        HTMLHelper::_('stylesheet', 'media/jui/css/jquery.searchtools.css');

        static::displayAdminMessages();
    }

    /**
     * @return void
     */
    public static function displayAdminMessages()
    {
        // No messages at this time
    }

    /**
     * Validate an email address. Used this to have the option to accept
     * plus signs in the email validation. The native PHP filter
     * doesn't support + in the email address, which is now allowed.
     *
     * @param ?string $email
     * @param ?bool   $acceptPlusSign
     *
     * @return bool
     */
    public static function validateEmail(?string $email, ?bool $acceptPlusSign = true): bool
    {
        if ($acceptPlusSign) {
            $pattern = '/^([a-z0-9_\-.+])+@([a-z0-9_\-.])+\.([a-z]{2,25})$/i';

            return (bool)preg_match($pattern, $email);
        }

        $valid = filter_var($email, FILTER_VALIDATE_EMAIL);

        return !empty($valid);
    }

    /**
     * Check if the path is a local path and exists. Otherwise, it can mean
     * we have an external URL.
     *
     * @param ?string $path
     *
     * @return bool
     */
    public static function isLocalPath(?string $path): bool
    {
        if (empty($path) || preg_match('#^//|[a-z0-9]+?://#i', $path)) {
            return false;
        }

        // If the file exists, it is a local path
        return File::exists(realpath(JPATH_SITE . '/' . ltrim($path, '/')));
    }

    /**
     * @param string $fileName
     * @param ?bool  $isUpload
     *
     * @return ?string
     */
    public static function getFullPath(string $fileName, bool $isUpload = true): ?string
    {
        $fileName = ltrim($fileName, '/');
        if ($isUpload) {
            return realpath(JPATH_SITE . '/media/com_osdownloads/files/' . $fileName);
        }

        return realpath(JPATH_SITE . '/' . $fileName);
    }

    /**
     * Standard item model amendments
     *
     * @param object $item
     *
     * @return void
     * @throws Exception
     */
    public static function prepareItem(object $item): void
    {
        if (!($item->params instanceof Registry)) {
            $item->params = new Registry($item->params);
        }
        $item->require_agree        = (bool)$item->require_agree;
        $item->agreement_article_id = (int)$item->agreement_article_id;

        if ($item->require_agree && $item->agreement_article_id) {
            $item->agreementLink = Route::_(ContentHelperRoute::getArticleRoute($item->agreement_article_id));

        } else {
            $item->agreementLink = '';
        }

        $item->isLocal  = null;
        $item->realName = null;
        $item->fullPath = null;
        $item->fileSize = null;
        if ($item->file_url) {
            $url = explode('?', $item->file_url);

            $item->isLocal  = Helper::isLocalPath($item->file_url);
            $item->realName = basename(reset($url));

            if ($item->isLocal) {
                $fileFullPath = static::getFullPath($item->file_url, false);
            }

        } else {
            $item->isLocal  = true;
            $item->realName = substr($item->file_path, strpos($item->file_path, '_') + 1);
            $fileFullPath   = static::getFullPath($item->file_path);
        }

        if ($item->isLocal && empty($fileFullPath) == false && is_file($fileFullPath)) {
            $item->fullPath = $fileFullPath;
            $item->fileSize = filesize($fileFullPath);
        }

        PluginHelper::importPlugin('content');

        // Make compatible with content plugins
        $item->text = '';

        Factory::getApplication()->triggerEvent(
            'onContentPrepare',
            ['com_osdownloads.file', &$item, &$item->params, null]
        );

        $prepareEvent = [
            'afterDisplayTitle'    => Factory::getApplication()->triggerEvent(
                'onContentAfterTitle',
                ['com_osdownloads.file', &$item, &$item->params, null]
            ),
            'beforeDisplayContent' => Factory::getApplication()->triggerEvent(
                'onContentBeforeDisplay',
                ['com_osdownloads.file', &$item, &$item->params, null]
            ),
            'afterDisplayContent'  => Factory::getApplication()->triggerEvent(
                'onContentAfterDisplay',
                ['com_osdownloads.file', &$item, &$item->params, null]
            )
        ];

        foreach ($prepareEvent as &$results) {
            $results = trim(join("\n", $results));
        }
        $item->event = (object)$prepareEvent;
    }

    /**
     * @return string[]
     */
    public static function getContexts(): array
    {
        return [];
    }
}
