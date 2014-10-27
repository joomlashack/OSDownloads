<?php
/**
 * @package   AllediaInstaller
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2014 Alledia.com, All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die();

use Alledia\Framework\Factory;

abstract class AllediaInstallerAbstract
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
    public function initprops($parent)
    {
        $this->installer = $parent->get('parent');
        $this->manifest  = $this->installer->getManifest();
        $this->messages  = array();

        if ($media = $this->manifest->media) {
            $path              = JPATH_SITE . '/' . $media['folder'] . '/' . $media['destination'];
            $this->mediaFolder = $path;
        }

        $attributes  = (array) $this->manifest->attributes();
        $attributes  = $attributes['@attributes'];
        $this->type  = $attributes['type'];

        if ($this->type === 'plugin') {
            $this->group = $attributes['group'];
        }

        // Load the installer default language
        $language = JFactory::getLanguage();
        $language->load('lib_allediainstaller.sys', ALLEDIA_INSTALLER_EXTENSION_PATH);
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
        $this->initprops($parent);
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
        $this->initprops($parent);

        if ($type === 'update') {
            $this->clearUpdateServers();
        }

        return true;
    }

    /**
     * Load Alledia Framework
     */
    protected function loadAllediaFramework()
    {
        if (!defined('ALLEDIA_FRAMEWORK_LOADED')) {
            $allediaFrameworkPath = JPATH_SITE . '/libraries/allediaframework/include.php';

            if (!file_exists($allediaFrameworkPath)) {
                throw new Exception('Alledia framework not found [AllediaInstaller]');
            }

            require_once $allediaFrameworkPath;
        }
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

        $this->loadAllediaFramework();

        // Load the extension instance from the framework
        $extension = Factory::getExtension(
            (string) $this->manifest->alledia->namespace,
            $this->type,
            $this->group
        );

        // If Free, remove any missed Pro library
        $goProAdHtml = '';
        if (!$extension->isPro()) {
            $proLibraryPath = $extension->getProLibraryPath();
            if (file_exists($proLibraryPath)) {
                jimport('joomla.filesystem.folder');
                JFolder::delete($proLibraryPath);
            }

            // Load the Pro ad field, if we have a pro version available
            $goProField = $this->manifest->xpath('//field[@type="gopro"]');

            if (!empty($goProField)) {
                require_once ALLEDIA_FRAMEWORK_PATH . '/joomla/fields/GoPro.php';

                $field = new JFormFieldGoPro();
                $field->fromInstaller = true;
                $goProAdHtml = $field->getInput();

                unset($field);
            }
        }

        // Show additional installation messages
        $extensionPath = $this->getExtensionPath($this->type, (string) $this->manifest->alledia->element, $this->group);

        // Load the extension language
        JFactory::getLanguage()->load($this->getFullElement(), $extensionPath);

        // If Pro extension, includes the license form view
        if ($extension->isPro()) {
            // Get the OSMyLicensesManager extension to handle the license key
            $licensesManagerExtension = Factory::getExtension('osmylicensesmanager', 'plugin', 'system');
            $isLicensesManagerInstalled = false;

            if (!empty($licensesManagerExtension)) {
                $licenseKey = $licensesManagerExtension->params->get('license-keys', '');

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

        $name         = $this->manifest->alledia->namespace . ($extension->isPro() ? ' Pro' : '');
        $mediaPath    = JPATH_SITE . '/media/' . $extension->getFullElement();
        $libMediaPath = JPATH_SITE . '/media/lib_allediaframework';
        $mediaURL     = JURI::root() . 'media/' . $extension->getFullElement();
        $libMediaURL  = JURI::root() . 'media/lib_allediaframework';

        $this->addStyles(array(
            $mediaPath . '/css/installer.css',
            $libMediaPath . '/css/style_gopro_field.css',
        ));

        // Include the installer template
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
                    unset($tmpInstaller);
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
                    if ($installer->install($path)) {
                        $this->setMessage(JText::sprintf($text, $typeName, $element));
                        if ($isNew) {
                            $current = $this->findExtension($type, $element, $group);

                            if (isset($attributes['publish']) && (bool) $attributes['publish']) {
                                $current->publish();

                                $this->storeFeedbackForRelatedExtension($element, 'publish', true);
                            }

                            if ($type === 'plugin') {
                                if (isset($attributes['ordering'])) {
                                    $this->setPluginOrder($current, $attributes['ordering']);

                                    $this->storeFeedbackForRelatedExtension($element, 'ordering', true);
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
            $installer      = new JInstaller();
            $source         = $this->installer->getPath('source');

            foreach ($this->manifest->alledia->relatedExtensions->extension as $extension) {
                $attributes = (array) $extension->attributes();
                if (!empty($attributes)) {
                    $attributes = $attributes['@attributes'];
                }

                if (isset($attributes['uninstall']) && (bool) $attributes['uninstall']) {
                    $type    = $attributes['type'];
                    $element = $attributes['element'];

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
                        $this->setMessage(JText::sprintf($msg, ucfirst($type), $element), $msgtype);
                    }
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

        $terms = array(
            'type'    => $type,
            'element' => (array_key_exists($type, $prefixes) ? $prefixes[$type] : '') . $element
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
                    $path = JPATH_SITE . (string) $file;
                    if (file_exists($path)) {
                        JFile::delete($path);
                    }
                }
            }

            // Folders
            if ($obsolete->folder) {
                jimport('joomla.filesystem.folder');

                foreach ($obsolete->folder as $folder) {
                    $path = JPATH_SITE . (string) $folder;
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
        $db->setQuery('SELECT `update_site_id`
                       FROM `#__update_sites_extensions`
                       WHERE extension_id = ' . (int)$extension->extension_id
        );

        if ($list = $db->loadColumn()) {
            $db->setQuery('DELETE FROM `#__update_sites_extensions`
                           WHERE extension_id=' . (int)$extension->extension_id);
            $db->execute();

            $db->setQuery('DELETE FROM `#__update_sites`
                           WHERE update_site_id IN (' . join(',', $list) . ')');
            $db->execute();
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
            'component' => 'administrator/components/com_',
            'plugin'    => 'plugins/',
            'template'  => 'templates/',
            'library'   => 'administrator/manifests/libraries/',
            'cli'       => 'cli/',
            'module'    => 'modules/mod_'
        );

        $basePath = JPATH_SITE . '/' . $folders[$type];

        if ($type === 'plugin') {
            $basePath .= $group . '/' . $element;
        } else {
            $basePath .= $element;
        }

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
        $basePath = $this->getExtensionPath($type, $element, $group);

        $installer = new JInstaller();
        if ($type !== 'library') {
            $installer->setPath('source', $basePath);
            $installer->getManifest();

            $manifestPath = $installer->getPath('manifest');
        } else {
            $manifestPath = $basePath . '.xml';

            if (!file_exists($manifestPath)) {
                $manifestPath = str_replace($element, 'lib_' . $element, $basePath) . '.xml';
            }

            if (!$installer->isManifest($manifestPath)) {
                $manifestPath = '';
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
        $db->setQuery('UPDATE `#__extensions` SET custom_data="{\"author\":\"Alledia\"}"
                       WHERE extension_id=' . (int)$extension->extension_id);
        $db->execute();

        // Update the Alledia framework
        // @TODO: remove this after libraries be able to have a custom install script
        $db->setQuery('UPDATE `#__extensions` SET custom_data="{\"author\":\"Alledia\"}"
                       WHERE type="library" AND element="allediaframework"');
        $db->execute();
    }

    /**
     * Add styles to the output. Used because when the postFlight
     * method is called, we can't add stylesheets to the head.
     *
     * @param array $stylesheets
     */
    protected function addStyles($stylesheets)
    {
        foreach ($stylesheets as $path) {
            if (file_exists($path)) {
                $style = file_get_contents($path);

                echo '<style>' . $style . '</style>';
            }
        }
    }
}
