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

use Alledia\Framework\Joomla\View\Admin\AbstractList;
use Alledia\OSDownloads\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die();

class OSDownloadsViewFiles extends AbstractList
{
    /**
     * @param string $tpl
     *
     * @return void
     * @throws Exception
     */
    public function display($tpl = null)
    {
        $this->items         = $this->model->getItems();
        $this->filterForm    = $this->model->getFilterForm();
        $this->activeFilters = $this->model->getActiveFilters();
        $this->pagination    = $this->model->getPagination();

        $this->addToolbar();

        $this->sidebar = JHtmlSidebar::render();

        parent::display($tpl);
    }

    protected function addToolbar()
    {
        JToolbarHelper::title(
            Text::_('COM_OSDOWNLOADS') . ': ' . Text::_('COM_OSDOWNLOADS_FILES'),
            'file-2 osdownloads-files'
        );

        $user  = Factory::getUser();
        $canDo = ContentHelper::getActions('com_osdownloads', 'category', $this->state->get('filter.category_id'));

        if ($canDo->get('core.create') || $user->getAuthorisedCategories('com_osdownloads', 'core.create')) {
            JToolbarHelper::addNew('file');
        }

        if ($canDo->get('core.edit') || $canDo->get('edit.own')) {
            JToolbarHelper::editList('file');
        }

        if ($canDo->get('core.delete')) {
            JToolbarHelper::deleteList('', 'files.delete');
        }

        if ($canDo->get('core.edit.state')) {
            JToolbarHelper::publishList('files.publish');
            JToolbarHelper::unpublishList('files.unpublish');
        }

        if ($canDo->get('core.admin') || $canDo->get('core.options')) {
            JToolbarHelper::preferences('com_osdownloads', '450');
        }
    }

    /**
     * Returns an array of fields the table can be sorted by
     *
     * @return  array  Array containing the field name to sort by as the key and display text as value
     *
     * @since   3.0
     */
    protected function getSortFields()
    {
        return [
            'doc.ordering'   => Text::_('JGRID_HEADING_ORDERING'),
            'doc.published'  => Text::_('COM_OSDOWNLOADS_PUBLISHED'),
            'doc.name'       => Text::_('COM_OSDOWNLOADS_NAME'),
            'doc.access'     => Text::_('COM_OSDOWNLOADS_ACCESS'),
            'doc.downloaded' => Text::_('COM_OSDOWNLOADS_DOWNLOADED'),
            'doc.id'         => Text::_('COM_OSDOWNLOADS_ID')
        ];
    }
}
