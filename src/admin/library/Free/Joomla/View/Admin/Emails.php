<?php
/**
 * @package   OSDownloads
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2015 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace Alledia\OSDownloads\Free\Joomla\View\Admin;

use Alledia\OSDownloads\Free\Joomla\View\Legacy as LegacyView;
use Alledia\Framework\Factory;
use stdClass;
use JPagination;
use JToolBarHelper;
use JText;

defined('_JEXEC') or die();


class Emails extends LegacyView
{
    public function display($tpl = null)
    {
        global $option;
        $app = Factory::getApplication();

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

        $db = Factory::getDBO();
        $query = $db->getQuery(true);

        $query->select("email.*, doc.name AS doc_name, cat.title AS cate_name");
        $query->from("#__osdownloads_emails email");
        $query->join("LEFT", "#__osdownloads_documents doc ON (email.document_id = doc.id)");
        $query->join("LEFT", "#__categories cat ON (cat.id = doc.cate_id)");

        if ($this->flt->search) {
            $query->where("email.email LIKE '%{$this->flt->search}%' OR doc.name LIKE '%{$this->flt->search}%'");
        }
        if ($this->flt->cate_id) {
            $query->where("cat.id = {$this->flt->cate_id}");
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
        $extension = Factory::getExtension('OSDownloads', 'component');
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
