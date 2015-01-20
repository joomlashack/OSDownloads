<?php
// Here you can initialize variables that will be available to your tests
$configPath = __DIR__ . '/config.php';
if (!file_exists($configPath)) {
    throw new Exception('Local configuration was not found: ' . $configPath);
}
require_once $configPath;

$pathToJoomla = $testPaths['joomla'];
if (!is_dir($pathToJoomla)) {
    throw new Exception('Could not find the Joomla folder: ' . $pathToJoomla);
}
$pathToJoomla = realpath($pathToJoomla);

// Load a minimal Joomla framework
define('_JEXEC', 1);

if (!defined('JPATH_BASE')) {
    define('JPATH_BASE', realpath($pathToJoomla));
}
require_once JPATH_BASE . '/includes/defines.php';

require_once JPATH_BASE . '/includes/framework.php';

// Copied from /includes/framework.php
@ini_set('magic_quotes_runtime', 0);
@ini_set('zend.ze1_compatibility_mode', '0');

require_once JPATH_LIBRARIES . '/import.php';

error_reporting(E_ALL & ~E_STRICT);
ini_set('display_errors', 1);

// Force library to be in JError legacy mode
JError::$legacy = true;
JError::setErrorHandling(E_NOTICE, 'message');
JError::setErrorHandling(E_WARNING, 'message');
JError::setErrorHandling(E_ERROR, 'message');

jimport('joomla.application.menu');
jimport('joomla.environment.uri');
jimport('joomla.utilities.utility');
jimport('joomla.event.dispatcher');
jimport('joomla.utilities.arrayhelper');

// Bootstrap the CMS libraries.
if (!defined('JPATH_PLATFORM')) {
    define('JPATH_PLATFORM', JPATH_BASE . '/libraries');
}
if (!defined('JDEBUG')) {
    define('JDEBUG', false);
}
require_once JPATH_LIBRARIES.'/cms.php';

// Load the configuration
require_once JPATH_CONFIGURATION . '/configuration.php';

// Instantiate some needed objects
JFactory::getApplication('site');

// Bootstrap OSDownloads
define('OSDOWNLOADS_SRC', realpath(__DIR__ . '/../../src'));
if (!is_dir(OSDOWNLOADS_SRC)) {
    throw new Exception('Could not find the OSDownloads folder: ' . OSDOWNLOADS_SRC);
}

// Specialized initialisation for OSDownloads testing
define('OSDOWNLOADS_LOADED', 1);
define('OSDOWNLOADS_ADMIN', OSDOWNLOADS_SRC . '/admin');
define('OSDOWNLOADS_SITE', OSDOWNLOADS_SRC . '/site');
define('OSDOWNLOADS_MEDIA', OSDOWNLOADS_SRC . '/media');
define('OSDOWNLOADS_LIBRARY', OSDOWNLOADS_ADMIN . '/library');

// Setup OSDownloads
require_once OSDOWNLOADS_ADMIN . '/include.php';

// Load the component libraries
$extension = Alledia\Framework\Factory::getExtension('OSDownloads', 'component');
$extension->loadLibrary();
