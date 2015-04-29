<?php
/**
 * @package   OSDownloads
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2015 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die();

use Alledia\Framework\Joomla\Extension\Helper as ExtensionHelper;

// Alledia Framework
if (!defined('ALLEDIA_FRAMEWORK_LOADED')) {
    $allediaFrameworkPath = JPATH_SITE . '/libraries/allediaframework/include.php';

    if (file_exists($allediaFrameworkPath)) {
        require_once $allediaFrameworkPath;
    } else {
        JFactory::getApplication()
            ->enqueueMessage('[OSDownloads] Alledia framework not found', 'error');
    }
}

define('OSDOWNLOADS_MEDIA_PATH', JPATH_SITE . 'media/com_osdownloads');
define('OSDOWNLOADS_MEDIA_URI', JUri::root() . 'media/com_osdownloads');

ExtensionHelper::loadLibrary('com_osdownloads');
