<?php
/**
 * @version 1.0.0
 * @author Open Source Training (www.ostraining.com)
 * @copyright (C) 2011- Open Source Training
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/
// no direct access
defined('_JEXEC') or die;

require_once JPATH_COMPONENT.DS.'controller.php';

$doc = JFactory::getDocument();
$doc->addStyleSheet(JURI::Root(). "components/com_osdownloads/assets/osdownloads.css");
	
$controller	= new OSDownloadsController( ); 
$controller->execute(JRequest::getCmd('task', 'display'));
$controller->redirect();

?>