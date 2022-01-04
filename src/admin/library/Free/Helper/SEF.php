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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Menu\MenuItem;

defined('_JEXEC') or die();

/**
 * OSDownloads Component Route Helper.
 */
class SEF
{
    /**
     * @var MenuItem[]
     */
    protected static $menuItemsById = null;

    /**
     * @var object
     */
    protected $container;

    public function __construct()
    {
        $this->container = Factory::getPimpleContainer();

        $this->getMenuItems();
    }

    /**
     * Returns the category id based on the file id.
     *
     * @param int $fileId
     *
     * @return int
     */
    public function getCategoryIdFromFileId($fileId)
    {
        $db = Factory::getDbo();

        $query = $db->getQuery(true)
            ->select('cate_id')
            ->from('#__osdownloads_documents')
            ->where('id = ' . (int)$fileId);


        $categoryId = $db->setQuery($query)->loadResult();

        if (empty($categoryId)) {
            Log::add(
                Text::sprintf(
                    'COM_OSDOWNLOADS_ERROR_FILE_NOT_FOUND',
                    $fileId,
                    'getCategoryIdFromFileId'
                ),
                Log::WARNING
            );
        }

        return $categoryId;
    }

    /**
     * Build the path to a category, considering the parent categories.
     *
     * @param array  $categories
     * @param int    $categoryId
     * @param string $categorySegmentToSkip
     *
     * @return array
     */
    public function buildCategoriesPath($categories, $categoryId, $categorySegmentToSkip = null)
    {
        if (empty($categoryId)) {
            return [];
        }

        $category = $this->getCategory($categoryId);

        $categoriesToSkip = [];
        if ($categorySegmentToSkip) {
            $categoriesToSkip = explode('/', $categorySegmentToSkip);
        }

        if (is_object($category) && $category->alias !== 'root' && !in_array($category->alias, $categoriesToSkip)) {
            $categories[] = $category->alias;
        }

        if (is_object($category) && $category->parent_id) {
            $categories = $this->buildCategoriesPath($categories, $category->parent_id, $categorySegmentToSkip);
        }

        return $categories;
    }

    /**
     * Append the category path to the segments and return the new array
     * of segments
     *
     * @param array  $segments
     * @param int    $categoryId
     * @param string $categorySegmentToSkip
     *
     * @return  array
     */
    public function appendCategoriesToSegments($segments, $categoryId, $categorySegmentToSkip = null)
    {
        // Append the categories before the alias of the file
        $categories = $this->buildCategoriesPath([], $categoryId, $categorySegmentToSkip);

        for ($i = count($categories) - 1; $i >= 0; $i--) {
            $segments[] = $categories[$i];
        }

        return $segments;
    }

    /**
     * Append menu path segments to the segments array, returning the new
     * array of segments.
     *
     * @param string[] $segments
     * @param MenuItem $menu
     *
     * @return array
     */
    public function appendMenuPathToSegments($segments, $menu)
    {
        if (is_array($segments) && isset($menu->path)) {
            $segments = array_merge(
                $segments,
                explode('/', $menu->path)
            );
        }

        return $segments;
    }

    /**
     * Returns the alias of a file based on the file id.
     *
     * @param int $id
     *
     * @return string
     */
    public function getFileAlias($id)
    {
        $db = Factory::getDbo();

        $query = $db->getQuery(true)
            ->select('alias')
            ->from('#__osdownloads_documents')
            ->where('id = ' . $db->quote((int)$id));

        $alias = $db->setQuery($query)->loadResult();

        if (empty($alias)) {
            Log::add(
                Text::sprintf(
                    'COM_OSDOWNLOADS_ERROR_FILE_NOT_FOUND',
                    $id,
                    'getFileAlias'
                ),
                Log::WARNING
            );
        }

        return urlencode($alias);
    }

