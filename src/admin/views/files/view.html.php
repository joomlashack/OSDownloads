<?php
/**
 * @package   OSDownloads
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2014 Alledia.com, All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.view');
jimport('legacy.model.legacy');

require_once __DIR__ . '/../../models/items.php';

class OSDownloadsViewFiles extends JViewLegacy
{
    public function __construct()
    {
        $model = JModelLegacy::getInstance('OSDownloadsModelItems');
        $this->setModel($model, true);

        $this->addTemplatePath(__DIR__ . '/tmpl');
    }

    public function display($tpl = null)
    {
        $app   = JFactory::getApplication();
        $model = $this->getModel();
        $db    = JFactory::getDBO();

        $pagination = $model->getPagination();

        $query = $model->getItemsQuery();

        $db->setQuery($query, $pagination->limitstart, $pagination->limit);
        $items = $db->loadObjectList();

        $filterOrder    = $app->getUserStateFromRequest("com_osdownloads.document.filter_order", 'filter_order', 'doc.id', '');
        $filterOrderDir = $app->getUserStateFromRequest("com_osdownloads.document.filter_order_Dir", 'filter_order_Dir', 'asc', 'word');

        $lists = array();
        $lists['order_Dir'] = $filterOrderDir;
        $lists['order']     = $filterOrder;

        $filter             = new stdClass;
        $filter->search     = $app->getUserStateFromRequest('com_osdownloads.document.request.search', 'search');
        $filter->categoryId = $app->getUserStateFromRequest('com_osdownloads.document.request.cate_id', 'flt_cate_id');

        // Load the extension
        $extension = Alledia\Framework\Factory::getExtension('OSDownloads', 'component');
        $extension->loadLibrary();

        $this->assignRef('lists', $lists);
        $this->assignRef("items", $items);
        $this->assignRef("filter", $filter);
        $this->assignRef("pagination", $pagination);
        $this->assignRef("extension", $extension);

        $this->addToolbar();

        parent::display($tpl);
    }

    protected function addToolbar()
    {
        JToolBarHelper::title(JText::_('COM_OSDOWNLOADS_FILES'));
        JToolBarHelper::custom('file', 'new.png', 'new_f2.png', 'JTOOLBAR_NEW', false);
        JToolBarHelper::custom('file', 'edit.png', 'edit_f2.png', 'JTOOLBAR_EDIT', true);
        JToolBarHelper::custom('file.delete', 'delete.png', 'delete_f2.png', 'JTOOLBAR_DELETE', true);
        JToolBarHelper::divider();
        JToolBarHelper::custom('file.publish', 'publish.png', 'publish_f2.png', 'JTOOLBAR_PUBLISH', true);
        JToolBarHelper::custom('file.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
        JToolBarHelper::divider();
        JToolBarHelper::preferences('com_osdownloads', '450');
    }
}
