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

use Alledia\OSDownloads\Free\Helper\SEF;
use Codeception\Attribute\DataProvider;
use Codeception\Test\Unit;
use ReflectionClass;

class SEFTest extends Unit
{
    /**
     * @var SEF
     */
    protected $sef = null;

    protected function setUp(): void
    {
        parent::setUp();

        // The constructor loads Joomla menu items
        $this->sef = (new ReflectionClass(SEF::class))->newInstanceWithoutConstructor();
    }

    public static function linkProvider(): array
    {
        return [
            'menu item link' => ['index.php?option=com_osdownloads&view=item&id=12', 12],
            'bare query'     => ['option=com_osdownloads&id=7', 7],
            'no id'          => ['index.php?option=com_osdownloads&view=downloads', null],
            'non-numeric id' => ['index.php?option=com_osdownloads&view=item&id=abc', 0],
        ];
    }

    #[DataProvider('linkProvider')]
    public function testGetIdFromLink(string $link, ?int $expected)
    {
        $this->assertSame($expected, $this->sef->getIdFromLink($link));
    }
}
