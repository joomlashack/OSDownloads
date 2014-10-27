<?php
/**
 * @package   OSSystem
 * @contact   www.ostraining.com, support@ostraining.com
 * @copyright 2013-2014 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die();

define('OSSYSTEM_PLUGIN_PATH', __DIR__);

// Alledia Framework
if (!defined('ALLEDIA_FRAMEWORK_LOADED')) {
    $allediaFrameworkPath = JPATH_SITE . '/libraries/allediaframework/include.php';

    if (!file_exists($allediaFrameworkPath)) {
        throw new Exception('Alledia framework not found [OSSystem]');
    }

    require_once $allediaFrameworkPath;
}

if (!class_exists('OSSystemHelper')) {
    require_once 'helper.php';
}
