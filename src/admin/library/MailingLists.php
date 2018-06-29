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
use Alledia\OSDownloads\Free\MailingList\MailChimp;
use JForm;

defined('_JEXEC') or die();

abstract class MailingLists
{
    public static function loadObservers(\JTable $table)
    {
        MailChimp::createObserver($table);
    }

    /**
     * Load all xmk configuration files for mailing list plugins
     *
     * @return string[]
     */
    public static function getConfigurationFiles()
    {
        $extension = Factory::getExtension('OSDownloads', 'component');

        $freePath = $extension->getLibraryPath() . '/free';
        $proPath  = $extension->isPro() ? $extension->getProLibraryPath() : null;

        $folder = '/MailingList';

        $configFiles = array_merge(
            $proPath ? \JFolder::files($proPath . $folder, '\.xml$', false, true) : array(),
            \JFolder::files($freePath . $folder, '\.xml$', false, true)
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
        $files        = static::getConfigurationFiles();

        if ($mailingLists && $files) {
            $mailingLists = array_shift($mailingLists);

            foreach ($files as $file) {
                $configuration = simplexml_load_file($file);
                if ($newNode = $configuration->xpath('fields[@name="mailinglist"]/fields')) {
                    $newNode = array_shift($newNode);
                    if ($group = (string)$newNode['name']) {
                        $listNode = $mailingLists->addChild('fields');
                        $listNode->addAttribute('name', $group);
                        $newFields = $newNode->children();
                        $form->setFields($newFields, 'mailinglist.' . $group);
                    }
                }
            }
        }
    }
}
