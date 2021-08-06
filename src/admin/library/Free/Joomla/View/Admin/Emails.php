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

namespace Alledia\OSDownloads\Free\Joomla\View\Admin;

use Alledia\Framework\Joomla\View\Admin\AbstractList;
use Alledia\OSDownloads\Factory;
use Joomla\CMS\HTML\Helpers\Sidebar;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Pagination\Pagination;
use JToolbarHelper;

defined('_JEXEC') or die();

class Emails extends AbstractList
{
    /**
     * @var object
     */
    protected $flt = null;

    /**
     * @var array
     */
    protected $lists = null;

    /**
     * @var object
     */
    protected $item = null;

    /**
     * @var bool
     */
    protected $isPro = false;

    public function display($tpl = null)
    {
        $app = $this->app;

        $this->flt = (object)[
            'search'  => $app->getUserStateFromRequest('osdownloads.email.request.search', 'search', ''),
            'cate_id' => $app->getUserStateFromRequest('osdownloads.email.request.cate_id', 'cate_id')
        ];

        $limit = $app->getUserStateFromRequest(
            'global.list.limit',
            'limit',
            $app->get('list_limit'),
            'int'
        );

        $limitstart = $app->getUserStateFromRequest('osdownloads.request.limitstart', 'limitstart', 0, 'int');

        $filter_order = $app->getUserStateFromRequest(
            'osdownloads.email.filter_order',
            'filter_order',
            'email.id',
            'cmd'
        );

        $filter_order_Dir = $app->getUserStateFromRequest(
            'osdownloads.email.filter_order_Dir',
            'filter_order_Dir',
            'DESC',
            'word'
        );

        $filter_confirmed = $app->getUserStateFromRequest(
            'osdownloads.email.filter_confirmed',
            'filter_confirmed',
            '-1',
            'int'
        );

        $db = Factory::getDbo();

        $query = $db->getQuery(true)
            ->select('email.*, doc.name AS doc_name, cat.title AS cate_name')
            ->from('#__osdownloads_emails email')
            ->leftJoin('#__osdownloads_documents doc ON (email.document_id = doc.id)')
            ->leftJoin('#__categories cat ON (cat.id = doc.cate_id)');

        $this->buildQuery($query);

        if ($this->flt->search) {
            $query->where(
                sprintf(
                    'email.email LIKE %s OR doc.name LIKE %s',
                    $db->quote('%' . $this->flt->search . '%'),
                    $db->quote('%' . $this->flt->search . '%')
                )
            );
        }
        if ($this->flt->cate_id) {
            $query->where('cat.id = ' . (int)$this->flt->cate_id);
        }

        if ($filter_confirmed >= 0) {
            $query->where('confirmed = ' . $db->quote($filter_confirmed));
        }

        $query->order($filter_order . ' ' . $filter_order_Dir);

        $db->setQuery($query)->execute();

        $total = $db->getNumRows();

        $this->pagination = new Pagination($total, $limitstart, $limit);
        $db->setQuery($query, $this->pagination->limitstart, $this->pagination->limit);

        $this->items = $db->loadObjectList();

        $this->lists = [
            'order_Dir'        => $filter_order_Dir,
            'order'            => $filter_order,
            'filter_confirmed' => $filter_confirmed
        ];

        $this->isPro = $this->extension->isPro();

        $this->addToolbar();
        $this->sidebar = Sidebar::render();

        parent::display($tpl);
    }

    protected function addToolbar()
    {
        JToolbarHelper::title(Text::_('COM_OSDOWNLOADS') . ': ' . Text::_('COM_OSDOWNLOADS_EMAILS'), 'address');

        JToolbarHelper::deleteList('Are you sure?', 'emails.delete');
        JToolbarHelper::divider();
        JToolbarHelper::preferences('com_osdownloads', '450');
    }

    protected function buildQuery($query)
    {

    }

    /**
     * @param string  $name
     * @param string  $extension
     * @param ?string $selected
     * @param ?string $javascript
     * @param ?int    $size
     * @param ?int    $category
     *
     * @return string
     */
    protected function categorySelect(
        string $name,
        string $extension,
        ?string $selected = null,
        ?string $javascript = null,
        ?int $size = 1,
        ?int $category = 1
    ): string {
        // Deprecation warning.
        Log::add('JList::category is deprecated.', Log::WARNING, 'deprecated');

        $categories = HTMLHelper::_('category.options', $extension);
        if ($category) {
            array_unshift($categories, HTMLHelper::_('select.option', '0', Text::_('JOPTION_SELECT_CATEGORY')));
        }

        $attributes = [
            'class' => 'chosen',
            'size'  => $size
        ];
        if ($javascript) {
            $attributes['onclick'] = $javascript;
        }

        return HTMLHelper::_(
            'select.genericlist',
            $categories,
            $name,
            $attributes,
            'value',
            'text',
            $selected
        );
    }
}
