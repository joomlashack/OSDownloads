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

namespace Alledia\OSDownloads\Free\Joomla\Model;

use Alledia\Framework\Joomla\Model\AbstractListModel;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die();

// phpcs:enable PSR1.Files.SideEffects

class Emails extends AbstractListModel
{
    /**
     * @inheritDoc
     */
    public function __construct($config = [])
    {
        $config['filter_fields'] = array_merge(
            $config['filter_fields'] ?? [],
            [
                'email.email',
                'doc.name',
                'cat.title',
                'email.downloaded_date',
                'email.id',
                'catid',
            ]
        );

        parent::__construct($config);
    }

    /**
     * @inheritDoc
     */
    protected function populateState($ordering = 'email.id', $direction = 'desc')
    {
        parent::populateState($ordering, $direction);
    }

    /**
     * @inheritDoc
     */
    protected function getListQuery()
    {
        $db = $this->getDbo();

        $query = $db->getQuery(true)
            ->select('email.*, doc.name AS doc_name, cat.title AS cate_name')
            ->from('#__osdownloads_emails email')
            ->leftJoin('#__osdownloads_documents doc ON (email.document_id = doc.id)')
            ->leftJoin('#__categories cat ON (cat.id = doc.catid)');

        $search = $this->getState('filter.search');
        if ($search) {
            $search = $db->quote('%' . $search . '%');
            $ors    = [
                'email.email LIKE ' . $search,
                'doc.name LIKE' . $search,
            ];
            $query->where(sprintf('(%)', join(' OR ', $ors)));
        }
        if ($categoryId = (int)$this->getState('filter.catid')) {
            $query->where('cat.id = ' . $categoryId);
        }

        $ordering  = $this->getState('list.ordering');
        $direction = $this->getState('list.direction');

        $query->order($ordering . ' ' . $direction);

        return $query;
    }
}
