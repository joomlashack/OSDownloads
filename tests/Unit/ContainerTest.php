<?php

/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2026 Joomlashack.com. All rights reserved
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

namespace Tests\Unit;

use Alledia\OSDownloads\Container;
use Codeception\Test\Unit;
use stdClass;

class ContainerTest extends Unit
{
    public function testDefaultHelpersAreNull()
    {
        $container = new Container();

        $this->assertNull($container->helperRoute);
        $this->assertNull($container->helperSanitize);
    }

    public function testUnknownServiceIsNull()
    {
        $this->assertNull((new Container())->noSuchService);
    }

    public function testMagicGetResolvesServices()
    {
        $service   = new stdClass();
        $container = new Container([
            'helperRoute' => function () use ($service) {
                return $service;
            },
        ]);

        $this->assertSame($service, $container->helperRoute);
        $this->assertSame($service, $container->helperRoute, 'Services are shared');
    }

    public function testGetInstanceReturnsSingleton()
    {
        $this->assertSame(Container::getInstance(), Container::getInstance());
    }
}
