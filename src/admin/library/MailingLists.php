<?php
/**
 * @package    OSDownloads
 * @contact    www.joomlashack.com, help@joomlashack.com
 * @copyright  2018 Open Source Training, LLC. All rights reserved
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

namespace Alledia\OSDownloads;

use Alledia\Framework\Factory;
use Alledia\Installer\Extension\Licensed;
use JFolder;
use JForm;
use JTable;
use SimpleXMLElement;

defined('_JEXEC') or die();

abstract class MailingLists
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
     * Find all Mailing List plugins and attach
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
     * Load all xmk configuration files for mailing list plugins
     *
     * @return string[]
     */
    public static function getPluginFiles($type)
    {
        jimport('joomla.filesystem.folder');

        $baseFolder = '/MailingList';
        $regex      = sprintf('\.%s$', $type);
        $extension  = Factory::getExtension('OSDownloads', 'component');

        $configFiles = array();

        // Collect Pro configuration files first
        if ($extension->isPro()) {
            $proPath  = $extension->getProLibraryPath() . $baseFolder;
            $proFiles = JFolder::files($proPath, $regex, false, true);
            foreach ($proFiles as $proFile) {
                $key               = strtolower(basename($proFile, '.' . $type));
                $configFiles[$key] = $proFile;
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
     * @param JForm $form
     *
     * @throws \Exception
     */
    public static function loadForms(JForm $form)
    {
        if ($formFiles = static::getPluginFiles('xml')) {
            switch ($form->getName()) {
                case 'com_config.component':
                    $component = \JFactory::getApplication()->input->getCmd('component');
                    if ($component == 'com_osdownloads') {
                        static::loadConfigurationForms($form, $formFiles);
                    }
                    break;
            }
        }
    }

    /**
     * Add Mailing list fields to OSDownloads Configuration form
     * which must contain the tag
     * <fields name="mailinglist"/>
     *
     * @param JForm    $form
     * @param string[] $files
     */
    protected static function loadConfigurationForms(JForm $form, $files)
    {
        $mailingLists = $form->getXml()->xpath('//fields[@name="mailinglist"]');

        if ($mailingLists && $files) {
            $mailingLists = array_shift($mailingLists);

            $configurations = array();
            foreach ($files as $file) {
                $configuration = simplexml_load_file($file);
                if ($newNode = $configuration->xpath('fields[@name="config"]/fields')) {
                    $newNode = array_shift($newNode);
                    if (!(int)$newNode['order']) {
                        $newNode->addAttribute('order', (int)$configuration['order']);
                    }

                    if (($group = (string)$newNode['name']) && empty($configurations[$group])) {
                        $configurations[$group] = $newNode;
                    }
                }
            }

            // Sort configuration field groups on optional 'order' attribute
            uasort($configurations, function (SimpleXMLElement $a, SimpleXMLElement $b) {
                $orderA = (int)$a['order'] ?: 999;
                $orderB = (int)$b['order'] ?: 999;

                return $orderA == $orderB
                    ? 0
                    : ($orderA < $orderB ? -1 : 1);
            });

            foreach ($configurations as $group => $configuration) {
                $listNode = $mailingLists->xpath(sprintf('fields[@name="%s"]', $group));
                if (!$listNode) {
                    $listNode = $mailingLists->addChild('fields');
                    $listNode->addAttribute('name', $group);
                }
                $newFields = $configuration->children();
                $form->setFields($newFields, 'mailinglist.' . $group);
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
