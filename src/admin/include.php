<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2018 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

use Alledia\Framework\Joomla\Extension;

defined('_JEXEC') or die();

// Alledia Framework
if (!defined('ALLEDIA_FRAMEWORK_LOADED')) {
    $allediaFrameworkPath = JPATH_SITE . '/libraries/allediaframework/include.php';

    if (file_exists($allediaFrameworkPath)) {
        require_once $allediaFrameworkPath;
    } else {
        $app = JFactory::getApplication();

        if ($app->isClient('administrator')) {
            $app->enqueueMessage('[OSDownloads] Alledia framework not found', 'error');
        }
    }
}

if (defined('ALLEDIA_FRAMEWORK_LOADED') && !defined('OSDOWNLOADS_LOADED')) {
    // Define the constant that say OSDownloads is ok to run
    define('OSDOWNLOADS_LOADED', 1);

    define('OSDOWNLOADS_MEDIA_PATH', JPATH_SITE . 'media/com_osdownloads');
    define('OSDOWNLOADS_MEDIA_URI', JUri::root() . 'media/com_osdownloads');

    Extension\Helper::loadLibrary('com_osdownloads');

    require_once JPATH_ADMINISTRATOR . '/components/com_osdownloads/vendor/autoload.php';
}
