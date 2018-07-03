<?php
/**
 * @package    OSDownloads
 * @contact    www.joomlashack.com, help@joomlashack.com
 * @copyright  2018 Open Source Training, LLC. All rights reserved
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
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

namespace Alledia\OSDownloads\MailingLists;

use CategoriesTableCategory;
use Joomla\Registry\Registry;
use JTable;

defined('_JEXEC') or die();

abstract class AbstractClient implements \JObserverInterface
{
    /**
     * @var CategoriesTableCategory[]
     */
    protected static $categories = array();

    /**
     * @param int $categoryId
     *
     * @return CategoriesTableCategory
     */
    protected function getCategory($categoryId)
    {
        $categoryId = (int)$categoryId;
        if ($categoryId && empty(static::$categories[$categoryId])) {
            /** @var CategoriesTableCategory $category */
            $category = JTable::getInstance('Category', 'JTable');
            $category->load($categoryId);

            if (!$category->params instanceof Registry) {
                $category->params = new Registry($category->params);
            }
            static::$categories[$categoryId] = $category;
        }

        if (!empty(static::$categories[$categoryId])) {
            return static::$categories[$categoryId];
        }

        return null;
    }
}
