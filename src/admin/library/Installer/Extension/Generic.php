<?php
/**
 * @package   AllediaInstaller
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2015 Alledia.com, All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace Alledia\Installer\Extension;

defined('_JEXEC') or die();

use JFactory;
use JRegistry;

/**
 * Generic extension class
 */
class Generic
{
    /**
     * The extension namespace
     *
     * @var string
     */
    protected $namespace;

    /**
     * The extension type
     *
     * @var string
     */
    protected $type;

    /**
     * The extension id
     *
     * @var int
     */
    protected $id;

    /**
     * The extension name
     *
     * @var string
     */
    protected $name;

    /**
     * The extension params
     *
     * @var JRegistry
     */
    public $params;

    /**
     * The extension enable state
     *
     * @var bool
     */
    protected $enabled;

    /**
     * The element of the extension
     *
     * @var string
     */
    protected $element;

    /**
     * Base path
     *
     * @var string
     */
    protected $basePath;

    /**
     * The manifest information
     *
     * @var \SimpleXMLElement
     */
    public $manifest;

    /**
     * The config.xml information
     *
     * @var \SimpleXMLElement
     */
    public $config;

    /**
     * Class constructor, set the extension type.
     *
     * @param string $namespace The element of the extension
     * @param string $type      The type of extension
     * @param string $folder    The folder for plugins (only)
     */
    public function __construct($namespace, $type, $folder = '', $basePath = JPATH_SITE)
    {
        $this->type      = $type;
        $this->element   = strtolower($namespace);
        $this->folder    = $folder;
        $this->basePath  = $basePath;
        $this->namespace = $namespace;

        $this->getManifest();

        $this->getDataFromDatabase();
    }

    /**
     * Get information about this extension from the database
     */
    protected function getDataFromDatabase()
    {
        $element = $this->getElementToDb();

        // Load the extension info from database
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select(array(
                $db->qn('extension_id'),
                $db->qn('name'),
                $db->qn('enabled'),
                $db->qn('params')
            ))
            ->from('#__extensions')
            ->where($db->qn('type') . ' = ' . $db->q($this->type))
            ->where($db->qn('element') . ' = ' . $db->q($element));

        if ($this->type === 'plugin') {
            $query->where($db->qn('folder') . ' = ' . $db->q($this->folder));
        }

        $db->setQuery($query);
        $row = $db->loadObject();

        if (is_object($row)) {
            $this->id = $row->extension_id;
            $this->name = $row->name;
            $this->enabled = (bool) $row->enabled;
            $this->params = new JRegistry($row->params);
        } else {
            $this->id = null;
            $this->name = null;
            $this->enabled = false;
            $this->params = new JRegistry;
        }
    }

    /**
     * Check if the extension is enabled
     *
     * @return boolean True for enabled
     */
    public function isEnabled()
    {
        return (bool) $this->enabled;
    }

    /**
     * Get the path for the extension
     *
     * @return string The path
     */
    public function getExtensionPath()
    {
        $basePath = '';

        $folders = array(
            'component' => 'administrator/components/',
            'plugin'    => 'plugins/',
            'template'  => 'templates/',
            'library'   => 'libraries/',
            'cli'       => 'cli/',
            'module'    => 'modules/'
        );

        $basePath = $this->basePath . '/' . $folders[$this->type];

        switch ($this->type) {
            case 'plugin':
                $basePath .= $this->folder . '/';
                break;

            case 'module':
                if (!preg_match('/^mod_/', $this->element)) {
                    $basePath .= 'mod_';
                }
                break;

            case 'component':
                if (!preg_match('/^com_/', $this->element)) {
                    $basePath .= 'com_';
                }
                break;
        }

        $basePath .= $this->element;

        return $basePath;
    }

