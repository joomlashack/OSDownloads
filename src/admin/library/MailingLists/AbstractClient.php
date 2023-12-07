<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2005-2023 Joomlashack.com. All rights reserved
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

namespace Alledia\OSDownloads\MailingLists;

use Alledia\Framework\Factory;
use Alledia\Framework\Joomla\AbstractTable;
use Alledia\OSDownloads\Free;
use CategoriesTableCategory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Table\Category;
use Joomla\CMS\Table\Table;
use Joomla\CMS\User\User;
use Joomla\Registry\Registry;
use OsdownloadsTableDocument;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die();

// phpcs:enable PSR1.Files.SideEffects

abstract class AbstractClient
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
    protected static $documents = [];

    /**
     * @var CategoriesTableCategory[]|Category[]
     */
    protected static $categories = [];

    public function __construct(AbstractTable $table)
    {
        $this->table = $table;
        $this->registerObservers();
    }

    /**
     * @return void
     */
    abstract protected function registerObservers(): void;

    /**
     * For customizing in subclasses. Prevents any access to a particular mailing list
     * if local dependencies are not available
     *
     * @return bool
     */
    public static function checkDependencies(): bool
    {
        return true;
    }

    /**
     * @return bool
     */
    public static function isEnabled(): bool
    {
        return true;
    }

    /**
     * @param ?int $documentId
     *
     * @return ?OsdownloadsTableDocument
     */
    protected function getDocument(?int $documentId = null): ?OsdownloadsTableDocument
    {
        $documentId = (int)($documentId ?: $this->table->get('document_id'));
        if (isset(static::$documents[$documentId]) == false) {
            /** @var OsdownloadsTableDocument $document */
            $document = Table::getInstance('Document', 'OsdownloadsTable');
            $document->load($documentId);

            static::$documents[$documentId] = $document->get('id') ? $document : false;
        }

        return static::$documents[$documentId] ?: null;
    }

    /**
     * @param string $email
     *
     * @return ?User
     */
    protected function getUserByEmail(string $email): ?User
    {
        $db    = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select('id')
            ->from('#__users')
            ->where('email = ' . $db->quote($email));

        if ($userId = (int)$db->setQuery($query)->loadResult()) {
            return Factory::getUser($userId);
        }

        return null;
    }

    /**
     * @param int $categoryId
     *
     * @return CategoriesTableCategory|Category
     */
    protected function getCategory(int $categoryId)
    {
        if ($categoryId && empty(static::$categories[$categoryId])) {
            /** @var CategoriesTableCategory|Category $category */
            $category = Table::getInstance('Category');
            $category->load($categoryId);

            if ($category->get('params') instanceof Registry == false) {
                $category->set('params', new Registry($category->get('params')));
            }
            static::$categories[$categoryId] = $category;
        }

        if (empty(static::$categories[$categoryId]) == false) {
            return static::$categories[$categoryId];
        }

        return null;
    }

    /**
     * @return Registry
     */
    protected static function getParams(): Registry
    {
        if (static::$params === null) {
            static::$params = ComponentHelper::getParams('com_osdownloads');
        }

        return static::$params;
    }

    /**
     * Get a parameter for a document.
     * Tracing back through category and global settings if needed
     *
     * @param int    $documentId
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    protected function getDocumentParam(int $documentId, string $key, $default = null)
    {
        if ($document = $this->getDocument($documentId)) {
            $value = $document->get('params')->get($key);
            if (is_array($value)) {
                $value = array_filter($value);
            }
        }

        if (empty($value)) {
            // Try category lookup
            if ($category = $this->getCategory($document->get('cate_id'))) {
                $value = $category->get('params')->get($key);
                if (is_array($value)) {
                    $value = array_filter($value);
                }
            }

            if (empty($value)) {
                // Try global
                $value = static::getParams()->get($key);
                if (is_array($value)) {
                    $value = array_filter($value);
                }
            }
        }

        return $value ?: $default;
    }

    /**
     * @param string  $message
     * @param int     $level
     * @param ?string $category
     *
     * @return void
     */
    protected function logError(string $message, int $level = Log::ALERT, ?string $category = null): void
    {
        if ($category == false) {
            $classParts = explode('\\', get_class($this));
            $category   = array_pop($classParts);
        }

        Log::addLogger(['text_file' => 'osdownloads.log.php'], Log::ALL, $category);
        Log::add($message, $level, $category);
    }
}
