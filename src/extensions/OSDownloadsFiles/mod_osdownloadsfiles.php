<?php
/**
 * @package   OSDownloads
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2014 Alledia.com, All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */
defined('_JEXEC') or die;

use Alledia\Framework\Joomla\Extension\Helper as ExtensionHelper;
use Alledia\OSDownloads\Free\Joomla\Component\Site as FreeComponentSite;

require_once JPATH_ADMINISTRATOR . '/components/com_osdownloads/include.php';
require_once __DIR__ . '/helper.php';

// Load the OSDownloads extension
ExtensionHelper::loadLibrary('com_osdownloads');
$extension = FreeComponentSite::getInstance();

$list = ModOSDownloadsFilesHelper::getList($params);
require JModuleHelper::getLayoutPath('mod_osdownloadsfiles', $params->get('layout', 'default'));
