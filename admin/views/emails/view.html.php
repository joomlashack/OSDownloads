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

class OSDownloadsViewEmails extends JView
{

	function display($tpl = null)
	{
		global $option;
		$mainframe = & JFactory::getApplication();
		
		$limit							= $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
		$limitstart						= $mainframe->getUserStateFromRequest( 'osdownloads.request.limitstart', 'limitstart', 0, 'int' );
		$this->flt->search 				= $mainframe->getUserStateFromRequest( 'osdownloads.email.request.search', 'search', "" );
		$this->flt->cate_id				= $mainframe->getUserStateFromRequest( 'osdownloads.email.request.cate_id', 'cate_id');
		$filter_order					= $mainframe->getUserStateFromRequest( "osdownloads.email.filter_order",	'filter_order',		'email.id',	'cmd' );
		$filter_order_Dir				= $mainframe->getUserStateFromRequest( "osdownloads.email.filter_order_Dir",	'filter_order_Dir',	'',		'word' ); 
		$db 	= & JFactory::getDBO();
		$query 	= $db->getQuery(true);
		
		$query->select("email.*, document.name AS doc_name, cate.title AS cate_name");
		$query->from("#__osdownloads_emails email");
		$query->join("LEFT", "#__osdownloads_documents document ON (email.document_id = document.id)");
		$query->join("LEFT", "#__categories cate ON (cate.id = document.cate_id)");
		
		if ($this->flt->search)
			$query->where("email.email LIKE '%{$this->flt->search}%' OR document.name LIKE '%{$this->flt->search}%'");
		if ($this->flt->cate_id)
			$query->where("cate.id = {$this->flt->cate_id}");
		
		$query->order(" $filter_order  $filter_order_Dir");
		
		$db->setQuery($query);
		$db->query();
		$total = $db->getNumRows();
		
		jimport('joomla.html.pagination');
		$pagination = new JPagination( $total, $limitstart, $limit );
		$db->setQuery($query, $pagination->limitstart,$pagination->limit );
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
		JToolBarHelper::title(JText::_('OSDOWNLOADS_EMAILS'));
		JToolBarHelper::deleteList('Are you sure?', 'emails.delete');
	}	
} 