<?php
/**
 * @package   com_osdownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace Alledia\OSDownloads\Free\Helper;

use Alledia\Framework\Factory;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use JFactory;
use JLog;
use JText;
use SefAdvanceHelper;

defined('_JEXEC') or die();

/**
 * OSDownloads Component Route Helper.
 */
class SEF
{
    /**
     * Returns the category id based on the file id.
     *
     * @param int $fileId
     *
     * @return int
     */
    public function getCategoryIdFromFileId($fileId)
    {
        $db = JFactory::getDbo();

        $query = $db->getQuery(true)
            ->select('cate_id')
            ->from('#__osdownloads_documents')
            ->where('id = ' . (int)$fileId);


        $categoryId = $db->setQuery($query)->loadResult();

        if (empty($categoryId)) {
            JLog::add(
                JText::sprintf(
                    'COM_OSDOWNLOADS_ERROR_FILE_NOT_FOUND',
                    $fileId,
                    'getCategoryIdFromFileId'
                ),
                JLog::WARNING
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
     */
    public function buildCategoriesPath(&$categories, $categoryId, $categorySegmentToSkip = null)
    {
        if (empty($categoryId)) {
            return;
        }

        $category = $this->getCategory($categoryId);

        $categoriesToSkip = array();
        if (!empty($categorySegmentToSkip)) {
            $categoriesToSkip = explode('/', $categorySegmentToSkip);
        }

        if (!empty($category) && $category->alias !== 'root' && ! in_array($category->alias, $categoriesToSkip)) {
            $categories[] = $category->alias;
        }

        if (!empty($category) && $category->parent_id) {
            $this->buildCategoriesPath($categories, $category->parent_id);
        }
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
        $categories = array();

        $this->buildCategoriesPath($categories, $categoryId, $categorySegmentToSkip);

        for ($i = count($categories) - 1; $i >= 0; $i--) {
            $segments[] = $categories[$i];
        }

        return $segments;
    }

    /**
     * Append menu path segments to the segments array, returning the new
     * array of segments.
     *
     * @param  array     $segments
     * @param  JMenuItem $menu
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
        $db = JFactory::getDbo();

        $query = $db->getQuery(true)
            ->select('alias')
            ->from('#__osdownloads_documents')
            ->where('id = ' . $db->quote((int)$id));

        $alias = $db->setQuery($query)->loadResult();

        if (empty($alias)) {
            JLog::add(
                JText::sprintf(
                    'COM_OSDOWNLOADS_ERROR_FILE_NOT_FOUND',
                    $id,
                    'getFileAlias'
                ),
                JLog::WARNING
            );
        }

        return urlencode($alias);
    }

    /**
     * Return the file object from alias
     *
     * @param  string $alias
     *
     * @return StdClass
     */
    public function getFileFromAlias($alias)
    {
        $db = JFactory::getDbo();

        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__osdownloads_documents')
            ->where('alias = ' . $db->quote($alias));

        $file = $db->setQuery($query)->loadObject();

        if (empty($file)) {
            JLog::add(
                JText::sprintf(
                    'COM_OSDOWNLOADS_ERROR_FILE_NOT_FOUND',
                    $alias,
                    'getFileIdFromAlias'
                ),
                JLog::WARNING
            );
        }

        return $file;
    }

    /**
     * Returns the id of a file based on the file's alias.
     *
     * @param string $alias
     *
     * @return int|null
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
     * @return stdClass
     */
    public function getCategory($id)
    {
        $db = JFactory::getDBO();

        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__categories')
            ->where('id = ' . (int)$id);

        $category = $db->setQuery($query)->loadObject();

        if (!is_object($category)) {
            JLog::add(
                JText::sprintf(
                    'COM_OSDOWNLOADS_ERROR_CATEGORY_NOT_FOUND',
                    $id,
                    'getCategory'
                ),
                JLog::WARNING
            );
        }

        return $category;
    }

    /**
     * Returns the category as object based on the alias.
     *
     * @param string $alias
     *
     * @return stdClass
     */
    public function getCategoryFromAlias($alias)
    {
        $db = JFactory::getDBO();

        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__categories')
            ->where(
                array(
                    'extension = ' . $db->quote('com_osdownloads'),
                    'alias = ' . $db->quote($alias)
                )
            );

        $category = $db->setQuery($query)->loadObject();

        if (!is_object($category)) {
            JLog::add(
                JText::sprintf(
                    'COM_OSDOWNLOADS_ERROR_CATEGORY_NOT_FOUND',
                    $alias,
                    'getCategoryFromAlias'
                ),
                JLog::WARNING
            );
        }

        return $category;
    }

    /**
     * Returns the file category as object based on the file id.
     *
     * @param int $id
     *
     * @return stdClass
     */
    public function getCategoryFromFileId($fileId)
    {
        $categoryId = $this->getCategoryIdFromFileId($fileId);

        if (!empty($categoryId)) {
            $category = $this->getCategory($categoryId);

            return $category;
        }

        return false;
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
        $vars = array();

        parse_str($link, $vars);

        if (isset($vars['id'])) {
            return (int) $vars['id'];
        }

        return null;
    }

    /**
     * Look for a menu item related to the given category id.
     *
     * @param  int $categoryId
     *
     * @return stdClass|false
     */
    public function getMenuItemForCategory($categoryId)
    {
        $db = JFactory::getDbo();

        // Look for the exact menu
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__menu')
            ->where('type = ' . $db->quote('component'))
            ->where('published = ' . $db->quote('1'))
            ->where('link = ' . $db->quote(Route::getFileListRoute($categoryId)));

        $menu = $db->setQuery($query)->loadObject();

        return $menu;
    }

    /**
     * Look for a menu item related to the given category id.
     *
     * @param  int $fileId
     *
     * @return stdClass|false
     */
    public function getMenuItemForFile($fileId)
    {
        $db = JFactory::getDbo();

        // Look for the exact menu
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__menu')
            ->where('type = ' . $db->quote('component'))
            ->where('published = ' . $db->quote('1'))
            ->where('link = ' . $db->quote(Route::getViewItemRoute($fileId)));

        $menu = $db->setQuery($query)->loadObject();

        return $menu;
    }

    /**
     * Look for a menu item related to OSDownloads.
     *
     * @return array
     */
    public function getMenuItemsForComponent()
    {
        $db = JFactory::getDbo();

        // Look for the exact menu
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__menu')
            ->where('type = ' . $db->quote('component'))
            ->where('published = ' . $db->quote('1'))
            ->where('link LIKE ' . $db->quote('%option=com_osdownloads%'));

        $menus = $db->setQuery($query)->loadObjectList();

        return $menus;
    }

    /**
     * Look for a menu item based on the path
     *
     * @param  string  $path
     *
     * @return stdClass|null
     */
    public function getMenuItemsFromPath($path)
    {
        $db = JFactory::getDbo();

        // Look for the exact menu
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__menu')
            ->where('type = ' . $db->quote('component'))
            ->where('published = ' . $db->quote('1'))
            ->where('path = ' . $db->quote($path));

        $menus = $db->setQuery($query)->loadObjectList();

        return $menus;
    }

    /**
     * Look for a menu item related to the most close category up in the tree,
     * recursively, including the root category.
     *
     * @param  int $categoryId
     *
     * @return JMenuItem|false
     */
    public function getMenuItemForCategoryTreeRecursively($categoryId)
    {
        // Is there a menu for the given category?
        $menu = $this->getMenuItemForCategory($categoryId);

        if (empty($menu)) {
            // No menu found. Try the parent category.
            $category = $this->container->helperSEF->getCategory($categoryId);

            if (!empty($category)) {
                return $this->getMenuItemForCategoryTreeRecursively((int) $category->parent_id);
            }
        }

        return $menu;
    }

    /**
     * Returns the last item of the array not considering empty items.
     * Somes rotes, with trailing slash can produce an empty segment item,
     * specially when using SEF Advance. The array is modified, having the
     * last items removed.
     *
     * @param  array  $array
     *
     * @return mix
     */
    public function getLastNoEmptyArrayItem(array &$array)
    {
        if (!empty($array)) {
            $lastItem = array_pop($array);

            if (empty($lastItem)) {
                $lastItem = $this->getLastNoEmptyArrayItem($array);
            }
        }

        return $lastItem;
    }
}
