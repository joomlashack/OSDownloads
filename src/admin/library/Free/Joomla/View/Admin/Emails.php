<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2018 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace Alledia\OSDownloads\Free\Joomla\View\Admin;

use Alledia\Framework\Joomla\Extension\Licensed;
use Alledia\OSDownloads\Free\Joomla\View\Legacy as LegacyView;
use Alledia\Framework\Factory;
use JHtmlSidebar;
use JPagination;
use JToolBarHelper;
use JText;

defined('_JEXEC') or die();


class Emails extends LegacyView
{
    /**
     * @var string
     */
    protected $sidebar = null;

    /**
     * @var object
     */
    protected $flt = null;

    /**
     * @var array
     */
    protected $lists = null;

    /**
     * @var object[]
     */
    protected $items = null;

    /**
     * @var JPagination
     */
    protected $pagination = null;

    /**
     * @var bool
     */
    protected $isPro = false;
    /**
     * @var Licensed
     */
    protected $extension = null;

    public function display($tpl = null)
    {
        $app = Factory::getApplication();

        $this->flt = (object)array(
            'search'  => $app->getUserStateFromRequest('osdownloads.email.request.search', 'search', ""),
            'cate_id' => $app->getUserStateFromRequest('osdownloads.email.request.cate_id', 'cate_id')
        );

        $limit = $app->getUserStateFromRequest(
            'global.list.limit',
            'limit',
            $app->get('list_limit'),
            'int'
        );

        $limitstart = $app->getUserStateFromRequest('osdownloads.request.limitstart', 'limitstart', 0, 'int');

        $filter_order = $app->getUserStateFromRequest(
            "osdownloads.email.filter_order",
            'filter_order',
            'email.id',
            'cmd'
        );

        $filter_order_Dir = $app->getUserStateFromRequest(
            "osdownloads.email.filter_order_Dir",
            'filter_order_Dir',
            'DESC',
            'word'
        );

        $filter_confirmed = $app->getUserStateFromRequest(
            "osdownloads.email.filter_confirmed",
            'filter_confirmed',
            '-1',
            'int'
        );

        $db = Factory::getDBO();

        $query = $db->getQuery(true)
            ->select("email.*, doc.name AS doc_name, cat.title AS cate_name")
            ->from("#__osdownloads_emails email")
            ->leftJoin("#__osdownloads_documents doc ON (email.document_id = doc.id)")
            ->leftJoin("#__categories cat ON (cat.id = doc.cate_id)");

        $this->buildQuery($query);

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

        $db->setQuery($query)->execute();
        $total = $db->getNumRows();

        jimport('joomla.html.pagination');
        $this->pagination = new JPagination($total, $limitstart, $limit);
        $db->setQuery($query, $this->pagination->limitstart, $this->pagination->limit);
        $this->items = (array)$db->loadObjectList();

        $this->lists = array(
            'order_Dir'        => $filter_order_Dir,
            'order'            => $filter_order,
            'filter_confirmed' => $filter_confirmed
        );

        // Load the extension
        $this->extension = Factory::getExtension('OSDownloads', 'component');
        $this->extension->loadLibrary();

        $this->isPro = $this->extension->isPro();

        $this->addToolbar();
        $this->sidebar = JHtmlSidebar::render();

        parent::display($tpl);
    }

    protected function addToolbar()
    {
        JToolBarHelper::title(JText::_('COM_OSDOWNLOADS') . ': ' . JText::_('COM_OSDOWNLOADS_EMAILS'), 'address');

        JToolBarHelper::deleteList('Are you sure?', 'emails.delete');
        JToolBarHelper::divider();
        JToolBarHelper::preferences('com_osdownloads', '450');
    }

    protected function buildQuery(&$query)
    {

    }
}
