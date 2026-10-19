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

use Alledia\OSDownloads\Free\Helper\Route;
use Codeception\Attribute\DataProvider;
use Codeception\Test\Unit;

class RouteTest extends Unit
{
    /**
     * @var Route
     */
    protected $route = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->route = new Route();
    }

    public static function fileListProvider(): array
    {
        return [
            'no arguments'      => [null, null, 'index.php?option=com_osdownloads&view=downloads'],
            'category'          => [5, null, 'index.php?option=com_osdownloads&view=downloads&id=5'],
            'category and menu' => [5, 101, 'index.php?option=com_osdownloads&view=downloads&id=5&Itemid=101'],
            'root category'     => [0, null, 'index.php?option=com_osdownloads&view=downloads&id=0'],
            'empty menu id'     => [5, 0, 'index.php?option=com_osdownloads&view=downloads&id=5'],
        ];
    }

    #[DataProvider('fileListProvider')]
    public function testGetFileListRoute(?int $id, ?int $itemId, string $expected)
    {
        $this->assertSame($expected, $this->route->getFileListRoute($id, $itemId));
    }

    public static function categoryListProvider(): array
    {
        return [
            'no arguments'      => [null, null, 'index.php?option=com_osdownloads&view=categories'],
            'category'          => [3, null, 'index.php?option=com_osdownloads&view=categories&id=3'],
            'category and menu' => [3, 101, 'index.php?option=com_osdownloads&view=categories&id=3&Itemid=101'],
            'empty menu id'     => [3, 0, 'index.php?option=com_osdownloads&view=categories&id=3'],
        ];
    }

    #[DataProvider('categoryListProvider')]
    public function testGetCategoryListRoute(?int $id, ?int $itemId, string $expected)
    {
        $this->assertSame($expected, $this->route->getCategoryListRoute($id, $itemId));
    }

    public function testGetViewItemRoute()
    {
        $this->assertSame(
            'index.php?option=com_osdownloads&view=item&id=7',
            $this->route->getViewItemRoute(7)
        );
        $this->assertSame(
            'index.php?option=com_osdownloads&view=item&id=7&Itemid=101',
            $this->route->getViewItemRoute(7, 101)
        );
    }

    public function testGetFileDownloadContentRoute()
    {
        $this->assertSame(
            'index.php?option=com_osdownloads&task=routedownload&tmpl=component&id=7',
            $this->route->getFileDownloadContentRoute(7)
        );
        $this->assertSame(
            'index.php?option=com_osdownloads&task=routedownload&tmpl=component&id=7&Itemid=101',
            $this->route->getFileDownloadContentRoute(7, 101)
        );
    }

    public function testAdminRoutes()
    {
        $this->assertSame('index.php?option=com_osdownloads&view=files', $this->route->getAdminFileListRoute());
        $this->assertSame(
            'index.php?option=com_categories&extension=com_osdownloads',
            $this->route->getAdminCategoryListRoute()
        );
        $this->assertSame('index.php?option=com_osdownloads&view=emails', $this->route->getAdminEmailListRoute());
        $this->assertSame('index.php?option=com_osdownloads', $this->route->getAdminMainViewRoute());
        $this->assertSame(
            'index.php?option=com_osdownloads&task=files.saveOrderAjax&tmpl=component',
            $this->route->getAdminSaveOrderingRoute()
        );
    }
}
