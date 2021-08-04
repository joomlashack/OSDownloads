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

namespace Alledia\OSDownloads\Free;

use Alledia\OSDownloads\Pro\Services as ProServices;

defined('_JEXEC') or die();

/**
 * Class Factory
 *
 * @package OSDownloads
 */
abstract class Factory extends \Alledia\Framework\Factory
{
    /**
     * @var Container
     */
    protected static $pimpleContainer = null;

    /**
     * @inheritDoc
     */
    public static function getExtension($namespace = 'osdownloads', $type = 'component', $folder = null)
    {
        return parent::getExtension($namespace, $type, $folder);
    }

    /**
     * Get the current container instance. Creates if not set yet.
     *
     * @return Container
     */
    public static function getPimpleContainer(): Container
    {
        if (static::$pimpleContainer === null) {
            $services = static::getExtension()->isPro()
                ? new ProServices()
                : new Services();

            $container = new Container();
            $container->register($services);

            static::$pimpleContainer = $container;
        }

        return static::$pimpleContainer;
    }
}