    /**
     * Get the full element
     *
     * @return string The full element
     */
    public function getFullElement()
    {
        $prefixes = array(
            'component' => 'com',
            'plugin'    => 'plg',
            'template'  => 'tpl',
            'library'   => 'lib',
            'cli'       => 'cli',
            'module'    => 'mod'
        );

        $fullElement = $prefixes[$this->type];

        if ($this->type === 'plugin') {
            $fullElement .= '_' . $this->folder;
        }

        $fullElement .= '_' . $this->element;

        return $fullElement;
    }

    /**
     * Get the element to match the database records.
     * Only components and modules have the prefix.
     *
     * @return string The element
     */
    public function getElementToDb()
    {
        $prefixes = array(
            'component' => 'com_',
            'module'    => 'mod_'
        );

        $fullElement = '';
        if (array_key_exists($this->type, $prefixes)) {
            if (!preg_match('/^' . $prefixes[$this->type] . '/', $this->element)) {
                $fullElement = $prefixes[$this->type];
            }
        }

        $fullElement .= $this->element;

        return $fullElement;
    }

    /**
     * Get manifest path for this extension
     *
     * @return string
     */
    public function getManifestPath()
    {
        $extensionPath = $this->getExtensionPath();

        $path = $extensionPath . "/{$this->element}.xml";

        if (!file_exists($path)) {
            $path = $extensionPath . "/{$this->getElementToDb()}.xml";
        }

        return $path;
    }

    /**
     * Get extension information
     *
     * @param bool $force If true, force to load the manifest, ignoring the cached one
     *
     * @return JRegistry
     */
    public function getManifest($force = false)
    {
        if (!isset($this->manifest) || $force) {
            $path = $this->getManifestPath();

            $xml = simplexml_load_file($path);

            $this->manifest = (object) json_decode(json_encode($xml));
        }

        return $this->manifest;
    }

    /**
     * Get extension config file
     *
     * @param bool $force Force to reload the config file
     *
     * @return JRegistry
     */
    public function getConfig($force = false)
    {
        if (!isset($this->config) || $force) {
            $path = $this->getExtensionPath() . '/config.xml';

            if (file_exists($path)) {
                $this->config = simplexml_load_file($path);
            }
        }

        return $this->config;
    }

    /**
     * Returns the update URL from database
     *
     * @return string
     */
    public function getUpdateURL()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('sites.location')
            ->from('#__update_sites AS sites')
            ->join('LEFT', '#__update_sites_extensions AS extensions ON (sites.update_site_id = extensions.update_site_id)')
            ->where('extensions.extension_id = ' . $this->id);
        $db->setQuery($query);
        $row = $db->loadResult();

        return $row;
    }

    /**
     * Set the update URL
     *
     * @param string $url
     */
    public function setUpdateURL($url)
    {
        $db = JFactory::getDbo();

        // Get the update site id
        $join = $db->qn('#__update_sites_extensions') . ' AS extensions '
            . 'ON (sites.update_site_id = extensions.update_site_id)';
        $query = $db->getQuery(true)
            ->select('sites.update_site_id')
            ->from($db->qn('#__update_sites') . ' AS sites')
            ->join('LEFT', $join)
            ->where('extensions.extension_id = ' . $this->id);
        $db->setQuery($query);
        $siteId = (int) $db->loadResult();

        if (!empty($siteId)) {
            $query = $db->getQuery(true)
                ->update($db->qn('#__update_sites'))
                ->set($db->qn('location') . ' = ' . $db->q($url))
                ->where($db->qn('update_site_id') . ' = ' . $siteId);
            $db->setQuery($query);
            $db->execute();
        }
    }

    /**
     * Store the params on the database
     *
     * @return void
     */
    public function storeParams()
    {
        $db     = JFactory::getDbo();
        $params = $db->q($this->params->toString());
        $id     = $db->q($this->id);

        $query = "UPDATE `#__extensions` SET params = {$params} WHERE extension_id = {$id}";
        $db->setQuery($query);
        $db->execute();
    }

    /**
     * Get extension name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
