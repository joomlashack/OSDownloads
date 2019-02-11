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

defined('_JEXEC') or die;

use Alledia\Framework\Factory;

require_once __DIR__ . '/../view.html.php';
require_once __DIR__ . '/../../models/file.php';

class OSDownloadsViewFile extends OSDownloadsViewAbstract
{
    protected $form;

    protected $model;

    public function display($tpl = null)
    {
        $app = JFactory::getApplication();
        $cid = $app->input->get('cid', array(), 'array');
        $cid = (int)array_shift($cid);

        $this->model = $this->getModel();
        $this->model->getState()->set('file.id', $cid);

        $this->form = $this->get('Form');

        $this->canDo = JHelperContent::getActions('com_osdownloads');

        JTable::addIncludePath(JPATH_COMPONENT . '/tables');

        $item = JTable::getInstance("document", "OSDownloadsTable");
        $item->load($cid);

        if ($item->description_1) {
            $item->description_1 = $item->brief . "<hr id=\"system-readmore\" />" . $item->description_1;
        } else {
            $item->description_1 = $item->brief;
        }

        $this->form->bind($item);

        /*===============================================
        =            Trigger content plugins            =
        ===============================================*/
        // In the Pro version this will allow com_files to save the custom fields values.

        JPluginHelper::importPlugin('content');
        $dispatcher = JEventDispatcher::getInstance();

        /*=====  End of Trigger content plugins  ======*/

        // Load the extension
        $extension = Factory::getExtension('OSDownloads', 'component');
        $extension->loadLibrary();

        // Add the agreementLink property
        if (!empty($item)) {
            $item->agreementLink = '';
            if ((bool)$item->require_agree) {
                \JLoader::register('ContentHelperRoute', JPATH_SITE . '/components/com_content/helpers/route.php');
                $item->agreementLink = JRoute::_(\ContentHelperRoute::getArticleRoute($item->agreement_article_id));
            }
        }

        $this->item      = $item;
        $this->extension = $extension;

        $this->addToolbar();

        parent::display($tpl);
    }

    protected function addToolbar()
    {
        JFactory::getApplication()->input->set('hidemainmenu', true);

        $user       = JFactory::getUser();
        $isNew      = ($this->item->id == 0);
        $canDo      = $this->canDo;

        JToolbarHelper::title(JText::_('COM_OSDOWNLOADS') . ': ' .
            ($isNew ? JText::_('COM_OSDOWNLOADS_FILE_NEW') : JText::_('COM_OSDOWNLOADS_FILE_EDIT')),
            'file-2 osdownloads-files'
        );

        if ($canDo->get('core.edit') || $canDo->get('core.create'))
        {
            JToolbarHelper::apply('file.apply', 'JTOOLBAR_APPLY');
            JToolbarHelper::save('file.save', 'JTOOLBAR_SAVE');
        }

        if ($canDo->get('core.create'))
        {
            JToolbarHelper::save2new('file.save2new');
        }

        // If an existing item, can save to a copy.
        if (!$isNew && $canDo->get('core.create'))
        {
            JToolbarHelper::save2copy('file.save2copy');
        }

        JToolbarHelper::cancel('cancel', 'JTOOLBAR_CANCEL');
    }
}
