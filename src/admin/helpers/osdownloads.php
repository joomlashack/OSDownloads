<?php
/**
 * @version 1.0.0
 * @author Open Source Training (www.ostraining.com)
 * @copyright (C) 2011- Open Source Training
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/
class OSDownloadsHelper
{
	public static function addSubmenu($vName)
	{

		JSubMenuHelper::addEntry(
			JText::_('COM_OSDOWNLOAD_SUBMENU_FILES'),
			'index.php?option=com_osdownloads&view=files',
			$vName == 'files'
		);

		JSubMenuHelper::addEntry(
			JText::_('COM_OSDOWNLOAD_SUBMENU_CATEGORIES'),
			'index.php?option=com_categories&extension=com_osdownloads',
			$vName == 'categories'
		);
		if ($vName=='categories') {
			JToolBarHelper::title(
				JText::sprintf('COM_CATEGORIES_CATEGORIES_TITLE',JText::_('com_osdownloads')),
				'osdownloads-categories');
		}

		JSubMenuHelper::addEntry(
			JText::_('COM_OSDOWNLOAD_SUBMENU_EMAILS'),
			'index.php?option=com_osdownloads&view=emails',
			$vName == 'emails'
		);
		JSubMenuHelper::addEntry(
			JText::_('COM_OSDOWNLOAD_SUBMENU_LANGUAGES'),
			'index.php?option=com_osdownloads&view=languages',
			$vName == 'languages'
		);

	} 
}
?>