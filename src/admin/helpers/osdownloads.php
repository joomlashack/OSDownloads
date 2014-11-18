<?php
/**
 * @package   OSDownloads
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2014 Alledia.com, All rights reserved
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;
class OSDownloadsHelper
{
    public static function addSubmenu($vName)
    {
        // Joomla 3.x only in Categories view
        if (version_compare(JVERSION, '3.0', 'ge') && JFactory::getApplication()->input->getCmd('option') == 'com_categories' ){
            JHtmlSidebar::addEntry(
                JText::_('COM_OSDOWNLOADS_SUBMENU_FILES'),
                'index.php?option=com_osdownloads&view=files',
                $vName == 'files'
            );

            JHtmlSidebar::addEntry(
                JText::_('COM_OSDOWNLOADS_SUBMENU_CATEGORIES'),
                'index.php?option=com_categories&extension=com_osdownloads',
                $vName == 'categories'
            );
            if ($vName=='categories') {
                JToolBarHelper::title(
                    JText::sprintf(
                        'COM_CATEGORIES_CATEGORIES_TITLE',
                        JText::_('COM_OSDOWNLOADS')
                    ),
                    'osdownloads-categories'
                );
            }

            JHtmlSidebar::addEntry(
                JText::_('COM_OSDOWNLOADS_SUBMENU_EMAILS'),
                'index.php?option=com_osdownloads&view=emails',
                $vName == 'emails'
            );
        }
        // Joomla 2.5
        else{
            JSubMenuHelper::addEntry(
                JText::_('COM_OSDOWNLOADS_SUBMENU_FILES'),
                'index.php?option=com_osdownloads&view=files',
                $vName == 'files'
            );

            JSubMenuHelper::addEntry(
                JText::_('COM_OSDOWNLOADS_SUBMENU_CATEGORIES'),
                'index.php?option=com_categories&extension=com_osdownloads',
                $vName == 'categories'
            );
            if ($vName=='categories') {
                JToolBarHelper::title(
                    JText::sprintf(
                        'COM_CATEGORIES_CATEGORIES_TITLE',
                        JText::_('COM_OSDOWNLOADS')
                    ),
                    'osdownloads-categories'
                );
            }

            JSubMenuHelper::addEntry(
                JText::_('COM_OSDOWNLOADS_SUBMENU_EMAILS'),
                'index.php?option=com_osdownloads&view=emails',
                $vName == 'emails'
            );
        }
    }

    /**
     * Get the files the user has access to, filtering or not by the externalRef.
     *
     * @param  int    $userId
     * @param  string $externalRef
     * @return array
     */
    public static function getAuthorizedFilesForUser($userId, $externalRef = '')
    {
        // Flush any JAccess cache
        JAccess::clearStatics();

        $authorizedViewLevels = JAccess::getAuthorisedViewLevels($userId);
        $authorizedViewLevels = implode(',', $authorizedViewLevels);

        // Get the files the user has access to and external ref has the suffix .pro
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__osdownloads_documents')
            ->where("access IN ($authorizedViewLevels)");

        if ($externalRef !== '') {
            $query->where('external_ref LIKE "' . $externalRef . '"');
        }
        $db->setQuery($query);

        return $db->loadObjectList();
    }
}
