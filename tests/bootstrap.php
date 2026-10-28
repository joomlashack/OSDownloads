<?php

/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2026 Joomlashack.com. All rights reserved
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

// phpcs:disable PSR1.Files.SideEffects

use Tests\Support\Paths;

// Extension files exit unless loaded from within Joomla
define('_JEXEC', 1);

define('JPATH_SITE', __DIR__ . '/Support/Data/site');

define('OSDOWNLOADS_LIBRARY', dirname(__DIR__) . '/src/admin/library');

require_once __DIR__ . '/Support/Paths.php';

// Pimple comes from ShackFramework at runtime, so load it from a framework checkout
require_once Paths::framework() . '/src/vendor/autoload.php';
