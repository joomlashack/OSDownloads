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

abstract class Manager
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
    protected static $xpathMailinglists = '//fields[@name="mailinglist"]';

    /**
     * Find all Mailing List plugins and attach as Table observers
     *
     * @param JTable $table
     *
     * @return void
     */
    public static function loadObservers(JTable $table)
    {
        $plugins = static::getPluginFiles('php');

        foreach ($plugins as $pluginPath) {
            /** @var \JObserverInterface $pluginClass */
            $pluginClass = static::convertPathToClass($pluginPath);

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
    public static function loadForms(JForm $form)
    {
        if ($formFiles = static::getPluginFiles('xml')) {
            $formName = $form->getName();

            if (!empty(static::$sourceNames[$formName])) {
                $sourceName = static::$sourceNames[$formName];

                // Special handling for configuration form
                if ($sourceName == 'config'
                    && \JFactory::getApplication()->input->getCmd('component') != 'com_osdownloads'
                ) {
                    return;
                }

                static::addFields($formFiles, $form, $sourceName);
            }
        }
    }

    /**
     * Load all xmk configuration files for mailing list plugins
     *
     * @return string[]
     */
    protected static function getPluginFiles($type)
    {
        jimport('joomla.filesystem.folder');

        $baseFolder = '/MailingList';
        $regex      = sprintf('\.%s$', $type);
        $extension  = Factory::getExtension('OSDownloads', 'component');

        $configFiles = array();

        // Collect Pro configuration files first
        if ($extension->isPro()) {
            $proPath  = $extension->getProLibraryPath() . $baseFolder;
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
    protected static function getFormSources($files, $name)
    {
        $sources = array();
        foreach ($files as $file) {
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

        return $sources;
    }

    protected static function addFields($files, JForm $form, $sourceName)
    {
        $sources = static::getFormSources($files, $sourceName);

        if ($target = $form->getXml()->xpath(static::$xpathMailinglists)) {
            $target = array_shift($target);
        }

        if ($sources && $target) {
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

    /**
     * @return Licensed
     */
    protected static function getExtension()
    {
        if (static::$extension === null) {
            static::$extension = Factory::getExtension('com_osdownloads', 'component');
        }

        return static::$extension;
    }

    /**
     * Get the base path for autoloaded library files
     *
     * @return string
     */
    protected static function getBasePath()
    {
        if (static::$basePath === null) {
            static::$basePath = static::getExtension()->getLibraryPath();
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
    protected static function convertPathToClass($filePath)
    {
        $classPath = str_replace(static::getBasePath(), '', $filePath);

        $className = static::$baseClass
            . str_replace(DIRECTORY_SEPARATOR, '\\', dirname($classPath))
            . '\\' . basename($classPath, '.php');

        return $className;
    }
}
