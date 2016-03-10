<?php
/**
 * @package   AllediaInstaller
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2015 Alledia.com, All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

use Alledia\Installer\AutoLoader;

defined('_JEXEC') or die();

// Setup autoloaded libraries
if (!class_exists('\\Alledia\\Installer\\AutoLoader')) {
    require_once __DIR__ . '/AutoLoader.php';
}

Autoloader::register('Alledia\\Installer', __DIR__);
