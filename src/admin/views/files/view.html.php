<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2018 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die();

require_once __DIR__ . '/../view.html.php';
require_once __DIR__ . '/../../models/items.php';

class OSDownloadsViewFiles extends OSDownloadsViewAbstract
{
    /**
     * @var string
     */
    protected $sidebar = null;

    public function __construct($config = array())
    {
        parent::__construct($config);

        $model = JModelLegacy::getInstance('OSDownloadsModelItems');
        $this->setModel($model, true);

        $this->addTemplatePath(__DIR__ . '/tmpl');
    }

    /**
     * @param string $tpl
     *
     * @return void
     * @throws Exception
     */
    public function display($tpl = null)
    {
        $app   = JFactory::getApplication();
        $model = $this->getModel();
        $db    = JFactory::getDBO();

        $pagination = $model->getPagination();

        $query = $model->getItemsQuery();

        $db->setQuery($query, $pagination->limitstart, $pagination->limit);
        $items = $db->loadObjectList();

        $filterOrder    = $app->getUserStateFromRequest(
            'com_osdownloads.document.filter_order',
            'filter_order',
            'doc.id',
            ''
        );
        $filterOrderDir = $app->getUserStateFromRequest(
            'com_osdownloads.document.filter_order_Dir',
            'filter_order_Dir',
            'asc',
            'word'
        );

        $lists              = array();
        $lists['order_Dir'] = $filterOrderDir;
        $lists['order']     = $filterOrder;

        $filter             = new stdClass;
        $filter->search     = $app->getUserStateFromRequest('com_osdownloads.document.request.search', 'search');
        $filter->categoryId = $app->getUserStateFromRequest('com_osdownloads.document.request.cate_id', 'flt_cate_id');
        $filter->limit      = $app->getUserStateFromRequest('com_osdownloads.document.request.limit', 'limit');

        if (empty($filter->limit)) {
            $filter->limit = 25;
        }

        // Load the extension
        $extension = Alledia\Framework\Factory::getExtension('OSDownloads', 'component');
        $extension->loadLibrary();

        // Add the agreementLink property
        if (!empty($items)) {
            foreach ($items as &$item) {
                $item->agreementLink = '';
                if ((bool)$item->require_agree) {
                    JLoader::register('ContentHelperRoute', JPATH_SITE . '/components/com_content/helpers/route.php');
                    $item->agreementLink = JRoute::_(ContentHelperRoute::getArticleRoute($item->agreement_article_id));
                }
            }
        }

        $this->lists      = $lists;
        $this->items      = $items;
        $this->filter     = $filter;
        $this->pagination = $pagination;
        $this->extension  = $extension;

        $this->addToolbar();
        $this->sidebar = JHtmlSidebar::render();

        parent::display($tpl);
    }

    protected function addToolbar()
    {
        JToolBarHelper::title(
            JText::_('COM_OSDOWNLOADS') . ': ' . JText::_('COM_OSDOWNLOADS_FILES'),
            'file-2 osdownloads-files'
        );

        JToolBarHelper::custom('file', 'new.png', 'new_f2.png', 'JTOOLBAR_NEW', false);
        JToolBarHelper::custom('file', 'edit.png', 'edit_f2.png', 'JTOOLBAR_EDIT', true);
        JToolBarHelper::custom('files.delete', 'delete.png', 'delete_f2.png', 'JTOOLBAR_DELETE', true);
        JToolBarHelper::divider();
        JToolBarHelper::custom('files.publish', 'publish.png', 'publish_f2.png', 'JTOOLBAR_PUBLISH', true);
        JToolBarHelper::custom('files.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
        JToolBarHelper::divider();
        JToolBarHelper::preferences('com_osdownloads', '450');
    }

    /**
     * Returns an array of fields the table can be sorted by
     *
     * @return  array  Array containing the field name to sort by as the key and display text as value
     *
     * @since   3.0
     */
    protected function getSortFields()
    {
        return array(
            'doc.ordering'   => JText::_('JGRID_HEADING_ORDERING'),
            'doc.published'  => JText::_('COM_OSDOWNLOADS_PUBLISHED'),
            'doc.name'       => JText::_('COM_OSDOWNLOADS_NAME'),
            'doc.access'     => JText::_('COM_OSDOWNLOADS_ACCESS'),
            'doc.downloaded' => JText::_('COM_OSDOWNLOADS_DOWNLOADED'),
            'doc.id'         => JText::_('COM_OSDOWNLOADS_ID')
        );
    }
}
