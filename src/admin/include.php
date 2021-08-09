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

use Alledia\Framework\Joomla\Extension\Helper;
use Alledia\OSDownloads\Factory;
use Joomla\CMS\Version;

defined('_JEXEC') or die();

if (!defined('ALLEDIA_FRAMEWORK_LOADED')) {
    $allediaFrameworkPath = JPATH_SITE . '/libraries/allediaframework/include.php';

    if (is_file($allediaFrameworkPath)) {
        require_once $allediaFrameworkPath;

    } else {
        $app = Factory::getApplication();

        if ($app->isClient('administrator')) {
            $app->enqueueMessage('[OSDownloads] Joomlashack framework not found', 'error');
        }
    }
}

if (defined('ALLEDIA_FRAMEWORK_LOADED') && !defined('OSDOWNLOADS_LOADED')) {
    define('OSDOWNLOADS_LOADED', 1);

    define('OSDOWNLOADS_ADMIN', JPATH_ADMINISTRATOR . '/components/com_osdownloads');
    define('OSDOWNLOADS_SITE', JPATH_SITE . '/components/com_osdownloads');
    define('OSDOWNLOADS_LIBRARY', OSDOWNLOADS_ADMIN . '/library');
    define('OSDOWNLOADS_MEDIA', JPATH_SITE . '/media/com_osdownloads');

    require_once OSDOWNLOADS_ADMIN . '/vendor/autoload.php';

    Helper::loadLibrary('com_osdownloads');

    if (version_compare(Version::MAJOR_VERSION, '4', 'lt')) {
        JLoader::register('ContentHelperRoute', JPATH_SITE . '/components/com_content/helpers/route.php');
    }

    switch (Factory::getApplication()->getName()) {
        case 'site':
            Factory::getLanguage()->load('com_osdownloads', OSDOWNLOADS_SITE);
            break;

        case 'administrator':
            Factory::getLanguage()->load('com_osdownloads', OSDOWNLOADS_ADMIN);
            break;
    }
}

return defined('OSDOWNLOADS_LOADED');
