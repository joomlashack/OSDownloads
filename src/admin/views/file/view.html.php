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

defined('_JEXEC') or die();

use Alledia\Framework\Factory;
use Alledia\Installer\Extension\Licensed;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Object\CMSObject;

class OSDownloadsViewFile extends JViewLegacy
{
    /**
     * @var Form
     */
    protected $form = null;

    /**
     * @var OSDownloadsModelFile
     */
    protected $model;

    /**
     * @var Licensed
     */
    protected $extension = null;

    /**
     * @param string $tpl
     *
     * @return void
     * @throws Exception
     */
    public function display($tpl = null)
    {
        $this->model = $this->getModel();
        $this->form  = $this->model->getForm();

        $this->extension = Factory::getExtension('OSDownloads', 'component');
        $this->extension->loadLibrary();

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
        JFactory::getApplication()->input->set('hidemainmenu', true);

        $user  = JFactory::getUser();
        $isNew = ($this->item->id == 0);
        $canDo = $this->canDo;

        JToolbarHelper::title(JText::_('COM_OSDOWNLOADS') . ': ' .
            ($isNew ? JText::_('COM_OSDOWNLOADS_FILE_NEW') : JText::_('COM_OSDOWNLOADS_FILE_EDIT')),
            'file-2 osdownloads-files'
        );

        if ($canDo->get('core.edit') || $canDo->get('core.create')) {
            JToolbarHelper::apply('file.apply', 'JTOOLBAR_APPLY');
            JToolbarHelper::save('file.save', 'JTOOLBAR_SAVE');
        }

        if ($canDo->get('core.create')) {
            JToolbarHelper::save2new('file.save2new');
        }

        // If an existing item, can save to a copy.
        if (!$isNew && $canDo->get('core.create')) {
            JToolbarHelper::save2copy('file.save2copy');
        }

        JToolbarHelper::cancel('cancel', 'JTOOLBAR_CANCEL');
    }
}
