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

use Alledia\OSDownloads\Factory;

defined('_JEXEC') or die();

if (!defined('OSDOWNLOADS_LOADED')) {
    $includePath = JPATH_ADMINISTRATOR . '/components/com_osdownloads/include.php';
    if (is_file($includePath)) {
        require_once $includePath;
    }
}

if (defined('OSDOWNLOADS_LOADED')) {
    $pluginClass = sprintf(
        '\\Alledia\\OSDownloads\\%s\\Joomla\\Plugin\\Content',
        Factory::getExtension()->isPro() ? 'Pro' : 'Free'
    );

    class_alias($pluginClass, 'PlgContentOsdownloads');
}
