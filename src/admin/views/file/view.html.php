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

use Alledia\Framework\Joomla\View\Admin\AbstractForm;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die();
// phpcs:enable PSR1.Files.SideEffects
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace

class OSDownloadsViewFile extends AbstractForm
{
    /**
     * @inheritDoc
     */
    public function display($tpl = null)
    {
        $this->item = $this->model->getItem();

        $this->addToolbar();

        parent::display($tpl);
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function addToolbar()
    {
        $this->app->input->set('hidemainmenu', true);

        $isNew = ($this->item->id == 0);
        $canDo = ContentHelper::getActions('com_osdownloads');

        $subTitle = 'COM_OSDOWNLOADS_FILE_' . ($isNew ? 'NEW' : 'EDIT');
        ToolbarHelper::title(Text::_('COM_OSDOWNLOADS') . ': ' . Text::_($subTitle), 'file-2 osdownloads-files');

        if (
            (!$isNew && $canDo->get('core.edit'))
            || ($isNew && $canDo->get('core.create'))
        ) {
            ToolbarHelper::apply('file.apply');
            ToolbarHelper::save('file.save');
        }

        if ($canDo->get('core.create')) {
            ToolbarHelper::save2new('file.save2new');
        }

        // If an existing item, can save to a copy.
        if (!$isNew && $canDo->get('core.create')) {
            ToolbarHelper::save2copy('file.save2copy');
        }

        ToolbarHelper::cancel('file.cancel');
    }
}
