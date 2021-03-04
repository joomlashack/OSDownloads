<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2005-2021 Joomlashack.com. All rights reserved
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

namespace Alledia\OSDownloads\Free\Joomla\Component;

defined('_JEXEC') or die();

use Alledia\Framework\Joomla\Extension\AbstractComponent;
use Alledia\Framework\Factory;
use JHtml;

class Site extends AbstractComponent
{
    protected static $instance;

    protected $version = 'auto';

    public static function getInstance($namespace = null)
    {
        return parent::getInstance('OSDownloads');
    }

    public function init()
    {
        $options = array('version' => $this->getMediaVersion(), 'relative' => true);

        JHtml::_('stylesheet', 'com_osdownloads/frontend.css', $options, array());

        parent::init();
    }

    public function getMediaVersion()
    {
        if ('auto' === $this->version) {
            $manifest = $this->getManifest();

            if (!empty($manifest)) {
                $this->version = md5($manifest->version);
            }
        }

        return $this->version;
    }
}
