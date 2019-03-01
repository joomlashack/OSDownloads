<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2005-2019 Joomlashack.com. All rights reserved
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

defined('_JEXEC') or die();

/**
 * OSDownloads Component Route Helper.
 */
class Route
{
    /**
     * Get the file download route.
     *
     * @param   integer $id The id of the file
     *
     * @return  string  The file download route.
     */
    public function getFileDownloadRoute($id)
    {
        $id = abs((int)$id);

        // Create the link
        $link = 'index.php?option=com_osdownloads&task=download&tmpl=component&id=' . $id;

        return $link;
    }

    /**
     * Get the file list route
     *
     * @param   integer $id     The id of the category
     * @param   integer $itemId The menu item id
     *
     * @return  string  The file route
     */
    public function getFileListRoute($id = null, $itemId = 0)
    {

        // Create the link
        $link = 'index.php?option=com_osdownloads&view=downloads';

        if (!is_null($id)) {
            $id = (int) $id;
            $link .= '&id=' . $id;
        }

        // Should we add the item id?
        if (!empty($itemId)) {
            $itemId = abs((int)$itemId);

            $link .= '&Itemid=' . $itemId;
        }

        return $link;
    }

    /**
     * Get the category list route
     *
     * @param   integer $id     The id of the category
     * @param   integer $itemId The menu item id
     *
     * @return  string  The file route
     */
    public function getCategoryListRoute($id = null, $itemId = 0)
    {

        // Create the link
        $link = 'index.php?option=com_osdownloads&view=categories';

        if (!is_null($id)) {
            $id = (int) $id;
            $link .= '&id=' . $id;
        }

        // Should we add the item id?
        if (!empty($itemId)) {
            $itemId = abs((int)$itemId);

            $link .= '&Itemid=' . $itemId;
        }

        return $link;
    }

    /**
     * Get the view item route
     *
     * @param   integer $id     The id of the file
     * @param   integer $itemId The menu item id
     *
     * @return  string  The file route
     */
    public function getViewItemRoute($id, $itemId = 0)
    {
        $id = abs((int)$id);

        // Create the link
        $link = 'index.php?option=com_osdownloads&view=item&id=' . $id;

        // Should we add the item id?
        if (!empty($itemId)) {
            $itemId = abs((int)$itemId);

            $link .= '&Itemid=' . $itemId;
        }

        return $link;
    }

    /**
     * Get the file download content route
     *
     * @param   integer $id     The id of the file
     * @param   integer $itemId The menu item id
     *
     * @return  string  The file route
     */
    public function getFileDownloadContentRoute($id, $itemId = 0)
    {
        $id = abs((int)$id);

        // Create the link
        $link = 'index.php?option=com_osdownloads&task=routedownload&tmpl=component&id=' . $id;

        // Should we add the item id?
        if (!empty($itemId)) {
            $itemId = abs((int)$itemId);

            $link .= '&Itemid=' . $itemId;
        }

        return $link;
    }

    /**
     * Get the route for the file list in the admin
     *
     * @return  string  The files list route
     */
    public function getAdminFileListRoute()
    {
        // Create the link
        $link = 'index.php?option=com_osdownloads&view=files';

        return $link;
    }

    /**
     * Get the route for the categories list in the admin
     *
     * @return  string  The category list route
     */
    public function getAdminCategoryListRoute()
    {
        // Create the link
        $link = 'index.php?option=com_categories&extension=com_osdownloads';

        return $link;
    }

    /**
     * Get the route for the emails list in the admin
     *
     * @return  string  The emails list route
     */
    public function getAdminEmailListRoute()
    {
        // Create the link
        $link = 'index.php?option=com_osdownloads&view=emails';

        return $link;
    }

    /**
     * Get the route for the main admin view
     *
     * @return  string  The main view route
     */
    public function getAdminMainViewRoute()
    {
        // Create the link
        $link = 'index.php?option=com_osdownloads';

        return $link;
    }

    /**
     * Get the route for the admin save ordering URL.
     *
     * @return  string  The save ordering route
     */
    public function getAdminSaveOrderingRoute()
    {
        // Create the link
        $link = 'index.php?option=com_osdownloads&task=files.saveOrderAjax&tmpl=component';

        return $link;
    }
}
