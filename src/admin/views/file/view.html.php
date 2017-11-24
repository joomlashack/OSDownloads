<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2017 Open Source Training, LLC. All rights reserved
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

        /*===============================================
        =            Trigger content plugins            =
        ===============================================*/
        // In the Pro version this will allow com_files to save the custom fields values.

        JPluginHelper::importPlugin('content');
        $dispatcher = JEventDispatcher::getInstance();

        // Trigger the form preparation event.
        $dispatcher->trigger('onContentPrepareForm', array($this->form, $item));

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

        $this->assignRef("item", $item);
        $this->assignRef("extension", $extension);

        $this->addToolbar();

        parent::display($tpl);
    }

    protected function addToolbar()
    {
        JToolbarHelper::title(JText::_('COM_OSDOWNLOADS') . ': ' . JText::_('COM_OSDOWNLOADS_FILE'));
        JToolbarHelper::save('file.save', 'JTOOLBAR_SAVE');
        JToolbarHelper::apply('file.apply', 'JTOOLBAR_APPLY');
        JToolbarHelper::cancel('cancel', 'JTOOLBAR_CANCEL');
    }
}
