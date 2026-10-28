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

// Extension files exit unless loaded from within Joomla
define('_JEXEC', 1);

define('JPATH_SITE', __DIR__ . '/Support/Data/site');

define('OSDOWNLOADS_LIBRARY', dirname(__DIR__) . '/src/admin/library');

/*
 * Pimple comes from ShackFramework at runtime, so load it from a framework checkout.
 * Use SHACKFRAMEWORK_PATH, then project.ShackFramework.path from build.properties,
 * then from ShackBuilder's global.properties, the same as the build.
 */
$frameworkPath = getenv('SHACKFRAMEWORK_PATH') ?: null;
if ($frameworkPath === null) {
    $properties = [];
    if (is_file(dirname(__DIR__) . '/build.properties')) {
        $properties = parse_ini_file(dirname(__DIR__) . '/build.properties', false, INI_SCANNER_RAW) ?: [];
    }

    $globalProperties = rtrim($properties['builder.path'] ?? '', '/') . '/global.properties';
    if (isset($properties['builder.path']) && is_file($globalProperties)) {
        $properties += parse_ini_file($globalProperties, false, INI_SCANNER_RAW) ?: [];
    }

    $frameworkPath = $properties['project.ShackFramework.path'] ?? null;
}

$frameworkAutoload = rtrim((string)$frameworkPath, '/') . '/src/vendor/autoload.php';
if ($frameworkPath === null || is_file($frameworkAutoload) == false) {
    fwrite(
        STDERR,
        sprintf(
            "ShackFramework vendor autoloader not found at %s\n"
            . "Set SHACKFRAMEWORK_PATH or project.ShackFramework.path in build.properties\n",
            $frameworkAutoload
        )
    );
    exit(1);
}

require_once $frameworkAutoload;
