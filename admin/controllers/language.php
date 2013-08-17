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

class OSDownloadsControllerLanguage extends JController
{

	function __construct( $default = array())
	{
		parent::__construct( $default );

		$this->registerTask( 'apply', 			'save');
	}
	
 	function display()
	{
		parent::display();
	}
	
	function save()
	{
		JRequest::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));
		jimport('joomla.filesystem.file'); 
		
		$language_code = JRequest::getVar("language_code");		
		$defines = JRequest::getVar("defines", '', 'post', 'string', JREQUEST_ALLOWRAW);
		
		$file = $language_code . ".com_osdownloads.ini";
		$lang_file = JPath::clean(JPATH_SITE.DS."language".DS.$language_code.DS.$file);
		
		$lines = array();
		foreach ($defines as $key => $value)
		{
			$lines[] = preg_replace("/'/", "", $key) . '="' . $value . '"';
		}
		
		$lines = implode("\r\n", $lines);
		JFile::write($lang_file, $lines);
		$this->setRedirect("index.php?option=com_osdownloads&view=languages&language_code={$language_code}", JText::_("Language file is saved"));
	}
	
}
?>