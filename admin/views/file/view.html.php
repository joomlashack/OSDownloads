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

class OSDownloadsViewFile extends JView
{

	function display($tpl = null)
	{
		JTable::addIncludePath(JPATH_COMPONENT.DS.'tables');
		
		$mainframe 	= & JFactory::getApplication();		
		$cid = JRequest::getVar("cid");
		
		if (is_array($cid))
			$cid = $cid[0];
		$item = JTable::getInstance("document", "Table" );
		$item->load($cid);
			
		if ($item->description_1)
			$item->description_1 = $item->brief . "<hr id=\"system-readmore\" />" . $item->description_1;
		else
			$item->description_1 = $item->brief;
		
		$this->assignRef("item", $item);
		
		$this->addToolbar();
		parent::display($tpl);
	}
	
	protected function addToolbar()
	{
		JToolBarHelper::title(JText::_('OSDOWNLOADS_FILE'));
		JToolBarHelper::save('file.save', 'JTOOLBAR_SAVE');
		JToolBarHelper::apply('file.apply', 'JTOOLBAR_APPLY');
		JToolBarHelper::cancel('cancel', 'JTOOLBAR_CANCEL');
	}	
} 