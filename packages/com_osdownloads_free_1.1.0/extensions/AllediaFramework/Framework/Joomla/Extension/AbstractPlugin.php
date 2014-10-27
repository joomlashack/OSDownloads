<?php
/**
 * @package   AllediaFramework
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2014 Alledia.com, All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace Alledia\Framework\Joomla\Extension;

use Alledia\Framework\Factory;

defined('_JEXEC') or die();

jimport('joomla.plugin.plugin');

abstract class AbstractPlugin extends \JPlugin
{
    /**
     * Alledia Extension instance
     *
     * @var object
     */
    protected $extension;

    /**
     * Library namespace
     *
     * @var string
     */
    protected $namespace;

    /**
     * Class constructor that instantiate the pro library, if installed
     *
     * @param   object  &$subject  The object to observe
     * @param   array   $config    An optional associative array of configuration settings.
     *                             Recognized key values include 'name', 'group', 'params', 'language'
     *                             (this list is not meant to be comprehensive).
     */
    public function __construct(&$subject, $config = array())
    {
        parent::__construct($subject, $config);
    }

    /**
     * Method used to load the extension data. It is not on the constructor
     * because this way we can avoid to load the data if the plugin
     * will not be used.
     *
     * @return void
     */
    protected function init()
    {
        $this->laodExtension();

        // Load the libraries, if existent
        $this->extension->loadLibrary();

        $this->loadLanguage();
    }

    /**
     * Method to load the language files
     *
     * @return void
     */
    public function loadLanguage($extension = '', $basePath = JPATH_ADMINISTRATOR)
    {
        parent::loadLanguage($extension, $basePath);

        $languagePath = JPATH_SITE . '/plugins/' . $this->_type . '/' . $this->_name;
        $languageFile = 'plg_' . $this->_name . '_' . $this->_type . '.sys';

        $language = \JFactory::getLanguage();
        $language->load($languageFile, $languagePath);
    }

    /**
     * Method to load the extension data
     * @return void
     */
    protected function laodExtension()
    {
        if (!isset($this->extension)) {
            $this->extension = Factory::getExtension($this->namespace, 'plugin', $this->_type);
        }
    }

    /**
     * Check if this extension is licensed as pro
     *
     * @return boolean True for pro version
     */
    protected function isPro()
    {
        return $this->extension->isPro();
    }
}
