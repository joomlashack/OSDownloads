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

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Table\Table;

defined('_JEXEC') or die();

class OSDownloadsModelFiles extends ListModel
{
    /**
     * @inheritDoc
     */
    public function __construct($config = [])
    {
        $config['filter_fields'] = [
            'cate_id',
            'doc.access',
            'doc.downloaded',
            'doc.id',
            'doc.name',
            'doc.ordering',
            'doc.published',
            'published',
        ];

        parent::__construct($config);
    }

    /**
     * @inheritDoc
     */
    public function getItems()
    {
        $items = parent::getItems();
        if (!empty($items[0]) && !isset($items[0]->agreementLink)) {
            foreach ($items as $item) {
                $item->agreementLink = '';
                if ($item->require_agree && $item->agreement_article_id) {
                    $item->agreementLink = Route::_(ContentHelperRoute::getArticleRoute($item->agreement_article_id));
                }
            }
        }

        return $items;
    }

    /**
     * @inheritDoc
     */
    protected function getListQuery()
    {
        $db = $this->getDbo();

        $query = $db->getQuery(true)
            ->select([
                'doc.*',
                'cat.access AS cat_access',
                'cat.title AS cat_title',
                'vl.title AS access_title'
            ])
            ->from('#__osdownloads_documents AS doc')
            ->leftJoin(
                '#__categories AS cat'
                . ' ON (doc.cate_id = cat.id AND cat.extension = ' . $db->quote('com_osdownloads') . ')'
            )
            ->leftJoin(
                '#__viewlevels AS vl'
                . ' ON (doc.access = vl.id)'
            );

        $search = $this->getState('filter.search');
        if ($search) {
            $query->where(sprintf('doc.name LIKE %s', $db->quote("%{$search}%")));
        }

        $published = $this->getState('filter.published');
        if ($published != '') {
            $query->where('published = ' . (int)$published);
        }

        $categoryId = (int)$this->getState('filter.cate_id');
        if ($categoryId) {
            $query->where('doc.cate_id = ' . $categoryId);
        }

        $ordering  = $this->getState('list.ordering');
        $direction = $this->getState('list.direction');

        if ($ordering == 'doc.ordering') {
            $query->order('doc.cate_id ' . $direction);
        }
        $query->order($ordering . ' ' . $direction);

        return $query;
    }

    /**
     * @inheritDoc
     */
    public function getTable($name = 'Document', $prefix = 'OsdownloadsTable', $options = [])
    {
        /** @var OsdownloadsTableDocument $table */
        $table = Table::getInstance('Document', 'OsdownloadsTable', $options);

        return $table;
    }

    /**
     * @inheritDoc
     */
    protected function populateState($ordering = 'doc.id', $direction = 'desc')
    {
        parent::populateState($ordering, $direction);
    }
}
