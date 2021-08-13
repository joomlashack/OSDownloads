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

namespace Alledia\OSDownloads\Free\Helper;

use Alledia\OSDownloads\Factory;

defined('_JEXEC') or die();

/**
 * OSDownloads Component Route Helper.
 */
class Route
{
    /**
     * Get the file download route.
     *
     * @param int $id The id of the file
     *
     * @return string  The file download route.
     */
    public function getFileDownloadRoute(int $id): string
    {
        $query = [
            'option' => 'com_osdownloads',
            'task'   => 'download',
            'tmpl'   => 'component',
            'id'     => $id
        ];

        if ($menu = Factory::getPimpleContainer()->helperSEF->getMenuItemForFile($id)) {
            $query['Itemid'] = $menu->id;
        }

        return 'index.php?' . http_build_query($query);
    }

    /**
     * Get the file list route
     *
     * @param ?int $id     The id of the category
     * @param ?int $itemId The menu item id
     *
     * @return string  The file route
     */
    public function getFileListRoute(?int $id = null, ?int $itemId = null): string
    {
        $query = [
            'option' => 'com_osdownloads',
            'view'   => 'downloads'
        ];

        if ($id !== null) {
            $query['id'] = $id;
        }

        if ($itemId) {
            $query['Itemid'] = $itemId;
        }

        return 'index.php?' . http_build_query($query);
    }

    /**
     * Get the category list route
     *
     * @param ?int $id     The id of the category
     * @param ?int $itemId The menu item id
     *
     * @return string  The file route
     */
    public function getCategoryListRoute(?int $id = null, ?int $itemId = null): string
    {
        $query = [
            'option' => 'com_osdownloads',
            'view'   => 'categories'
        ];

        if ($id !== null) {
            $query['id'] = $id;
        }

        if ($itemId) {
            $query['Itemid'] = $itemId;
        }

        return 'index.php?' . http_build_query($query);
    }

    /**
     * Get the view item route
     *
     * @param int  $id     The id of the file
     * @param ?int $itemId The menu item id
     *
     * @return string  The file route
     */
    public function getViewItemRoute(int $id, ?int $itemId = null): string
    {
        $query = [
            'option' => 'com_osdownloads',
            'view'   => 'item',
            'id'     => $id
        ];

        if ($itemId) {
            $query['Itemid'] = $itemId;
        }

        return 'index.php?' . http_build_query($query);
    }

    /**
     * Get the file download content route
     *
     * @param int  $id     The id of the file
     * @param ?int $itemId The menu item id
     *
     * @return string  The file route
     */
    public function getFileDownloadContentRoute(int $id, ?int $itemId = null): string
    {
        $query = [
            'option' => 'com_osdownloads',
            'task'   => 'routedownload',
            'tmpl'   => 'component',
            'id'     => $id
        ];

        if ($itemId) {
            $query['Itemid'] = $itemId;
        }

        return 'index.php?' . http_build_query($query);
    }

    /**
     * Get the route for the file list in the admin
     *
     * @return string  The files list route
     */
    public function getAdminFileListRoute(): string
    {
        return 'index.php?option=com_osdownloads&view=files';
    }

    /**
     * Get the route for the categories list in the admin
     *
     * @return string  The category list route
     */
    public function getAdminCategoryListRoute(): string
    {
        return 'index.php?option=com_categories&extension=com_osdownloads';
    }

    /**
     * Get the route for the emails list in the admin
     *
     * @return string  The emails list route
     */
    public function getAdminEmailListRoute(): string
    {
        return 'index.php?option=com_osdownloads&view=emails';
    }

    /**
     * Get the route for the main admin view
     *
     * @return string  The main view route
     */
    public function getAdminMainViewRoute(): string
    {
        return 'index.php?option=com_osdownloads';
    }

    /**
     * Get the route for the admin save ordering URL.
     *
     * @return string  The save ordering route
     */
    public function getAdminSaveOrderingRoute(): string
    {
        return 'index.php?option=com_osdownloads&task=files.saveOrderAjax&tmpl=component';
    }
}
