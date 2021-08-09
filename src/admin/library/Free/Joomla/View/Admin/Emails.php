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
use Joomla\CMS\HTML\Helpers\Sidebar;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

defined('_JEXEC') or die();

class Emails extends AbstractList
{
    /**
     * @var object
     */
    protected $item = null;

    /**
     * @inheritDoc
     */
    public function display($tpl = null)
    {
        $this->items         = $this->model->getItems();
        $this->filterForm    = $this->model->getFilterForm();
        $this->activeFilters = $this->model->getActiveFilters();
        $this->pagination    = $this->model->getPagination();

        $this->addToolbar();

        $this->sidebar = Sidebar::render();

        parent::display($tpl);
    }

    /**
     * @return void
     */
    protected function addToolbar()
    {
        ToolbarHelper::title(Text::_('COM_OSDOWNLOADS') . ': ' . Text::_('COM_OSDOWNLOADS_EMAILS'), 'address');

        ToolbarHelper::deleteList(Text::_('COM_OSDOWNLOADS_CONFIRM'), 'emails.delete');

        ToolbarHelper::preferences('com_osdownloads', '450');
    }
}
