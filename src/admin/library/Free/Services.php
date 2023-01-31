<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2005-2023 Joomlashack.com. All rights reserved
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

use Alledia\OSDownloads\Factory;
use Alledia\OSDownloads\MailingLists\Manager;
use Pimple\Container as Pimple;
use Pimple\ServiceProviderInterface;

defined('_JEXEC') or die();

/**
 * Class Services
 *
 * Pimple services for OSDownloads.
 *
 * @package OSDownloads
 */
class Services implements ServiceProviderInterface
{
    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Pimple $pimple
     */
    public function register(Pimple $pimple)
    {
        // Services
        $pimple['app'] = function () {
            return Factory::getApplication();
        };

        $pimple['db'] = function () {
            return Factory::getDbo();
        };

        $pimple['helperRoute'] = function () {
            return new Helper\Route();
        };

        $pimple['helperSEF'] = function () {
            return new Helper\SEF();
        };

        $pimple['helperView'] = function () {
            return new Helper\View();
        };

        $pimple['mailingLists'] = function () {
            return new Manager();
        };
    }
}
