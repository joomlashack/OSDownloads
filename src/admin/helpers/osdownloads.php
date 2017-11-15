<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2017 Open Source Training, LLC. All rights reserved
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

use Alledia\OSDownloads\Free\Factory;

class OSDownloadsHelper
{
    public static function addSubmenu($vName)
    {
        $container = Factory::getContainer();

        // Joomla 3.x Backward Compatibility
        if (JFactory::getApplication()->input->getCmd('option') == 'com_categories') {
            $subMenuClass = 'JHtmlSidebar';
        } else {
            $subMenuClass = 'JSubMenuHelper';
        }

        $subMenuClass::addEntry(
            JText::_('COM_OSDOWNLOADS_SUBMENU_FILES'),
            $container->helperRoute->getAdminFileListRoute(),
            $vName == 'files'
        );

        $subMenuClass::addEntry(
            JText::_('COM_OSDOWNLOADS_SUBMENU_CATEGORIES'),
            $container->helperRoute->getAdminCategoryListRoute(),
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

        $subMenuClass::addEntry(
            JText::_('COM_OSDOWNLOADS_SUBMENU_EMAILS'),
            $container->helperRoute->getAdminEmailListRoute(),
            $vName == 'emails'
        );

        // Load responsive CSS
        JHtml::stylesheet('media/jui/css/jquery.searchtools.css');
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
