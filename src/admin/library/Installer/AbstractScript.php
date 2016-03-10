<?php
/**
 * @package   AllediaInstaller
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2015 Alledia.com, All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace Alledia\Installer;

defined('_JEXEC') or die();

use JFactory;
use JTable;
use JTableExtension;
use JInstaller;
use JRegistry;
use JText;
use JURI;
use JFolder;
use JFormFieldCustomFooter;
use JInstallerAdapterComponent;
use JModelLegacy;
use JFile;
use SimpleXMLElement;

abstract class AbstractScript
{
    /**
     * @var JInstaller
     */
    protected $installer = null;

    /**
     * @var SimpleXMLElement
     */
    protected $manifest = null;

    /**
     * @var string
     */
    protected $previousVersion = '0.0.0';

    /**
     * @var string
     */
    protected $mediaFolder = null;

    /**
     * @var array
     */
    protected $messages = array();

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $group;

    /**
     * List of tables and respective columns
     * @var array
     */
    protected $columns;

    /**
     * List of tables
     * @var array
     */
    protected $tables;

    /**
     * Feedback of the install by related extension
     *
     * @var array
     */
    protected $relatedExtensionFeedback = array();

    /**
     * @param JInstallerAdapterComponent $parent
     *
     * @return void
     */
    public function initProperties($parent)
    {
        $this->installer = $parent->get('parent');
        $this->manifest  = $this->installer->getManifest();
        $this->messages  = array();

        if ($media = $this->manifest->media) {
            $this->mediaFolder = JPATH_SITE . '/' . $media['folder'] . '/' . $media['destination'];
        }

        $attributes  = (array) $this->manifest->attributes();
        $attributes  = $attributes['@attributes'];
        $this->type  = $attributes['type'];

        if ($this->type === 'plugin') {
            $this->group = $attributes['group'];
        }

        // Get the previous version number for upgrades
        $path = $this->installer->getPath('extension_administrator');
        $path .= '/' . basename($this->installer->getPath('manifest'));
        if (is_file($path)) {
            $previousManifest      = JInstaller::parseXMLInstallFile($path);
            $this->previousVersion = (string)$previousManifest['version'] ? : '0.0.0';
        }

        // Load the installer default language
        $language = JFactory::getLanguage();
        $language->load('lib_allediainstaller.sys', __DIR__ . '/../../');
    }

    /**
     * @param JInstallerAdapterComponent $parent
     *
     * @return bool
     */
    public function install($parent)
    {
        return true;
    }

    /**
     * @param JInstallerAdapterComponent $parent
     *
     * @return bool
     */
    public function discover_install($parent)
    {
        return $this->install($parent);
    }

    /**
     * @param JInstallerAdapterComponent $parent
     *
     * @return void
     */
    public function uninstall($parent)
    {
        $this->initProperties($parent);
        $this->uninstallRelated();
        $this->showMessages();
    }

    /**
     * @param JInstallerAdapterComponent $parent
     *
     * @return bool
     */
    public function update($parent)
    {
        return true;
    }

    /**
     * @param string                     $type
     * @param JInstallerAdapterComponent $parent
     *
     * @return bool
     */
    public function preFlight($type, $parent)
    {
        $this->initProperties($parent);

        if ($type === 'update') {
            $this->clearUpdateServers();
        }

        return true;
    }

    /**
     * @param string                     $type
     * @param JInstallerAdapterComponent $parent
     *
     * @return void
     */
    public function postFlight($type, $parent)
    {
        $this->clearObsolete();
        $this->installRelated();
        $this->addAllediaAuthorshipToExtension();

        // @TODO: Stop the script here if this is a related extension (but still remove pro folder, if needed)

        $element = (string) $this->manifest->alledia->element;

        // Check and publish/reorder the plugin, if required
        $published = false;
        $ordering  = false;
        if (strpos($type, 'install') !== false && $this->type === 'plugin') {
            $published = $this->publishThisPlugin();
            $ordering  = $this->reorderThisPlugin();
        }

        $extension = new Extension\Licensed(
            (string) $this->manifest->alledia->namespace,
            $this->type,
            $this->group
        );

        // If Free, remove any missed Pro library
        if (!$extension->isPro()) {
            $proLibraryPath = $extension->getProLibraryPath();
            if (file_exists($proLibraryPath)) {
                jimport('joomla.filesystem.folder');
                JFolder::delete($proLibraryPath);
            }
        }

        // Get the footer content
        $footer        = '';
        $footerElement = null;

        // Check if we have a dedicated config.xml file
        $configPath = $extension->getExtensionPath() . '/config.xml';
        if (JFile::exists($configPath)) {
            $config = $extension->getConfig();

            if (!empty($config)) {
                $footerElement = $config->xpath('//field[@type="customfooter"]');
            }
        } else {
            $footerElement = $this->manifest->xpath('//field[@type="customfooter"]');
        }

        if (!empty($footerElement)) {
            if (!class_exists('JFormFieldCustomFooter')) {
                require_once $extension->getExtensionPath() . '/form/fields/customfooter.php';
            }

            $field = new JFormFieldCustomFooter();
            $field->fromInstaller = true;
            $footer = $field->getInputUsingCustomElement($footerElement[0]);

            unset($field);
        }

        // Show additional installation messages
        $extensionPath = $this->getExtensionPath($this->type, (string) $this->manifest->alledia->element, $this->group);

        // Load the extension language
        JFactory::getLanguage()->load($this->getFullElement(), $extensionPath);

        // If Pro extension, includes the license form view
        if ($extension->isPro()) {
            // Get the OSMyLicensesManager extension to handle the license key
            $licensesManagerExtension = new Extension\Generic('osmylicensesmanager', 'plugin', 'system');
            $isLicensesManagerInstalled = false;

            if (!empty($licensesManagerExtension)) {
                if (isset($licensesManagerExtension->params)) {
                    $licenseKey = $licensesManagerExtension->params->get('license-keys', '');
                } else {
                    $licenseKey = '';
                }

                $isLicensesManagerInstalled = true;
            }
        }

        // Welcome message
        if ($type === 'install') {
            $string = 'LIB_ALLEDIAINSTALLER_THANKS_INSTALL';
        } else {
            $string = 'LIB_ALLEDIAINSTALLER_THANKS_UPDATE';
        }
        $welcomeMessage = JText::sprintf(
            $string,
            $this->manifest->alledia->namespace . ($extension->isPro() ? ' Pro' : '')
        );

        // Variables for the template
        $name     = $this->manifest->alledia->namespace . ($extension->isPro() ? ' Pro' : '');
        $mediaURL = JURI::root() . 'media/' . $extension->getFullElement();

        $this->addStyle($this->mediaFolder . '/css/installer.css');

        // Include the template
        include $extensionPath . '/views/installer/tmpl/default.php';

        $this->showMessages();
    }

    /**
     * Install related extensions
     *
     * @return void
     */
    protected function installRelated()
    {
        if ($this->manifest->alledia->relatedExtensions) {
            $installer      = new JInstaller();
            $source         = $this->installer->getPath('source');
            $extensionsPath = $source . '/extensions';

            foreach ($this->manifest->alledia->relatedExtensions->extension as $extension) {
                $path = $extensionsPath . '/' . (string)$extension;

                $attributes = (array) $extension->attributes();
                if (!empty($attributes)) {
                    $attributes = $attributes['@attributes'];
                }

                if (is_dir($path)) {
                    $type    = $attributes['type'];
                    $element = $attributes['element'];

                    $group = '';
                    if (isset($attributes['group'])) {
                        $group  = $attributes['group'];
                    }

                    $current = $this->findExtension($type, $element, $group);
                    $isNew   = empty($current);

                    $typeName = ucfirst(trim(($group ? : '') . ' ' . $type));

                    // Get data from the manifest
                    $tmpInstaller = new JInstaller();
                    $tmpInstaller->setPath('source', $path);
                    $newManifest = $tmpInstaller->getManifest();
                    $newVersion = (string)$newManifest->version;

                    $this->storeFeedbackForRelatedExtension($element, 'name', (string) $newManifest->name);

                    // Check if we have a higher version installed
                    if (!$isNew) {
                        $currentManifestPath = $this->getManifestPath($type, $element, $group);
                        $currentManifest = $this->getInfoFromManifest($currentManifestPath);

                        // Avoid to update for an outdated version
                        $currentVersion = $currentManifest->get('version');

                        if (version_compare($currentVersion, $newVersion, '>')) {
                            $this->setMessage(
                                JText::sprintf(
                                    'LIB_ALLEDIAINSTALLER_RELATED_UPDATE_SKIPED',
                                    strtolower($typeName),
                                    $element,
                                    $newVersion,
                                    $currentVersion
                                ),
                                'warning'
                            );

                            // Store the state of the install/update
                            $this->storeFeedbackForRelatedExtension(
                                $element,
                                'message',
                                JText::sprintf(
                                    'LIB_ALLEDIAINSTALLER_RELATED_UPDATE_STATE_SKIPED',
                                    $newVersion,
                                    $currentVersion
                                )
                            );

                            // Skip the install for this extension
                            continue;
                        }
                    }

                    $text = 'LIB_ALLEDIAINSTALLER_RELATED_' . ($isNew ? 'INSTALL' : 'UPDATE');
                    if ($tmpInstaller->install($path)) {
                        $this->setMessage(JText::sprintf($text, $typeName, $element));
                        if ($isNew) {
                            $current = $this->findExtension($type, $element, $group);

                            if (is_object($current)) {
                                if ($type === 'plugin') {
                                    if (isset($attributes['publish']) && $this->parseConditionalExpression($attributes['publish']) ) {
                                        $current->publish();

                                        $this->storeFeedbackForRelatedExtension(
                                            $element,
                                            'publish',
                                            (bool) $attributes['publish']
                                        );
                                    }

                                    if (isset($attributes['ordering'])) {
                                        $this->setPluginOrder($current, $attributes['ordering']);

                                        $this->storeFeedbackForRelatedExtension(
                                            $element,
                                            'ordering',
                                            $attributes['ordering']
                                        );
                                    }
                                }
                            }
                        }

                        $this->storeFeedbackForRelatedExtension(
                            $element,
                            'message',
                            JText::sprintf(
                                'LIB_ALLEDIAINSTALLER_RELATED_UPDATE_STATE_INSTALLED',
                                $newVersion
                            )
                        );

                    } else {
                        $this->setMessage(JText::sprintf($text . '_FAIL', $typeName, $element), 'error');

                        $this->storeFeedbackForRelatedExtension(
                            $element,
                            'message',
                            JText::sprintf(
                                'LIB_ALLEDIAINSTALLER_RELATED_UPDATE_STATE_FAILED',
                                $newVersion
                            )
                        );
                    }
                    unset($tmpInstaller);
                }
            }
        }
    }

    /**
     * Uninstall the related extensions that are useless without the component
     */
    protected function uninstallRelated()
    {
        if ($this->manifest->alledia->relatedExtensions) {
            $installer = new JInstaller();
            $source    = $this->installer->getPath('source');

            foreach ($this->manifest->alledia->relatedExtensions->extension as $extension) {
                $attributes = (array) $extension->attributes();
                if (!empty($attributes)) {
                    $attributes = $attributes['@attributes'];
                }

                $type    = $attributes['type'];
                $element = $attributes['element'];

                if (isset($attributes['uninstall']) && (bool) $attributes['uninstall']) {
                    $group = '';
                    if (isset($attributes['group'])) {
                        $group  = $attributes['group'];
                    }

                    if ($current = $this->findExtension($type, $element, $group)) {
                        $msg     = 'LIB_ALLEDIAINSTALLER_RELATED_UNINSTALL';
                        $msgtype = 'message';
                        if (!$installer->uninstall($current->type, $current->extension_id)) {
                            $msg .= '_FAIL';
                            $msgtype = 'error';
                        }
                        $this->setMessage(
                            JText::sprintf($msg, ucfirst($type), $element),
                            $msgtype
                        );
                    }
                } else {
                    $this->setMessage(
                        JText::sprintf('LIB_ALLEDIAINSTALLER_RELATED_NOT_UNINSTALLED', ucfirst($type), $element),
                        'warning'
                    );
                }
            }
        }
    }

    /**
     * @param string $type
     * @param string $element
     * @param string $group
     *
     * @return JTable
     */
    protected function findExtension($type, $element, $group = null)
    {
        $row = JTable::getInstance('extension');

        $prefixes = array(
            'component' => 'com_',
            'module'    => 'mod_'
        );

        // Fix the element, if the prefix is not found
        if (array_key_exists($type, $prefixes)) {
            if (substr_count($element, $prefixes[$type]) === 0) {
                $element = $prefixes[$type] . $element;
            }
        }

        $terms = array(
            'type'    => $type,
            'element' => $element
        );

        if ($type === 'plugin') {
            $terms['folder'] = $group;
        }

        $eid = $row->find($terms);
        if ($eid) {
            $row->load($eid);
            return $row;
        }

        return null;
    }

    /**
     * Set requested ordering for selected plugin extension
     * Accepted ordering arguments:
     * (n<=1 | first) First within folder
     * (* | last) Last within folder
     * (before:element) Before the named plugin
     * (after:element) After the named plugin
     *
     * @param JTable $extension
     * @param string $order
     *
     * @return void
     */
    protected function setPluginOrder(JTable $extension, $order)
    {
        if ($extension->type == 'plugin' && !empty($order)) {
            $db    = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->select('extension_id, element');
            $query->from('#__extensions');
            $query->where(
                array(
                    $db->qn('folder') . ' = ' . $db->q($extension->folder),
                    $db->qn('type') . ' = ' . $db->q($extension->type)
                )
            );
            $query->order($db->qn('ordering'));

            $plugins = $db->setQuery($query)->loadObjectList('element');

            // Set the order only if plugin already successfully installed
            if (array_key_exists($extension->element, $plugins)) {
                $target = array(
                    $extension->element => $plugins[$extension->element]
                );
                $others = array_diff_key($plugins, $target);

                if ((is_numeric($order) && $order <= 1) || $order == 'first') {
                    // First in order
                    $neworder = array_merge($target, $others);
                } elseif (($order == '*') || ($order == 'last')) {
                    // Last in order
                    $neworder = array_merge($others, $target);
                } elseif (preg_match('/^(before|after):(\S+)$/', $order, $match)) {
                    // place before or after named plugin
                    $place    = $match[1];
                    $element  = $match[2];
                    $neworder = array();
                    $previous = '';

                    foreach ($others as $plugin) {
                        if ((($place == 'before') && ($plugin->element == $element)) || (($place == 'after') && ($previous == $element))) {
                            $neworder = array_merge($neworder, $target);
                        }
                        $neworder[$plugin->element] = $plugin;
                        $previous                   = $plugin->element;
                    }
                    if (count($neworder) < count($plugins)) {
                        // Make it last if the requested plugin isn't installed
                        $neworder = array_merge($neworder, $target);
                    }
                } else {
                    $neworder = array();
                }

                if (count($neworder) == count($plugins)) {
                    // Only reorder if have a validated new order
                    JModelLegacy::addIncludePath(
                        JPATH_ADMINISTRATOR . '/components/com_plugins/models',
                        'PluginsModels'
                    );
                    $model = JModelLegacy::getInstance('Plugin', 'PluginsModel');

                    $ids = array();
                    foreach ($neworder as $plugin) {
                        $ids[] = $plugin->extension_id;
                    }
                    $order = range(1, count($ids));
                    $model->saveorder($ids, $order);
                }
            }
        }
    }

    /**
     * Display messages from array
     *
     * @return void
     */
    protected function showMessages()
    {
        $app = JFactory::getApplication();
        foreach ($this->messages as $msg) {
            $app->enqueueMessage($msg[0], $msg[1]);
        }

        $this->messages = array();
    }

    /**
     * Add a message to the message list
     *
     * @param string $msg
     * @param string $type
     *
     * @return void
     */
    protected function setMessage($msg, $type = 'message')
    {
        $this->messages[] = array($msg, $type);
    }

    /**
     * Delete obsolete files, folders and extensions.
     * Files and folders are identified from the site
     * root path and should starts with a slash.
     */
    protected function clearObsolete()
    {
        $obsolete = $this->manifest->alledia->obsolete;
        if ($obsolete) {
            // Extensions
            if ($obsolete->extension) {
                foreach ($obsolete->extension as $extension) {
                    $attributes = (array) $extension->attributes();
                    if (!empty($attributes)) {
                        $attributes = $attributes['@attributes'];
                    }

                    $type    = $attributes['type'];
                    $element = $attributes['element'];

                    $group = '';
                    if (isset($attributes['group'])) {
                        $group  = $attributes['group'];
                    }

                    $current = $this->findExtension($type, $element, $group);
                    if (!empty($current)) {
                        // Try to uninstall
                        $tmpInstaller = new JInstaller();
                        $uninstalled = $tmpInstaller->uninstall($type, $current->extension_id);

                        $typeName = ucfirst(trim(($group ? : '') . ' ' . $type));

                        if ($uninstalled) {
                            $this->setMessage(
                                JText::sprintf(
                                    'LIB_ALLEDIAINSTALLER_OBSOLETE_UNINSTALLED_SUCCESS',
                                    strtolower($typeName),
                                    $element
                                )
                            );
                        } else {
                            $this->setMessage(
                                JText::sprintf(
                                    'LIB_ALLEDIAINSTALLER_OBSOLETE_UNINSTALLED_FAIL',
                                    strtolower($typeName),
                                    $element
                                ),
                                'error'
                            );
                        }
                    }
                }
            }

            // Files
            if ($obsolete->file) {
                jimport('joomla.filesystem.file');

                foreach ($obsolete->file as $file) {
                    $path = JPATH_SITE . '/' . (string) $file;
                    if (file_exists($path)) {
                        JFile::delete($path);
                    }
                }
            }

            // Folders
            if ($obsolete->folder) {
                jimport('joomla.filesystem.folder');

                foreach ($obsolete->folder as $folder) {
                    $path = JPATH_SITE . '/' . (string) $folder;
                    if (file_exists($path)) {
                        JFolder::delete($path);
                    }
                }
            }
        }
    }

    /**
     * Finds the extension row for the main extension
     *
     * @return JTableExtension
     */
    protected function findThisExtension()
    {
        $attributes = (array)$this->manifest->attributes();
        $attributes = $attributes['@attributes'];

        $group = '';
        if ($attributes['type'] === 'plugin') {
            $group = $attributes['group'];
        }

        $extension = $this->findExtension(
            $attributes['type'],
            (string) $this->manifest->alledia->element,
            $group
        );

        return $extension;
    }

    /**
     * Use this in preflight to clear out obsolete update servers when the url has changed.
     */
    protected function clearUpdateServers()
    {
        $extension = $this->findThisExtension();

        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select($db->quoteName('update_site_id'))
            ->from($db->quoteName('#__update_sites_extensions'))
            ->where($db->quoteName('extension_id') . '=' . (int)$extension->extension_id);

        if ($list = $db->setQuery($query)->loadColumn()) {
            $query = $db->getQuery(true)
                ->delete($db->quoteName('#__update_sites_extensions'))
                ->where($db->quoteName('extension_id') . '=' . (int)$extension->extension_id);
            $db->setQuery($query)->execute();

            array_walk($list, 'intval');
            $query = $db->getQuery(true)
                ->delete($db->quoteName('#__update_sites'))
                ->where($db->quoteName('update_site_id') . ' IN (' . join(',', $list) . ')');
            $db->setQuery($query)->execute();
        }
    }

    /**
     * Get the full element, like com_myextension, lib_extension
     *
     * @var string $type
     * @var string $element
     * @var string $group
     *
     * @return string
     */
    protected function getFullElement($type = null, $element = null, $group = null)
    {
        $prefixes = array(
            'component' => 'com',
            'plugin'    => 'plg',
            'template'  => 'tpl',
            'library'   => 'lib',
            'cli'       => 'cli',
            'module'    => 'mod'
        );

        $type    = empty($type) ? $this->type : $type;
        $element = empty($element) ? (string) $this->manifest->alledia->element : $element;
        $group   = empty($group) ? $this->group : $group;

        $fullElement = $prefixes[$type] . '_';

        if ($type === 'plugin') {
            $fullElement .= $group . '_';
        }

        $fullElement .= $element;

        return $fullElement;
    }

    /**
     * Get extension information from manifest
     *
     * @return JRegistry
     */
    protected function getInfoFromManifest($manifestPath)
    {
        $info = new JRegistry();
        if (file_exists($manifestPath)) {
            $xml = JFactory::getXML($manifestPath);

            $attributes = (array) $xml->attributes();
            $attributes = $attributes['@attributes'];
            foreach ($attributes as $attribute => $value) {
                $info->set($attribute, $value);
            }

            foreach ($xml->children() as $e) {
                if (!$e->children()) {
                    $info->set($e->getName(), (string)$e);
                }
            }
        } else {
            $relativePath = str_replace(JPATH_SITE . '/', '', $manifestPath);
            $this->setMessage(
                JText::sprintf('LIB_ALLEDIAINSTALLER_MANIFEST_NOT_FOUND', $relativePath),
                'error'
            );
        }

        return $info;
    }

    /**
     * Get the path for the extension
     *
     * @return string The path
     */
    protected function getExtensionPath($type, $element, $group = '')
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

        $basePath = JPATH_SITE . '/' . $folders[$type];

        switch ($type) {
            case 'plugin':
                $basePath .= $group . '/';
                break;

            case 'module':
                if (!preg_match('/^mod_/', $element)) {
                    $basePath .= 'mod_';
                }
                break;

            case 'component':
                if (!preg_match('/^com_/', $element)) {
                    $basePath .= 'com_';
                }
                break;
        }

        $basePath .= $element;


        return $basePath;
    }

    /**
     * Get the id for an installed extension
     *
     * @return int The id
     */
    protected function getExtensionId($type, $element, $group = '')
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('extension_id')
            ->from('#__extensions')
            ->where(
                array(
                    $db->qn('element') . ' = ' . $db->q($element),
                    $db->qn('folder') . ' = ' . $db->q($group),
                    $db->qn('type') . ' = ' . $db->q($type)
                )
            );
        $db->setQuery($query);

        return $db->loadResult();
    }

    /**
     * Get the path for the manifest file
     *
     * @return string The path
     */
    protected function getManifestPath($type, $element, $group = '')
    {
        $installer = new JInstaller();

        if ($type !== 'library') {
            $basePath = $this->getExtensionPath($type, $element, $group);

            $installer->setPath('source', $basePath);
            $installer->getManifest();

            $manifestPath = $installer->getPath('manifest');
        } else {
            $manifestPath = JPATH_SITE . '/administrator/manifests/libraries/' . $element . '.xml';

            if (!file_exists($manifestPath) || !$installer->isManifest($manifestPath)) {
                $manifestPath = false;
            }
        }

        return $manifestPath;
    }

    /**
     * Check if it needs to publish the extension
     */
    protected function publishThisPlugin()
    {
        $attributes = (array) $this->manifest->alledia->element->attributes();
        $attributes = $attributes['@attributes'];

        if (isset($attributes['publish']) && (bool) $attributes['publish']) {
            $extension = $this->findThisExtension();
            $extension->publish();

            return true;
        }

        return false;
    }

    /**
     * Check if it needs to reorder the extension
     */
    protected function reorderThisPlugin()
    {
        $attributes = (array) $this->manifest->alledia->element->attributes();
        $attributes = $attributes['@attributes'];

        if (isset($attributes['ordering'])) {
            $extension = $this->findThisExtension();
            $this->setPluginOrder($extension, $attributes['ordering']);

            return $attributes['ordering'];
        }

        return false;
    }

    /**
     * Stores feedback data for related extensions to display after install
     *
     * @param  string $element
     * @param  string $key
     * @param  string $value
     */
    protected function storeFeedbackForRelatedExtension($element, $key, $value)
    {
        if (!isset($this->relatedExtensionFeedback[$element])) {
            $this->relatedExtensionFeedback[$element] = array();
        }

        $this->relatedExtensionFeedback[$element][$key] = $value;
    }

    /**
     * This method add a mark to the extensions, allowing to detect our extensions
     * on the extensions table.
     */
    protected function addAllediaAuthorshipToExtension()
    {
        $extension = $this->findThisExtension();

        $db = JFactory::getDbo();

        // Update the extension
        $customData = json_decode($extension->custom_data) ?: new \stdClass();
        $customData->author = 'Alledia';

        $query = $db->getQuery(true)
            ->update($db->quoteName('#__extensions'))
            ->set($db->quoteName('custom_data') . '=' . $db->quote(json_encode($customData)))
            ->where($db->quoteName('extension_id') . '=' . (int)$extension->extension_id);
        $db->setQuery($query)->execute();

        // Update the Alledia framework
        // @TODO: remove this after libraries be able to have a custom install script
        $query = $db->getQuery(true)
            ->update($db->quoteName('#__extensions'))
            ->set($db->quoteName('custom_data') . '=' . $db->quote('{"author":"Alledia"}'))
            ->where(
                array(
                    $db->quoteName('type') . '=' . $db->quote('library'),
                    $db->quoteName('element') . '=' . $db->quote('allediaframework')
                )
            );
        $db->setQuery($query)->execute();
    }

    /**
     * Add styles to the output. Used because when the postFlight
     * method is called, we can't add stylesheets to the head.
     *
     * @param mixed $stylesheets
     */
    protected function addStyle($stylesheets)
    {
        if (is_string($stylesheets)) {
            $stylesheets = array($stylesheets);
        }

        foreach ($stylesheets as $path) {
            if (file_exists($path)) {
                $style = file_get_contents($path);

                echo '<style>' . $style . '</style>';
            }
        }
    }

    /**
     * On new component install, this will check and fix any menus
     * that may have been created in a previous installation.
     *
     * @return void
     */
    protected function fixMenus()
    {
        if ($this->type == 'component') {
            $db = JFactory::getDbo();

            if ($extension = $this->findThisExtension()) {
                $id     = $extension->extension_id;
                $option = $extension->name;

                $query = $db->getQuery(true)
                    ->update('#__menu')
                    ->set('component_id = ' . $db->quote($id))
                    ->where(
                        array(
                            'type = ' . $db->quote('component'),
                            'link LIKE ' . $db->quote("%option={$option}%")
                        )
                    );
                $db->setQuery($query)->execute();

                // Check hidden admin menu option
                // @TODO:  Remove after Joomla! incorporates this natively
                $menuElement = $this->manifest->administration->menu;
                if (in_array((string) $menuElement['hidden'], array('true', 'hidden'))) {
                    $menu = JTable::getInstance('Menu');
                    $menu->load(array('component_id' => $id, 'client_id' => 1));
                    if ($menu->id) {
                        $menu->delete();
                    }
                }
            }
        }
    }

    /**
     * Get and store a cache of columns of a table
     *
     * @param  string $table The table name
     * @return array         A list of columns from a table
     */
    protected function getColumnsFromTable($table)
    {
        if (!isset($this->columns[$table])) {
            $db = JFactory::getDbo();
            $db->setQuery("SHOW COLUMNS FROM " . $db->quoteName($table));
            $rows = $db->loadObjectList();

            $columns = array();
            foreach ($rows as $row) {
                $columns[] = $row->Field;
            }

            $this->columns[$table] = $columns;
        }

        return $this->columns[$table];
    }

    /**
     * Add columns to a table if they doesn't exists
     *
     * @param string $table   The table name
     * @param array  $columns The column's names that needed to be checked and added
     */
    protected function addColumnsIfNotExists($table, $columns)
    {
        $db = JFactory::getDbo();

        $existentColumns = $this->getColumnsFromTable($table);

        foreach ($columns as $column => $specification) {
            if (! in_array($column, $existentColumns)) {
                $db->setQuery('ALTER TABLE ' . $db->quoteName($table)
                    . ' ADD COLUMN ' . $column . ' ' . $specification);
                $db->execute();
            }
        }
    }

    /**
     * Drop columns from a table if they exists
     *
     * @param string $table   The table name
     * @param array  $columns The column's names that needed to be checked and added
     */
    protected function dropColumnsIfExists($table, $columns)
    {
        $db = JFactory::getDbo();

        $existentColumns = $this->getColumnsFromTable($table);

        foreach ($columns as $column) {
            if (in_array($column, $existentColumns)) {
                $db->setQuery('ALTER TABLE ' . $db->quoteName($table) . ' DROP COLUMN ' . $column);
                $db->execute();
            }
        }
    }

    /**
     * Check if a table exists
     *
     * @param  string $name The table name
     * @return bool         True if the table exists
     */
    protected function tableExists($name)
    {
        $config = JFactory::getConfig();
        $tables = $this->getTables();

        // Replace the table prefix
        $name = str_replace('#__', $config->get('dbprefix'), $name);

        return in_array($name, $tables);
    }

    /**
     * Get a list of tables found in the db
     *
     * @param  bool  Force to get a fresh list of tables
     * @return array List of tables
     */
    protected function getTables($force = false)
    {
        if (empty($this->tables) || $force) {
            $normalizeTableArray = function($item) {
                return $item[0];
            };

            $db = JFactory::getDbo();
            $db->setQuery('SHOW TABLES');
            $tables = $db->loadRowList();

            $this->tables = array_map($normalizeTableArray, $tables);
        }

        return $this->tables;
    }

    /**
     * Parses a conditional string, returning a Boolean value (default: false).
     * For now it only supports an extension name and * as version.
     *
     * @param  string $expression The conditional expression
     * @return bool                According to the evaluation of the expression
     */
    protected function parseConditionalExpression($expression)
    {
        $expression = strtolower($expression);
        $terms      = explode('=', $expression);
        $term0      = trim($terms[0]);

        if (count($terms) === 1) {
            return ! (empty($terms[0]) || $terms[0] === 'null');
        } else {
            $term1 = trim($terms[1]);

            // Is the first term a name of extension?
            if (preg_match('/^(com_|plg_|mod_|lib_|tpl_|cli_)/', $term0)) {
                $info = $this->getExtensionInfoFromElement($term0);

                $extension = $this->findExtension($info['type'], $term0, $info['group']);

                // @TODO: compare the version, if specified, or different than *
                // @TODO: Check if the extension is enabled, not just installed

                if (! empty($extension)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get extension's info from element string, or extension name
     *
     * @param  string $element The extension name, as element
     * @return array           An associative array with information about the extension
     */
    public static function getExtensionInfoFromElement($element)
    {
        $result = array(
            'type'      => null,
            'name'      => null,
            'group'     => null,
            'prefix'    => null,
            'namespace' => null
        );

        $types = array(
            'com' => 'component',
            'plg' => 'plugin',
            'mod' => 'module',
            'lib' => 'library',
            'tpl' => 'template',
            'cli' => 'cli'
        );

        $element = explode('_', $element);

        $result['prefix'] = $element[0];

        if (array_key_exists($result['prefix'], $types)) {
            $result['type']  = $types[$result['prefix']];

            if ($result['prefix'] === 'plg') {
                $result['group'] = $element[1];
                $result['name']  = $element[2];
            } else {
                $result['name']  = $element[1];
                $result['group'] = null;
            }
        }

        $result['namespace'] = preg_replace_callback(
            '/^(os[a-z])(.*)/i',
            function($matches) {
                return strtoupper($matches[1]) . $matches[2];
            },
            $result['name']
        );

        return $result;
    }
}
