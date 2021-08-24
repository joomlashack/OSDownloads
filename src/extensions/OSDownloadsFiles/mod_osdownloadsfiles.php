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

use Alledia\Framework\Joomla\Extension\Helper as ExtensionHelper;
use Alledia\OSDownloads\Free\Joomla\Component\Site as FreeComponentSite;
use Alledia\OSDownloads\Free\Joomla\Module\File as FreeFileModule;
use Alledia\OSDownloads\Pro\Joomla\Module\File as ProFileModule;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Language\Language;
use Joomla\Registry\Registry;

defined('_JEXEC') or die();

/**
 * @var stdClass        $module
 * @var string[]        $attribs
 * @var string[]        $chrome
 * @var SiteApplication $app
 * @var string          $scope
 * @var Registry        $params
 * @var string          $template
 * @var string          $path
 * @var Language        $lang
 * @var string          $coreLanguageDirectory
 * @var string          $extensionLanguageDirectory
 * @var string[]        $langPaths
 * @var string          $content
 */

$includePath = JPATH_ADMINISTRATOR . '/components/com_osdownloads/include.php';
if (is_file($includePath) && include $includePath) {
    ExtensionHelper::loadLibrary('com_osdownloads');

    $osdownloads = FreeComponentSite::getInstance();

    if ($osdownloads->isPro()) {
        $osdownloadsModule = new ProFileModule('OSDownloadsFiles', $module);
    } else {
        $osdownloadsModule = new FreeFileModule('OSDownloadsFiles', $module);
    }

    $osdownloadsModule->init();
}
