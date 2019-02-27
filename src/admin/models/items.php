<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2005-2019 Joomlashack.com. All rights reserved
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

use Joomla\CMS\Application\AdministratorApplication;

defined('_JEXEC') or die();

require_once __DIR__ . '/abstract/items.php';

class OSDownloadsModelItems extends OSDownloadsModelItemsAbstract
{
    protected $context = 'com_osdownloads.document';

    /**
     * @return void
     * @throws Exception
     */
    protected function populateState()
    {
        /** @var AdministratorApplication $app */
        $app = JFactory::getApplication();

        $search = $app->getUserStateFromRequest($this->context . '.filter.search', 'search', '', 'string');
        $this->setState('filter.search', $search);

        $category = $app->getUserStateFromRequest($this->context . '.filter.category_id', 'flt_cate_id', null, 'int');
        $this->setState('filter.category_id', $category);

        $limit = $app->getUserStateFromRequest(
            $this->context . 'list.limit',
            'limit',
            $app->get('list_limit'),
            'init'
        );
        $this->setState('list.limit', $limit);

        $fullOrdering = $app->getUserStateFromRequest(
            'com_osdownloads.document.filter_order',
            'filter_order',
            'doc.id asc',
            'string'
        );

        list($ordering, $direction) = explode(' ', $fullOrdering, 2);

        $this->setState('list.ordering', $ordering);
        $this->setState('list.direction', strtolower($direction));

        parent::populateState();
    }
}
