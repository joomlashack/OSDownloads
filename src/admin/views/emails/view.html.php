<?php
/**
 * @package   OSDownloads
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2015 Open Source Training, LLC. All rights reserved
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

use Alledia\OSDownloads\Free\Joomla\View\Legacy as LegacyView;


class OSDownloadsViewEmails extends LegacyView
{
    public function display($tpl = null)
    {
        global $option;
        $app = JFactory::getApplication();

        if (!isset($this->flt)) {
            $this->flt = new stdClass;
        }

        $limit              = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');
        $limitstart         = $app->getUserStateFromRequest('osdownloads.request.limitstart', 'limitstart', 0, 'int');
        $this->flt->search  = $app->getUserStateFromRequest('osdownloads.email.request.search', 'search', "");
        $this->flt->cate_id = $app->getUserStateFromRequest('osdownloads.email.request.cate_id', 'cate_id');
        $filter_order       = $app->getUserStateFromRequest("osdownloads.email.filter_order", 'filter_order', 'email.id', 'cmd');
        $filter_order_Dir   = $app->getUserStateFromRequest("osdownloads.email.filter_order_Dir", 'filter_order_Dir', '', 'word');
        $filter_confirmed   = $app->getUserStateFromRequest("osdownloads.email.filter_confirmed", 'filter_confirmed', '-1', 'int');

        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $query->select("email.*, document.name AS doc_name, cate.title AS cate_name");
        $query->from("#__osdownloads_emails email");
        $query->join("LEFT", "#__osdownloads_documents document ON (email.document_id = document.id)");
        $query->join("LEFT", "#__categories cate ON (cate.id = document.cate_id)");

        if ($this->flt->search) {
            $query->where("email.email LIKE '%{$this->flt->search}%' OR document.name LIKE '%{$this->flt->search}%'");
        }
        if ($this->flt->cate_id) {
            $query->where("cate.id = {$this->flt->cate_id}");
        }

        if ($filter_confirmed >= 0) {
            $query->where('confirmed = ' . $db->quote($filter_confirmed));
        }

        $query->order(" $filter_order  $filter_order_Dir");

        $db->setQuery($query);
        $db->query();
        $total = $db->getNumRows();

        jimport('joomla.html.pagination');
        $pagination = new JPagination($total, $limitstart, $limit);
        $db->setQuery($query, $pagination->limitstart, $pagination->limit);
        $items = (array) $db->loadObjectList();

        $lists = array();
        $lists['order_Dir']        = $filter_order_Dir;
        $lists['order']            = $filter_order;
        $lists['filter_confirmed'] = $filter_confirmed;

        // Load the extension
        $extension = Alledia\Framework\Factory::getExtension('OSDownloads', 'component');
        $extension->loadLibrary();

        $this->assignRef('lists', $lists);
        $this->assignRef("items", $items);
        $this->assignRef("pagination", $pagination);
        $this->assignRef("extension", $extension);
        $this->assign("isPro", $extension->isPro());

        $this->addToolbar();
        parent::display($tpl);
    }

    protected function addToolbar()
    {
        JToolBarHelper::title(JText::_('COM_OSDOWNLOADS') . ': ' . JText::_('COM_OSDOWNLOADS_EMAILS'));
        JToolBarHelper::deleteList('Are you sure?', 'emails.delete');
    }
}
