<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2005-2025 Joomlashack.com. All rights reserved
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
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
 * along with OSDownloads.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace Alledia\OSDownloads\Free\Joomla\Component;

use Alledia\Framework\Joomla\Extension\AbstractComponent;
use Joomla\CMS\HTML\HTMLHelper;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die();

// phpcs:enable PSR1.Files.SideEffects

class Site extends AbstractComponent
{
    /**
     * @var self
     */
    protected static $instance;

    /**
     * @var string
     */
    protected $version = 'auto';

    /**
     * @inheritDoc
     */
    public static function getInstance($namespace = null)
    {
        return parent::getInstance('OSDownloads');
    }

    /**
     * @inheritDoc
     */
    public function init()
    {
        $options = ['version' => $this->getMediaVersion(), 'relative' => true];

        HTMLHelper::_('stylesheet', 'com_osdownloads/frontend.css', $options, []);

        parent::init();
    }

    /**
     * @return string
     */
    public function getMediaVersion(): string
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
