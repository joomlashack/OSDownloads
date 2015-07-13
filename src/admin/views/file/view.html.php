<?php
/**
 * @package   OSDownloads
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2015 Open Source Training, LLC. All rights reserved
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
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
        $this->form = $this->get('Form');
        $this->model = $this->getModel();

        JTable::addIncludePath(JPATH_COMPONENT . '/tables');

        $app = JFactory::getApplication();
        $cid = $app->input->get('cid', array(), 'array');
        $cid = (int)array_shift($cid);



        $item = JTable::getInstance("document", "OSDownloadsTable");
        $item->load($cid);

        if ($item->description_1) {
            $item->description_1 = $item->brief . "<hr id=\"system-readmore\" />" . $item->description_1;
        } else {
            $item->description_1 = $item->brief;
        }

        $this->form->bind($item);

        // Load the extension
        $extension = Factory::getExtension('OSDownloads', 'component');
        $extension->loadLibrary();

        // Add the agreementLink property
        if (!empty($item)) {
            $item->agreementLink = '';
            if ((bool)$item->require_agree) {
                $item->agreementLink = JRoute::_('index.php?option=com_content&view=article&id=' . (int)  $item->agreement_article_id);
            }
        }

        $this->assignRef("item", $item);
        $this->assignRef("extension", $extension);

        $this->addToolbar();

        parent::display($tpl);
    }

    protected function addToolbar()
    {
        JToolBarHelper::title(JText::_('COM_OSDOWNLOADS') . ': ' . JText::_('COM_OSDOWNLOADS_FILE'));
        JToolBarHelper::save('file.save', 'JTOOLBAR_SAVE');
        JToolBarHelper::apply('file.apply', 'JTOOLBAR_APPLY');
        JToolBarHelper::cancel('cancel', 'JTOOLBAR_CANCEL');
    }
}
