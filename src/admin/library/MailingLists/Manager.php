<?php
/**
 * @package    OSDownloads
 * @contact    www.joomlashack.com, help@joomlashack.com
 * @copyright  2016-2018 Open Source Training, LLC. All rights reserved
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 *
 * This file is part of OSDownloads.
 *
 * OSDownloads is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * OSDownloads is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OSDownloads.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Alledia\OSDownloads\MailingLists;

use Alledia\Framework\Factory;
use Alledia\Installer\Extension\Licensed;
use JFolder;
use JForm;
use JTable;
use SimpleXMLElement;

defined('_JEXEC') or die();

class Manager
{
    /**
     * @var Licensed
     */
    protected static $extension = null;

    /**
     * @var string
     */
    protected static $basePath = null;

    /**
     * @var string
     */
    protected static $baseClass = '\\Alledia\\OSDownloads';

    /**
     * @var string[]
     */
    protected static $sourceNames = array(
        'com_config.component'                   => 'config',
        'com_categories.categorycom_osdownloads' => 'category',
        'com_osdownloads.file'                   => 'file'
    );

    /**
     * @var string
     */
    protected static $xpathFields = '//fields[@name="mailinglist"]';

    /**
     * @var string
     */
    protected static $xpathFieldset = '//fieldset[@name="mailinglists"]';

    /**
     * Find all Mailing List plugins and attach as Table observers
     *
     * @param JTable $table
     *
     * @return void
     */
    public function loadObservers(JTable $table)
    {
        $plugins = $this->getPluginFiles('php');

        foreach ($plugins as $pluginPath) {
            /** @var \JObserverInterface $pluginClass */
            $pluginClass = $this->convertPathToClass($pluginPath);

            $pluginClass::createObserver($table);
        }
    }

    /**
     * Load plugin fragments to selected forms. The target form requires:
     * <fields name="mailinglist"/>
     *
     * The plugin source form file must be structured as:
     * <form order="##" group="PluginName">
     *    <fields name="FormName">
     *       <FormField tag>
     *       ...
     *    </fields>
     * </form>
     *
     * ##              : (Optional) ordering/priority for the plugin form fragment in the form
     * PluginGroupName : The field group name for the added fields in the form
     * FormName        : A form name listed in static::$sourceNames
     *
     * @param JForm $form
     *
     * @throws \Exception
     */
    public function loadForms(JForm $form)
    {
        if ($formFiles = $this->getPluginFiles('xml')) {
            $formName = $form->getName();

            if (!empty(static::$sourceNames[$formName])) {
                $sourceName = static::$sourceNames[$formName];

                // Special handling for configuration form
                if ($sourceName == 'config'
                    && \JFactory::getApplication()->input->getCmd('component') != 'com_osdownloads'
                ) {
                    return;
                }

                $this->addFields($formFiles, $form, $sourceName);
            }
        }
    }

    /**
     * Load all xml configuration files for mailing list plugins
     *
     * @return string[]
     */
    protected function getPluginFiles($type)
    {
        jimport('joomla.filesystem.folder');

        $baseFolder = '/MailingList';
        $regex      = sprintf('\.%s$', $type);
        $extension  = $this->getExtension();

        $configFiles = array();

        // Collect Pro configuration files first
        if ($extension->isPro()) {
            $proPath = $extension->getProLibraryPath() . $baseFolder;

            if (is_dir($proPath)) {
                $proFiles = JFolder::files($proPath, $regex, false, true);
                foreach ($proFiles as $proFile) {
                    $key               = strtolower(basename($proFile, '.' . $type));
                    $configFiles[$key] = $proFile;
                }
            }
        }

        // Collect free files but don't override pro versions
        $freePath  = $extension->getLibraryPath() . '/Free' . $baseFolder;
        $freeFiles = JFolder::files($freePath, $regex, false, true);
        foreach ($freeFiles as $freeFile) {
            $key = strtolower(basename($freeFile, '.' . $type));
            if (empty($configFiles[$key])) {
                $configFiles[$key] = $freeFile;
            }
        }

        return $configFiles;
    }

    /**
     * @param string[] $files
     * @param string   $name
     *
     * @return array
     */
    protected function getFormSources($files, $name)
    {
        $sources = array();
        foreach ($files as $file) {
            if ($this->pluginEnabled($file, $name)) {
                $source  = simplexml_load_file($file);
                $group   = (string)$source['group'];
                $order   = (int)$source['order'] ?: 999;
                $newNode = $source->xpath(sprintf('fields[@name="%s"]', $name));

                if ($group && $newNode) {
                    $newNode = array_shift($newNode);
                    if (!(int)$newNode['order']) {
                        $newNode->addAttribute('order', $order);
                    }

                    if (empty($sources[$group])) {
                        $sources[$group] = $newNode;
                    }
                }
            }
        }

        return $sources;
    }

    /**
     * Add plugin fields for the selected form
     *
     * @param string[] $files
     * @param JForm    $form
     * @param string   $sourceName
     *
     * @return void
     */
    protected function addFields($files, JForm $form, $sourceName)
    {
        $formXml = $form->getXml();

        if ($target = $formXml->xpath(static::$xpathFields)) {
            $target = array_shift($target);
        }

        if ($target) {
            $sources = $this->getFormSources($files, $sourceName);
            if (!$sources) {
                if ($fieldset = $formXml->xpath(static::$xpathFieldset)) {
                    $fieldset = array_shift($fieldset);

                    $fieldset['description'] = \JText::_('COM_OSDOWNLOADS_ML_NO_PLUGINS');
                }

            } else {
                $parents        = $target->xpath('ancestor::fields[@name]/@name');
                $parentGroups   = array_map('strval', $parents ?: array());
                $parentGroups[] = (string)$target['name'];
                $parentGroup    = join('.', $parentGroups) . '.';

                // Sort field groups on optional 'order' attribute
                uasort($sources, function (SimpleXMLElement $a, SimpleXMLElement $b) {
                    $orderA = (int)$a['order'] ?: 999;
                    $orderB = (int)$b['order'] ?: 999;

                    return $orderA == $orderB
                        ? 0
                        : ($orderA < $orderB ? -1 : 1);
                });

                foreach ($sources as $group => $source) {
                    $listNode = $target->xpath(sprintf('fields[@name="%s"]', $group));
                    if (!$listNode) {
                        $listNode = $target->addChild('fields');
                        $listNode->addAttribute('name', $group);
                    }
                    $newFields = $source->children();
                    $form->setFields($newFields, $parentGroup . $group);
                }
            }
        }
    }

    /**
     * @return Licensed
     */
    protected function getExtension()
    {
        if (static::$extension === null) {
            static::$extension = Factory::getExtension('OSDownloads', 'component');
        }

        return static::$extension;
    }

    /**
     * Get the base path for autoloaded library files
     *
     * @return string
     */
    protected function getBasePath()
    {
        if (static::$basePath === null) {
            static::$basePath = $this->getExtension()->getLibraryPath();
        }

        return static::$basePath;
    }

    /**
     * Converts a file path to an autoloadable class name
     *
     * @param string $filePath
     *
     * @return string
     */
    protected function convertPathToClass($filePath)
    {
        $basePath = str_replace('/', '\\', $this->getBasePath());
        $filePath = str_replace('/', '\\', $filePath);
        $classPath = str_replace($basePath, '', $filePath);

        $className = static::$baseClass . preg_replace('/\.(php|xml)$/', '', $classPath);

        return $className;
    }

    protected function pluginEnabled($file, $formName)
    {
        $enabled = true;

        $className = $this->convertPathToClass($file);
        if (class_exists($className)) {
            $method  = 'checkDependencies';
            $enabled = !method_exists($className, $method) || call_user_func(array($className, $method));
            if ($enabled && $formName != 'config') {
                $method  = 'isEnabled';
                $enabled = !method_exists($className, $method) || call_user_func(array($className, $method));
            }
        }

        return $enabled;
    }
}
