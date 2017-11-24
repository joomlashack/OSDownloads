<?php
/**
 * @package   com_osdownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2017 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace Alledia\OSDownloads\Free\Helper;

use JHtmlSidebar;
use JText;
use JToolBarHelper;
use JAccess;
use JFactory;

defined('_JEXEC') or die();

class Helper
{
    /**
     * @param string $vName
     *
     * @return void
     */
    public static function addSubmenu($vName)
    {
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
        if ($vName == 'categories') {
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

        // Load responsive CSS
        JHtml::stylesheet('media/jui/css/jquery.searchtools.css');
    }

    /**
     * Validate an email address. Used this to have the option to accept
     * plus signs in the email validation. The native PHP filter
     * doesn't support + in the email address, which is now allowed.
     *
     * @param string $email
     * @param bool   $acceptPlusSign
     *
     * @return bool
     */
    public static function validateEmail($email, $acceptPlusSign = true)
    {
        if ($acceptPlusSign) {
            $pattern = '/^([a-z0-9_\-\.\+])+\@([a-z0-9_\-\.])+\.([a-z]{2,25})$/i';

            return (bool)preg_match($pattern, $email);
        }

        $valid = filter_var($email, FILTER_VALIDATE_EMAIL);

        return !empty($valid);
    }

    /**
     * Check if the path is a local path and exists. Otherwise it can means
     * we have an external URL.
     *
     * @param string $path
     *
     * @return bool
     */
    public static function isLocalPath($path)
    {
        // Is an external URL or empty path?
        if (empty($path) || preg_match('#(?:^//|[a-z0-9]+?://)#i', $path)) {
            return false;
        }

        // If the file exists, it is a local path
        return  \JFile::exists(realpath(JPATH_SITE . '/' . ltrim($path, '/')));
    }

    /**
     * Get the files the user has access to, filtering or not by the externalRef.
     *
     * @param  int    $userId
     * @param  string $externalRef
     *
     * @return array
     */
    public static function getAuthorizedFilesForUser($userId, $externalRef = '')
    {
        // Flush any JAccess cache
        JAccess::clearStatics();

        $authorizedViewLevels = JAccess::getAuthorisedViewLevels($userId);
        $authorizedViewLevels = implode(',', $authorizedViewLevels);

        // Get the files the user has access to and external ref has the suffix .pro
        $db    = JFactory::getDbo();
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
