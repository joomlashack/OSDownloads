<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2005-2022 Joomlashack.com. All rights reserved
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
use Joomla\CMS\Router\Route;

defined('_JEXEC') or die();

/**
 * OSDownloads View Helper.
 */
class View
{
    /**
     * Build a list of category breadcrumbs.
     *
     * @param int $categoryId
     *
     * @return void
     * @throws \Exception
     */
    public function buildCategoryBreadcrumbs($categoryId)
    {
        $app     = Factory::getApplication();
        $pathway = $app->getPathway();

        $pathwayItems = $pathway->getPathway();
        if ($pathwayItems) {
            $routing = Factory::getPimpleContainer()->helperRoute;
            $itemId  = $app->input->getInt('Itemid');

            $lastItem = [];
            parse_str(
                parse_url(
                    end($pathwayItems)->link,
                    PHP_URL_QUERY
                ),
                $lastItem
            );
            if ($lastItem['id'] == $categoryId && $lastItem['Itemid'] == $itemId) {
                // We're already on the required menu
                return;
            }

            $paths = [];
            $this->buildPath($paths, $categoryId);

            $paths = array_reverse($paths);
            foreach ($paths as $path) {
                $link   = $routing->getFileListRoute($path->id, $itemId);
                $route  = Route::_($link);
                $exists = false;

                // Check if the current category is already in the pathway, to ignore
                foreach ($pathwayItems as $pathwayItem) {
                    if ($pathwayItem->link === $link) {
                        $exists = true;
                        break;
                    }
                }

                if (!$exists) {
                    $pathway->addItem($path->title, $route);
                }
            }
        }
    }

    /**
     * Build a list of file breadcrumbs.
     *
     * @param object $file
     *
     * @return void
     * @throws \Exception
     */
    public function buildFileBreadcrumbs($file)
    {
        $container = Factory::getPimpleContainer();

        if ($activeMenu = $container->app->getMenu()->getActive()) {
            $view = $activeMenu->query['view'] ?? null;
            $id   = $activeMenu->query['id'] ?? null;
            if ($view == 'item' && $id == $file->id) {
                // Current menu is for this file
                return;
            }
        }

        $this->buildCategoryBreadcrumbs($file->cate_id);

        $app       = Factory::getApplication();
        $container = Factory::getPimpleContainer();

        $pathway = $app->getPathway();
        $itemId  = $app->input->getInt('Itemid');

        $link = $container->helperRoute->getViewItemRoute($file->id, $itemId);

        $pathway->addItem($file->name, $link);
    }

    /**
     * Build an inverse recurcive list of paths for categories' breadcrumbs.
     *
     * @param object[] &$paths
     * @param int       $categoryId
     */
    protected function buildPath(&$paths, $categoryId)
    {
        if (empty($categoryId)) {
            return;
        }

        $db = Factory::getDbo();

        $query    = $db->getQuery(true)
            ->select('*')
            ->from('#__categories')
            ->where([
                'extension = ' . $db->quote('com_osdownloads'),
                'id = ' . $db->quote((int)$categoryId)
            ]);
        $category = $db->setQuery($query)->loadObject();

        if ($category) {
            $paths[] = $category;

            if ($category->parent_id) {
                $this->buildPath($paths, $category->parent_id);
            }
        }
    }
}
