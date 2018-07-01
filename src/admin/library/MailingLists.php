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
        $plugins = static::getConfigurationFiles('php');

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
    public static function getConfigurationFiles($type)
    {
        jimport('joomla.filesystem.folder');

        $extension = Factory::getExtension('OSDownloads', 'component');

        $freePath = $extension->getLibraryPath() . '/Free';
        $proPath  = $extension->isPro() ? $extension->getProLibraryPath() : null;

        $folder = '/MailingList';

        $regex       = sprintf('\.%s$', $type);
        $configFiles = array_merge(
            $proPath ? JFolder::files($proPath . $folder, $regex, false, true) : array(),
            JFolder::files($freePath . $folder, $regex, false, true)
        );

        return $configFiles;
    }

    /**
     * Add Mailing list fields to any JForm that has a
     * <fields name="mailinglist"/> tag
     *
     * @param JForm $form
     */
    public static function loadConfigurationForms(JForm $form)
    {
        $mailingLists = $form->getXml()->xpath('//fields[@name="mailinglist"]');
        $files        = static::getConfigurationFiles('xml');

        if ($mailingLists && $files) {
            $mailingLists = array_shift($mailingLists);

            $configurations = array();
            foreach ($files as $file) {
                $configuration = simplexml_load_file($file);
                if ($newNode = $configuration->xpath('fields[@name="mailinglist"]/fields')) {
                    $newNode = array_shift($newNode);
                    if (($group = (string)$newNode['name']) && empty($configurations[$group])) {
                        $configurations[$group] = $newNode;
                    }
                }
            }

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
