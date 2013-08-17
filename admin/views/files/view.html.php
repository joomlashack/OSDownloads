<?php
/**
 * @version 1.0.0
 * @author Open Source Training (www.ostraining.com)
 * @copyright (C) 2011- Open Source Training
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.view');

class OSDownloadsViewFiles extends JView
{

	function display($tpl = null)
	{
		global $option;
		$mainframe = & JFactory::getApplication();
		
		$limit							= $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
		$limitstart						= $mainframe->getUserStateFromRequest( 'osdownloads.request.limitstart', 'limitstart', 0, 'int' );
		$this->flt->search 				= $mainframe->getUserStateFromRequest( 'osdownloads.document.request.search', 'search' );
		$this->flt->cate_id 			= $mainframe->getUserStateFromRequest( 'osdownloads.document.request.cate_id', 'flt_cate_id' );
		$filter_order					= $mainframe->getUserStateFromRequest( "osdownloads.document.filter_order",		'filter_order',		'documents.id',	'' );
		$filter_order_Dir				= $mainframe->getUserStateFromRequest( "osdownloads.document.filter_order_Dir",	'filter_order_Dir',	'asc',		'word' ); 
				
		$db 	= & JFactory::getDBO();
		
		$query = "SELECT documents.*, cate.title AS cate_name
						FROM #__osdownloads_documents documents
						LEFT OUTER JOIN #__categories cate ON (documents.cate_id = cate.id AND cate.extension = 'com_osdownloads')";
		
		$where = array();

		if ($this->flt->search)
			$where[] = "documents.name LIKE '%{$this->flt->search}%'";
			
		if ($this->flt->cate_id)
			$where[] = "cate.id = " .$this->flt->cate_id;
				
		$where = ( count( $where ) ? ' WHERE ' . implode( ' AND ', $where ) : '' ); 
		$query .= $where;
		if ($filter_order == "documents.ordering")
			$orderby 	= ' ORDER BY documents.cate_id, '. $filter_order .' '. $filter_order_Dir; 
		else
			$orderby 	= ' ORDER BY '. $filter_order .' '. $filter_order_Dir; 
		
		$db->setQuery($query);
		$db->query();
		$total = $db->getNumRows();
		
		jimport('joomla.html.pagination');
		$pagination = new JPagination( $total, $limitstart, $limit );
		$db->setQuery($query. $orderby, $pagination->limitstart,$pagination->limit );
		$items = $db->loadObjectList();

		$lists = array();
		$lists['order_Dir']	= $filter_order_Dir;
		$lists['order']		= $filter_order; 

		$this->assignRef('lists',		$lists); 
		$this->assignRef("items", 		$items);
		$this->assignRef("pagination", 	$pagination);
		
		$this->addToolbar();
		parent::display($tpl);
	}
	
	protected function addToolbar()
	{
		JToolBarHelper::title(JText::_('OSDOWNLOADS_FILES'));
		JToolBarHelper::custom('file', 'new.png', 'new_f2.png','JTOOLBAR_NEW', false);
		JToolBarHelper::custom('file', 'edit.png', 'edit_f2.png','JTOOLBAR_EDIT', true);
		JToolBarHelper::custom('file.delete','delete.png','delete_f2.png','JTOOLBAR_DELETE', true);
		JToolBarHelper::divider();
		JToolBarHelper::custom('file.publish', 'publish.png', 'publish_f2.png','JTOOLBAR_PUBLISH', true);
		JToolBarHelper::custom('file.unpublish', 'unpublish.png', 'unpublish_f2.png','JTOOLBAR_UNPUBLISH', true);
		JToolBarHelper::divider();
		JToolBarHelper::preferences('com_osdownloads', '450');
	}	
} 