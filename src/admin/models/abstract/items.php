<?php
/**
 * @package   OSDownloads
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2014 Alledia.com, All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.modeladmin');

abstract class OSDownloadsModelItemsAbstract extends JModelAdmin
{
    protected $state;

    protected $pagination;

    /**
     * Get the documents list query
     *
     * @param  int $documentId
     * @return JDatabaseQuery
     */
    public function getItemsQuery()
    {
        $app = JFactory::getApplication();
        $db  = $this->getDBO();

        $query  = $db->getQuery(true)
            ->select(
                array(
                    'doc.*',
                    'cat.access AS cat_access',
                    'cat.title AS cat_title',
                    'vl.title AS access_title'
                )
            )
            ->from('#__osdownloads_documents AS doc')
            ->leftJoin(
                '#__categories AS cat'
                    . ' ON (doc.cate_id = cat.id AND cat.extension = ' . $db->quote('com_osdownloads') . ')'
            )
            ->leftJoin(
                '#__viewlevels AS vl'
                    . ' ON (doc.access = vl.id)'
            );

        $search = $app->getUserStateFromRequest('com_osdownloads.document.request.search', 'search');
        if ($search) {
            $query->where("doc.name LIKE '%{$search}%'");
        }

        $categoryId = $app->getUserStateFromRequest('com_osdownloads.document.request.cate_id', 'flt_cate_id');
        if (!empty($categoryId)) {
            $query->where("cat.id = " . $categoryId);
        }

        $filterOrder    = $app->getUserStateFromRequest("com_osdownloads.document.filter_order", 'filter_order', 'doc.id', '');
        $filterOrderDir = $app->getUserStateFromRequest("com_osdownloads.document.filter_order_Dir", 'filter_order_Dir', 'asc', 'word');

        if ($filterOrder == "doc.ordering") {
            $query->order('doc.cate_id, ' . $filterOrder . ' ' . $filterOrderDir);
        } else {
            $query->order($filterOrder . ' ' . $filterOrderDir);
        }

        return $query;
    }

    public function getPagination()
    {
        jimport('joomla.html.pagination');

        if (empty($this->pagination)) {

            $app   = JFactory::getApplication();
            $db    = $this->getDBO();
            $query = $this->getItemsQuery();

            $db->setQuery($query);
            $db->query();

            $total = $db->getNumRows();

            $limitStart = $app->getUserStateFromRequest('com_osdownloads.request.limitstart', 'limitstart', 0, 'int');
            $limit      = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');

            $this->pagination = new JPagination($total, $limitStart, $limit);
        }

        return $this->pagination;
    }

    /**
     * Method to get the row form.
     *
     * @param   array    $data      Data for the form.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  mixed    A JForm object on success, false on failure
     *
     * @since   1.6
     */
    public function getForm($data = array(), $loadData = true)
    {
        return false;
    }

    public function publish(&$pks, $value = 1)
    {
        $db = $this->getDBO();
        $query = $db->getQuery(true)
            ->update('#__osdownloads_documents')
            ->set('published = ' . $db->quote($value))
            ->where('id IN (' . implode(',', $pks) . ')');
        $db->setQuery($query);
        $db->execute();
    }
}
