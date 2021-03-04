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

use Alledia\Framework\Extension;
use Alledia\Installer\Extension\Licensed;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Pagination\Pagination;

defined('_JEXEC') or die();

class OSDownloadsViewFiles extends JViewLegacy
{
    /**
     * @var string
     */
    protected $sidebar = null;

    /**
     * @var CMSObject
     */
    protected $state = null;

    /**
     * @var Pagination
     */
    protected $pagination = null;

    /**
     * @var OSDownloadsModelFiles
     */
    protected $model = null;

    /**
     * @var array
     */
    protected $items = array();

    /**
     * @var Extension
     */
    protected $extension = null;

    /**
     * @var JForm
     */
    public $filterForm = null;

    /**
     * @var array
     */
    public $activeFilters = null;

    /**
     * @param string $tpl
     *
     * @return void
     * @throws Exception
     */
    public function display($tpl = null)
    {
        $this->model         = $this->getModel();
        $this->state         = $this->model->getState();
        $this->items         = $this->model->getItems();
        $this->filterForm    = $this->model->getFilterForm();
        $this->activeFilters = $this->model->getActiveFilters();
        $this->pagination    = $this->model->getPagination();

        $this->extension = Alledia\Framework\Factory::getExtension('OSDownloads', 'component');
        $this->extension->loadLibrary();

        $this->addToolbar();
        $this->sidebar = JHtmlSidebar::render();

        parent::display($tpl);
    }

    protected function addToolbar()
    {
        JToolBarHelper::title(
            JText::_('COM_OSDOWNLOADS') . ': ' . JText::_('COM_OSDOWNLOADS_FILES'),
            'file-2 osdownloads-files'
        );

        $user  = JFactory::getUser();
        $canDo = JHelperContent::getActions('com_osdownloads', 'category', $this->state->get('filter.category_id'));

        if ($canDo->get('core.create') || $user->getAuthorisedCategories('com_osdownloads', 'core.create')) {
            JToolBarHelper::addNew('file');
        }

        if ($canDo->get('core.edit') || $canDo->get('edit.own')) {
            JToolBarHelper::editList('file');
        }

        if ($canDo->get('core.delete')) {
            JToolBarHelper::deleteList('', 'files.delete');
        }

        if ($canDo->get('core.edit.state')) {
            JToolBarHelper::publishList('files.publish');
            JToolBarHelper::unpublishList('files.unpublish');
        }

        if ($canDo->get('core.admin') || $canDo->get('core.options')) {
            JToolBarHelper::preferences('com_osdownloads', '450');
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
        return array(
            'doc.ordering'   => JText::_('JGRID_HEADING_ORDERING'),
            'doc.published'  => JText::_('COM_OSDOWNLOADS_PUBLISHED'),
            'doc.name'       => JText::_('COM_OSDOWNLOADS_NAME'),
            'doc.access'     => JText::_('COM_OSDOWNLOADS_ACCESS'),
            'doc.downloaded' => JText::_('COM_OSDOWNLOADS_DOWNLOADED'),
            'doc.id'         => JText::_('COM_OSDOWNLOADS_ID')
        );
    }
}
