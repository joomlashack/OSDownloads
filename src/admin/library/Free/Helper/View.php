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

use Alledia\OSDownloads\Free\Joomla\Component\Site as FreeComponentSite;
use JRoute;
use Alledia\OSDownloads\Factory;

defined('_JEXEC') or die();

/**
 * OSDownloads View Helper.
 */
class View
{
    /**
     * Build a list of category breadcrumbs.
     *
     * @param  int     $categoryId
     */
    public function buildCategoryBreadcrumbs($categoryId)
    {
        $paths = array();
        $this->buildPath($paths, $categoryId);

        $app       = Factory::getApplication();
        $container = Factory::getPimpleContainer();

        $pathway    = $app->getPathway();
        $itemID     = $app->input->getInt('Itemid');
        $countPaths = count($paths) - 1;
        $component  = FreeComponentSite::getInstance();

        $pathwayList = $pathway->getPathway();

        for ($i = $countPaths; $i >= 0; $i--) {
            $link   = $container->helperRoute->getFileListRoute($paths[$i]->id, $itemID);
            $route  = JRoute::_($link);
            $exists = false;

            // Check if the current category is already in the pathway, to ignore
            foreach ($pathwayList as $item) {
                if ($item->link === $link) {
                    $exists = true;
                    break;
                }
            }

            if (!$exists) {
                $pathway->addItem($paths[$i]->title, $route);
            }
        }
    }

    /**
     * Build a list of file breadcrumbs.
     *
     * @param  object  $file
     */
    public function buildFileBreadcrumbs($file)
    {
        $container = Factory::getPimpleContainer();

        // Check if the current file has a menu item
        $menu = $container->app->getMenu()->getActive();

        if (is_object($menu)) {
            if ('item' === $menu->query['view'] && $menu->query['id'] === $file->id) {
                // Yes, so do nothing
                return;
            }
        }

        $this->buildCategoryBreadcrumbs($file->cate_id);

        $app       = Factory::getApplication();
        $container = Factory::getPimpleContainer();

        $pathway = $app->getPathway();
        $itemID  = $app->input->getInt('Itemid');

        $pathwayList = $pathway->getPathway();

        $link   = $container->helperRoute->getViewItemRoute($file->id, $itemID);
        $route  = JRoute::_($link);
        $exists = false;

        if (!$exists) {
            $pathway->addItem($file->name, $link);
        }
    }

    /**
     * Build an inverse recurcive list of paths for categories' breadcrumbs.
     *
     * @param  array &$paths
     * @param  int   $categoryId
     */
    protected function buildPath(&$paths, $categoryId)
    {
        if (empty($categoryId)) {
            return;
        }

        $db = Factory::getDbo();

        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__categories')
            ->where(
                array(
                    'extension = ' . $db->quote('com_osdownloads'),
                    'id = ' . $db->quote((int) $categoryId)
                )
            );
        $category = $db->setQuery($query)->loadObject();

        if (!empty($category)) {
            $paths[] = $category;

            if ($category->parent_id) {
                $this->buildPath($paths, $category->parent_id);
            }
        }
    }
}
