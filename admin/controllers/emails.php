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

class OSDownloadsControllerEmails extends JController
	{
		public function delete(){
		$id_arr = Jrequest::getVar('cid');
		$str_id = implode(',',$id_arr);
		$db = JFactory::getDBO();
		$query = "DELETE FROM #__osdownloads_emails WHERE id IN (".$str_id.")";
		$db->setQuery($query);
		$db->query();
		$this->setRedirect("index.php?option=com_osdownloads&view=emails");
	}
} 