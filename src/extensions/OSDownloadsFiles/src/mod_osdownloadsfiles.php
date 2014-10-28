<?php
/**
 * @package   OSDownloads
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2014 Alledia.com, All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */
defined('_JEXEC') or die;

require_once __DIR__ . '/helper.php';

$list = ModOSDownloadsFilesHelper::getList($params);
require JModuleHelper::getLayoutPath('mod_osdownloadsfiles');
