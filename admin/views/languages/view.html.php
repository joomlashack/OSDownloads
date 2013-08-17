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

class OSDownloadsViewLanguages extends JView
{
	function display($tpl = null)
	{
		jimport('joomla.filesystem.file'); 
		
		$db = & JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('a.*');
		$query->from('`#__extensions` AS a');
		$query->where('a.client_id = 0 AND a.type = "language"');
		$db->setQuery($query);
		$languages = $db->loadObjectList();
		
		$lang = $languages[0]->element;
		if (JRequest::getVar("language_code"))
			$lang = JRequest::getVar("language_code");
		
		$file = $lang . ".com_osdownloads.ini";
		
		$lang_file = JPath::clean(JPATH_SITE.DS."language".DS.$lang.DS.$file);
		if (!JFile::exists($lang_file))
		{
			foreach ($languages as $language)
			{
				$lang_file = JPath::clean(JPATH_SITE.DS."language".DS.$language->element.DS.$language->element.".com_osdownloads.ini");
				if (JFile::exists($lang_file)) 
					break;
			}
		}
		$defines = array();
		if (JFile::exists($lang_file))
		{
			$content = JFile::read($lang_file);
			$lines = preg_split("/\n/", $content);
			
			foreach ($lines as $line)
			{
				if (strlen(trim($line)))
				{
					list($key, $value) = preg_split("/=/", $line);
					$value = substr($line, strlen($key) + 1);	
					$defines[$key] = trim(preg_replace("/\"/", "", $value));
				}
			}
		}
		
		$this->assignRef("languages", $languages);		
		$this->assignRef("defines", $defines);		
		$this->assignRef("lang", $lang);
		$this->addToolbar();
		parent::display($tpl);
		
	}

	protected function addToolbar()
	{
		JToolBarHelper::title(JText::_('COM_OSDOWNLOAD_SUBMENU_LANGUAGES'));
		JToolBarHelper::apply('language.apply', 'JTOOLBAR_APPLY');
	}	
}
?>