    /**
     * Return the file object from alias
     *
     * @param string $alias
     * @param string $path
     *
     * @return object|false
     */
    public function getFileFromAlias($alias, $path = null)
    {
        $db = Factory::getDbo();

        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__osdownloads_documents')
            ->where('alias = ' . $db->quote($alias));

        $files = $db->setQuery($query)->loadObjectList();

        if (empty($files)) {
            Log::add(
                Text::sprintf(
                    'COM_OSDOWNLOADS_ERROR_FILE_NOT_FOUND',
                    $alias,
                    'getFileIdFromAlias'
                ),
                Log::WARNING
            );

            return false;
        }

        // Do we have only one file and no path to check?
        if (empty($path) && count($files) === 1) {
            return $files[0];
        }

        // We have more files. We need to check the path of each file
        foreach ($files as $file) {
            // Get the file category
            $category = $this->getCategory($file->cate_id);

            if (!empty($category) && $category->path === $path) {
                return $file;
            }
        }

        return false;
    }

    /**
     * Returns the id of a file based on the file's alias.
     *
     * @param string $alias
     *
     * @return int|false
     */
    public function getFileIdFromAlias($alias)
    {
        $file = $this->getFileFromAlias($alias);

        if (!empty($file)) {
            return $file->id;
        }

        return false;
    }

    /**
     * Returns the category as object based on the id.
     *
     * @param int $id
     *
     * @return object
     */
    public function getCategory($id)
    {
        $db = Factory::getDbo();

        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__categories')
            ->where('id = ' . (int)$id);

        $category = $db->setQuery($query)->loadObject();

        if (!is_object($category)) {
            Log::add(
                Text::sprintf(
                    'COM_OSDOWNLOADS_ERROR_CATEGORY_NOT_FOUND',
                    $id,
                    'getCategory'
                ),
                Log::WARNING
            );
        }

        return $category;
    }

    /**
     * Returns the list of categories based on the alias.
     *
     * @param string $alias
     *
     * @return object[]
     */
    public function getCategoriesFromAlias($alias)
    {
        $db = Factory::getDbo();

        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__categories')
            ->where([
                'extension = ' . $db->quote('com_osdownloads'),
                'alias = ' . $db->quote($alias)
            ]);

        return $db->setQuery($query)->loadObjectList();
    }

    /**
     * Returns the category as object based on the alias.
     *
     * @param string  $alias
     * @param ?string $path
     *
     * @return ?object
     */
    public function getCategoryFromAlias($alias, $path = null)
    {
        $categories = $this->getCategoriesFromAlias($alias);

        if (empty($categories)) {
            Log::add(
                Text::sprintf(
                    'COM_OSDOWNLOADS_ERROR_CATEGORY_NOT_FOUND',
                    $alias,
                    'getCategoryFromAlias'
                ),
                Log::WARNING
            );
        }

        // Do we have only one file?
        if (count($categories) === 1 || (empty($path) && !empty($categories))) {
            return $categories[0];
        }

        // We have more files. We need to check the path of each file
        foreach ($categories as $category) {
            if ($category->path === $path) {
                return $category;
            }
        }

        return null;
    }

    /**
     * Returns the file category as object based on the file id.
     *
     * @param int $fileId
     *
     * @return ?object
     */
    public function getCategoryFromFileId($fileId)
    {
        $categoryId = $this->getCategoryIdFromFileId($fileId);

        return $categoryId ? $this->getCategory($categoryId) : null;
    }

    /**
     * Returns the id found in the query of the link.
     *
     * @param string link
     *
     * @return int|null
     */
    public function getIdFromLink($link)
    {
        $vars = [];

        parse_str($link, $vars);

        if (isset($vars['id'])) {
            return (int)$vars['id'];
        }

        return null;
    }

