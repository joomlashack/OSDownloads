<?php
/**
 * @version 1.0.0
 * @author Open Source Training (www.ostraining.com)
 * @copyright (C) 2011- Open Source Training
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/
// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

class OSDownloadsControllerFile extends JController
{

	function __construct( $default = array())
	{
		parent::__construct( $default );

		$this->registerTask( 'apply', 			'save');
		$this->registerTask( 'unpublish', 		'publish');
		$this->registerTask( 'orderup', 		'reorder' );
		$this->registerTask( 'orderdown', 		'reorder' ); 
	}
	
 	function display()
	{
		parent::display();
	}
	
	function save()
	{
		JRequest::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));
		JTable::addIncludePath(JPATH_COMPONENT.DS.'tables');
		jimport('joomla.filesystem.file'); 
		jimport('joomla.filesystem.folder'); 
		
		$row 	= & JTable::getInstance('document', 'Table');
		$post 	= JRequest::get('post');
	
		$row->bind($post);

		$text 				= JRequest::getVar( 'description_1', '', 'post', 'string', JREQUEST_ALLOWRAW ); 
		$text				= str_replace( '<br>', '<br />', $text ); 
		$pattern 			= '#<hr\s+id=("|\')system-readmore("|\')\s*\/*>#i';
		$tagPos				= preg_match($pattern, $text); 
		if ($tagPos == 0) 
		{
			$row->brief	= $text;
			$row->description_1 = "";
		}
		else
		{
			list($row->brief, $row->description_1) = preg_split($pattern, $text, 2);
		}

		$text 				= JRequest::getVar( 'description_2', '', 'post', 'string', JREQUEST_ALLOWRAW ); 
		$text				= str_replace( '<br>', '<br />', $text ); 
		$row->description_2 = $text;
		$text 				= JRequest::getVar( 'description_3', '', 'post', 'string', JREQUEST_ALLOWRAW ); 
		$text				= str_replace( '<br>', '<br />', $text ); 
		$row->description_3 = $text;
		$row->show_email 	= isset($row->show_email);
		$row->require_email = isset($row->require_email);
		$row->require_agree = isset($row->require_agree);
		
		$file 				= JRequest::getVar("file", '', "files");
		$file["name"] 		= JFile::makeSafe($file["name"]);
		
		if (isset($file["name"]) && $file["name"])
		{
			if ($post["old_file"] && JFile::exists(JPath::clean(JPATH_SITE.DS."media".DS."OSDownloads".DS.$post["old_file"])))
				unlink(JPath::clean(JPATH_SITE.DS."media".DS."OSDownloads".DS.$post["old_file"]));
			
			if (!JFolder::exists(JPath::clean(JPATH_SITE.DS."media".DS."OSDownloads")))
				JFolder::create(JPath::clean(JPATH_SITE.DS."media".DS."OSDownloads"));
			
			$timestamp = mktime();
			$filepath = JPath::clean(JPATH_SITE.DS."media".DS."OSDownloads".DS.$timestamp."_".$file["name"]);
			$row->file_path = $timestamp."_".$file["name"];
			JFile::upload($file["tmp_name"], $filepath);
		}
		
		$row->store();
		switch ($this->getTask()) 
		{
			case "apply":
				$this->setRedirect("index.php?option=com_osdownloads&view=file&cid=" . $row->id, JText::_("Document is saved")); 
				break;
			default:
				$this->setRedirect("index.php?option=com_osdownloads&view=files", JText::_("Document is saved")); 
		}
	}	
	
	function publish()
	{

		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );

		$db 	=& JFactory::getDBO();

		$cid		= JRequest::getVar( 'cid', array(), '', 'array' );
		$publish	= ( $this->getTask() == 'publish' ? 1 : 0 );

		JArrayHelper::toInteger($cid);

		$cids = implode( ',', $cid );

		$query = 'UPDATE #__osdownloads_documents'
		. ' SET published = ' . (int) $publish
		. ' WHERE id IN ( '. $cids .' )';
		$db->setQuery( $query );
		if (!$db->query())
		{
			JError::raiseError(500, $db->getErrorMsg() );
		}
		$this->setRedirect( 'index.php?option=com_osdownloads&view=files' );
	} 	
	
	function delete()
	{
		JRequest::checkToken() or jexit( 'Invalid Token' );
		jimport('joomla.filesystem.file'); 

		$db 	=& JFactory::getDBO();
		$cid	= JRequest::getVar( 'cid', array(), '', 'array' );
		
		JArrayHelper::toInteger($cid);
		
		$cids = implode( ',', $cid );
		
		$query = 'SELECT * FROM #__osdownloads_documents WHERE id IN ( '. $cids .' )';
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		foreach ($rows as $item)
		{
			$filepath = JPath::clean(JPATH_SITE.DS."media".DS."OSDownloads".DS.$item->file_path);
			if (JFile::exists($filepath))
				JFile::delete($filepath);
		}
		
		$query = 'DELETE FROM #__osdownloads_documents WHERE id IN ( '. $cids .' )';
		$db->setQuery( $query );
		if (!$db->query())
		{
			JError::raiseError(500, $db->getErrorMsg() );
		}
		else
		{
			$query = 'DELETE FROM #__osdownloads_emails WHERE document_id IN ( '. $cids .' )';
			$db->setQuery( $query );
			$db->query();
		}
		
		$this->setRedirect( 'index.php?option=com_osdownloads&view=files', JText::_("Files are deleted"));
		
	}
	
	function saveorder()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );
		JTable::addIncludePath(JPATH_COMPONENT.DS.'tables');

		// Initialize some variables
		$db		=& JFactory::getDBO();

		$cid 	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		if (empty( $cid )) {
			return JError::raiseWarning( 500, 'No items selected' );
		}

		$total		= count( $cid );
		$row 		= & JTable::getInstance('document', 'Table');
		$groupings = array();

		$order 		= JRequest::getVar( 'order', array(0), 'post', 'array' );
		JArrayHelper::toInteger($order);

		// update ordering values
		for ($i = 0; $i < $total; $i++)
		{
			$row->load( (int) $cid[$i] );
			// track postions
			$groupings[] = $row->cate_id;

			if ($row->ordering != $order[$i])
			{
				$row->ordering = $order[$i];
				if (!$row->store()) {
					//return JError::raiseWarning( 500, $db->getErrorMsg() );
				}
			}
		}

		// execute updateOrder for each parent group
		$groupings = array_unique( $groupings );
		foreach ($groupings as $group){
			$row->reorder('cate_id = '.$db->Quote($group));
		}

		$this->setMessage (JText::_( 'New ordering saved' ));
		$this->setRedirect( 'index.php?option=com_osdownloads&view=files' );
	} 	

	function reorder()
	{
		global $mainframe;
		JTable::addIncludePath(JPATH_COMPONENT.DS.'tables');

		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );

		// Initialize some variables
		$db		=& JFactory::getDBO();

		$cid 	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		$task	= $this->getTask();
		$inc	= ($task == 'orderup' ? -1 : 1);

		if (empty( $cid )) {
			return JError::raiseWarning( 500, 'No items selected' );
		}

		$row 		= & JTable::getInstance('document', 'Table');
		$row->load( (int) $cid[0] );

		$row->move( $inc, 'cate_id = '.$db->Quote( $row->cate_id ) );
		$this->setMessage (JText::_( 'New ordering saved' ));
		$this->setRedirect( 'index.php?option=com_osdownloads&view=files' );
	} 
} 