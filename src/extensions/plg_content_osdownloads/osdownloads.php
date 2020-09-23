<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2005-2020 Joomlashack.com. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
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

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;

defined('_JEXEC') or die();

if (!defined('OSDOWNLOADS_LOADED')) {
    $includePath = JPATH_ADMINISTRATOR . '/components/com_osdownloads/include.php';
    if (is_file($includePath)) {
        require_once $includePath;
    }
}

if (defined('OSDOWNLOADS_LOADED')) {
    $baseClass = '\\Alledia\\OSDownloads\\%s\\Joomla\\Plugin\\Content';

    if (class_exists(sprintf($baseClass, 'Pro'))) {
        class PlgContentOsdownloads extends \Alledia\OSDownloads\Pro\Joomla\Plugin\Content
        {
        }

    } elseif (class_exists(sprintf($baseClass, 'Free'))) {
        class PlgContentOsdownloads extends \Alledia\OSDownloads\Free\Joomla\Plugin\Content
        {
        }

    } else {
        class PlgContentOsdownloads extends CMSPlugin
        {
            protected $autoloadLanguage = true;

            /**
             * PlgContentOsdownloads constructor.
             *
             * @param JEventDispatcher $subject
             * @param array            $config
             *
             * @throws Exception
             */
            public function __construct($subject, array $config = [])
            {
                parent::__construct($subject, $config);

                Factory::getApplication()->enqueueMessage(
                    Text::sprintf('COM_OSDOWNLOADS_PLUGIN_NOT_INSTALLED', 'CONTENT')
                );
            }
        }
    }
}
