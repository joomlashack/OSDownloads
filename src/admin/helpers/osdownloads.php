<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2021 Joomlashack.com. All rights reserved
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

use Alledia\OSDownloads\Factory;
use Alledia\OSDownloads\Free\Helper\Helper as FreeHelper;
use Alledia\OSDownloads\Pro\Helper\Helper as ProHelper;

defined('_JEXEC') or die();

$include = JPATH_ADMINISTRATOR . '/components/com_osdownloads/include.php';
if (is_file($include) && include $include) {
    if (Factory::getExtension()->isPro()) {
        class OsdownloadsHelper extends ProHelper
        {
        }

    } else {
        class OsdownloadsHelper extends FreeHelper
        {
        }
    }
}
