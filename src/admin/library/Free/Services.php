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

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die();
// phpcs:enable PSR1.Files.SideEffects

class Services implements ServiceProviderInterface
{
    /**
     * @inheritDoc
     */
    public function register(Pimple $pimple): void
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
