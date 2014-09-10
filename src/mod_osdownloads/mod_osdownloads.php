<?php
/**
 * @version 1.0.0
 * @author Open Source Training (www.ostraining.com)
 * @copyright (C) 2014 Open Source Training
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/
defined('_JEXEC') or die;

require_once dirname(__FILE__) . '/helper.php';

$list = ModOSDownloadsHelper::getList($params);
require JModuleHelper::getLayoutPath('mod_osdownloads');
