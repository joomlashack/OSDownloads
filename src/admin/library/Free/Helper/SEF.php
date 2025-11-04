<?php

/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2005-2025 Joomlashack.com. All rights reserved
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

namespace Alledia\OSDownloads\Free\Helper;

use Alledia\OSDownloads\Container;
use Alledia\OSDownloads\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Menu\MenuItem;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die();

// phpcs:enable PSR1.Files.SideEffects

class SEF
{
    /**
     * @var MenuItem[]
     */
    protected static $menuItemsById = null;

    /**
     * @var Container
     */
    protected $container;

    /**
     * @return void
     * @throws \Exception
     */
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
    public function getCategoryIdFromFileId(int $fileId): int
    {
        $db = Factory::getDbo();

        $query = $db->getQuery(true)
            ->select('cate_id')
            ->from('#__osdownloads_documents')
            ->where('id = ' . $fileId);


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
     * @param array   $categories
     * @param int     $categoryId
     * @param ?string $skipCateogry
     *
     * @return array
     */
    public function buildCategoriesPath(
        array $categories,
        int $categoryId,
        ?string $skipCateogry = null
    ): array {
        if (empty($categoryId)) {
            return [];
        }

        $category = $this->getCategory($categoryId);

        $categoriesToSkip = [];
        if ($skipCateogry) {
            $categoriesToSkip = explode('/', $skipCateogry);
        }

        if (is_object($category) && $category->alias !== 'root' && !in_array($category->alias, $categoriesToSkip)) {
            $categories[] = $category->alias;
        }

        if (is_object($category) && $category->parent_id) {
            $categories = $this->buildCategoriesPath($categories, $category->parent_id, $skipCateogry);
        }

        return $categories;
    }

    /**
     * Append the category path to the segments and return the new array
     * of segments
     *
     * @param array   $segments
     * @param int     $categoryId
     * @param ?string $skipCateogry
     *
     * @return  array
     */
    public function appendCategoriesToSegments(
        array $segments,
        int $categoryId,
        ?string $skipCateogry = null
    ): array {
        // Append the categories before the alias of the file
        $categories = $this->buildCategoriesPath([], $categoryId, $skipCateogry);

        for ($i = count($categories) - 1; $i >= 0; $i--) {
            $segments[] = $categories[$i];
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
    public function getFileAlias(int $id): string
    {
        $db = Factory::getDbo();

        $query = $db->getQuery(true)
            ->select('alias')
            ->from('#__osdownloads_documents')
            ->where('id = ' . $db->quote($id));

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
     * @param string  $alias
     * @param ?string $path
     *
     * @return ?object
     */
    public function getFileFromAlias(string $alias, ?string $path = null): ?object
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

        } elseif (empty($path) && count($files) === 1) {
            // Only one file and no path to check
            return $files[0];

        } else {
            // We need to check the path of each file
            foreach ($files as $file) {
                // Get the file category
                $category = $this->getCategory($file->cate_id);

                if (empty($category) == false && $category->path === $path) {
                    return $file;
                }
            }
        }

        return null;
    }

    /**
     * Returns the category as object based on the id.
     *
     * @param int $id
     *
     * @return ?object
     */
    public function getCategory(int $id): ?object
    {
        $db = Factory::getDbo();

        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__categories')
            ->where('id = ' . $id);

        $category = $db->setQuery($query)->loadObject();

        if (empty($category)) {
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
    public function getCategoriesFromAlias(string $alias): array
    {
        $db = Factory::getDbo();

        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__categories')
            ->where([
                'extension = ' . $db->quote('com_osdownloads'),
                'alias = ' . $db->quote($alias),
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
    public function getCategoryFromAlias(string $alias, ?string $path = null): ?object
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
        if (count($categories) === 1 || (empty($path) && empty($categories) == false)) {
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
    public function getCategoryFromFileId(int $fileId): ?object
    {
        $categoryId = $this->getCategoryIdFromFileId($fileId);

        return $categoryId ? $this->getCategory($categoryId) : null;
    }

    /**
     * Returns the id found in the query of the link.
     *
     * @param string $link
     *
     * @return ?int
     */
    public function getIdFromLink(string $link): ?int
    {
        $vars = [];

        parse_str($link, $vars);

        if (isset($vars['id'])) {
            return (int)$vars['id'];
        }

        return null;
    }

    /**
     * Get the list of menu items
     *
     * @return MenuItem[]
     * @throws \Exception
     */
    protected function getMenuItems(): array
    {
        $app       = Factory::getApplication();
        $menu      = $app->getMenu('site');
        $menuItems = $menu->getItems('component', 'com_osdownloads');

        static::$menuItemsById = [];

        foreach ($menuItems as $menuItem) {
            static::$menuItemsById[$menuItem->id] = $menuItem;
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
    public function getMenuItemForListOfFiles(int $categoryId): ?MenuItem
    {
        if (empty(static::$menuItemsById) == false) {
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
    public function getMenuItemForFile(int $fileId): ?object
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
     * Look for a menu item related to the most close category up in the tree,
     * recursively, including the root category.
     *
     * @param int $categoryId
     *
     * @return ?MenuItem
     */
    public function getMenuItemForCategoryTreeRecursively(int $categoryId): ?MenuItem
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