    /**
     * Returns the last item of the array not considering empty items.
     * Some routes, with trailing slash can produce an empty segment item,
     * specially when using SEF Advance. The array is modified, having the
     * last items removed.
     *
     * @param array $array
     *
     * @return mixed
     */
    public function getLastNoEmptyArrayItem(array &$array)
    {
        if (!empty($array)) {
            $lastItem = array_pop($array);

            if (empty($lastItem)) {
                $lastItem = $this->getLastNoEmptyArrayItem($array);
            }
        }

        return $lastItem ?? null;
    }

    /**
     * Get the list of menu items
     *
     * @return MenuItem[]
     * @throws \Exception
     */
    protected function getMenuItems()
    {
        // Get all relevant menu items.
        $app       = Factory::getApplication();
        $menu      = $app->getMenu();
        $menuItems = $menu->getItems('component', 'com_osdownloads');

        static::$menuItemsById = [];

        foreach ($menuItems as $item) {
            static::$menuItemsById[$item->id] = $item;
        }

        return static::$menuItemsById;
    }

    /**
     * Look for a menu item related to the given category id.
     *
     * @param int $categoryId
     *
     * @return ?MenuItem
     */
    public function getMenuItemForListOfFiles($categoryId)
    {
        if (!empty(static::$menuItemsById)) {
            foreach (static::$menuItemsById as $menu) {
                $link = $this->container->helperRoute->getFileListRoute($categoryId);

                if ('downloads' === $menu->query['view'] && $menu->link === $link) {
                    return $menu;
                }
            }
        }

        return null;
    }

    /**
     * Look for a menu item related to the given category id.
     *
     * @param int $fileId
     *
     * @return ?object
     */
    public function getMenuItemForFile($fileId)
    {
        if (!empty(static::$menuItemsById)) {
            foreach (static::$menuItemsById as $menu) {
                if ($menu->link === $this->container->helperRoute->getViewItemRoute($fileId)) {
                    return $menu;
                }
            }
        }

        return null;
    }

    /**
     * Look for a menu item related to OSDownloads.
     *
     * @return array
     */
    public function getMenuItemsForComponent()
    {
        return static::$menuItemsById;
    }

    /**
     * Look for a menu item based on the path
     *
     * @param string $path
     *
     * @return ?object
     */
    public function getMenuItemsFromPath($path)
    {
        if (!empty(static::$menuItemsById)) {
            foreach (static::$menuItemsById as $menu) {
                if ($menu->path === $path) {
                    return $menu;
                }
            }
        }

        return null;
    }

    /**
     * Look for a menu item related to the most close category up in the tree,
     * recursively, including the root category.
     *
     * @param int $categoryId
     *
     * @return ?MenuItem
     */
    public function getMenuItemForCategoryTreeRecursively($categoryId)
    {
        // Is there a menu for the given category?
        $menu = $this->getMenuItemForListOfFiles($categoryId);

        if (empty($menu)) {
            // No menu found. Try the parent category.
            $category = $this->getCategory($categoryId);

            if (!empty($category)) {
                return $this->getMenuItemForCategoryTreeRecursively((int)$category->parent_id);
            }

            // Check the root category, since no other category seems to be on a menu
            if ($categoryId > 0) {
                return $this->getMenuItemForCategoryTreeRecursively(0);
            }
        }

        return $menu;
    }

    /**
     * Get a menu item related to the component, by id.
     *
     * @param int $id
     *
     * @return object|bool
     */
    public function getMenuItemById($id)
    {
        if (!empty(static::$menuItemsById) && isset(static::$menuItemsById[$id])) {
            return static::$menuItemsById[$id];
        }

        return false;
    }

    /**
     * Get a menu item related to the component, filtering by the query.
     * You don't need to give the full query, but all given query vars should
     * match.
     *
     * @param array $query
     *
     * @return ?object
     */
    public function getMenuItemByQuery(array $query): ?object
    {
        if (!empty(static::$menuItemsById)) {
            foreach (static::$menuItemsById as $menuItem) {
                if (array_intersect($query, $menuItem->query) == $query) {
                    return $menuItem;
                }
            }
        }

        return null;
    }
}
