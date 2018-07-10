<?php
/**
 * @package    OSDownloads
 * @contact    www.joomlashack.com, help@joomlashack.com
 * @copyright  2016-2018 Open Source Training, LLC. All rights reserved
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

use Alledia\OSDownloads\Free;
use CategoriesTableCategory;
use JObservableInterface;
use JObserverInterface;
use Joomla\Registry\Registry;
use JTable;
use OsdownloadsTableDocument;

defined('_JEXEC') or die();

abstract class AbstractClient implements JObserverInterface
{
    /**
     * @var Free\Joomla\Table\Email
     */
    protected $table = null;

    /**
     * @var Registry
     */
    protected static $params = null;

    /**
     * @var OsdownloadsTableDocument[]
     */
    protected static $documents = array();

    /**
     * @var CategoriesTableCategory[]
     */
    protected static $categories = array();

    public function __construct(JObservableInterface $table)
    {
        $table->attachObserver($this);
        $this->table = $table;
    }

    /**
     * @param   JObservableInterface $observableObject The observable subject object
     * @param   array                $params           Params for this observer
     *
     * @return  JObserverInterface
     */
    public static function createObserver(JObservableInterface $observableObject, $params = array())
    {
        $observer = new static($observableObject);

        return $observer;
    }

    /**
     * For customizing in subclasses. Prevents any access to a particular mailing list
     * if local dependencies are not available
     *
     * @return bool
     */
    public static function checkDependencies()
    {
        return true;
    }

    /**
     * For use in subclasses for display of plugin options on any form
     * other than the configuration form
     *
     * @return bool
     */
    public static function isEnabled()
    {
        return true;
    }

    /**
     * @param int $documentId
     *
     * @return OsdownloadsTableDocument
     */
    protected function getDocument($documentId = null)
    {
        $documentId = (int)($documentId ?: $this->table->document_id);
        if (!isset(static::$documents[$documentId])) {
            /** @var OsdownloadsTableDocument $document */
            $document = JTable::getInstance('Document', 'OsdownloadsTable');
            $document->load($documentId);
            static::$documents[$documentId] = $document->id ? $document : false;
        }

        return static::$documents[$documentId] ?: null;
    }


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

    /**
     * @return Registry
     */
    protected static function getParams()
    {
        if (static::$params === null) {
            static::$params = \JComponentHelper::getParams('com_osdownloads');
        }

        return static::$params;
    }

}
