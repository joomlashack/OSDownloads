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

use Alledia\OSDownloads\Factory;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die();
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace


$includePath = JPATH_ADMINISTRATOR . '/components/com_osdownloads/include.php';
if (is_file($includePath) && include $includePath) {
    if (Factory::getExtension()->isPro()) {
        class_alias('\\Alledia\\OSDownloads\\Pro\\Joomla\\Plugin\\Content', 'DownloadsPlugin');

    } else {
        class_alias('\\Alledia\\OSDownloads\\Free\\Joomla\\Plugin\\Content', 'DownloadsPlugin');
    }

    class PlgContentOsdownloads extends DownloadsPlugin
    {

    }
}
