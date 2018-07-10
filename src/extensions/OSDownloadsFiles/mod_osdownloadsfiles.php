<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2018 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

use Alledia\OSDownloads\Free\Joomla\Component\Site as FreeComponentSite;
use Alledia\OSDownloads\Free\Joomla\Module\File as FreeFileModule;
use Alledia\OSDownloads\Pro\Joomla\Module\File as ProFileModule;
use Alledia\Framework\Joomla\Extension\Helper as ExtensionHelper;

defined('_JEXEC') or die;

include_once JPATH_ADMINISTRATOR . '/components/com_osdownloads/include.php';

if (defined('OSDOWNLOADS_LOADED')) {
    // Load the OSDownloads extension
    ExtensionHelper::loadLibrary('com_osdownloads');

    $osdownloads = FreeComponentSite::getInstance();

    if ($osdownloads->isPro()) {
        $osdownloadsModule = new ProFileModule('OSDownloadsFiles', $module);
    } else {
        $osdownloadsModule = new FreeFileModule('OSDownloadsFiles', $module);
    }

    $osdownloadsModule->init();
}
