<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2005-2020 Joomlashack.com. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 *
 * This file is part of OSDownloads.
 *
 * OSDownloads is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * OSDownloads is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OSDownloads.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Alledia\OSDownloads\Free\Helper;

use Alledia\OSDownloads\Free\MailingList\MailChimp;
use Exception;
use JEventDispatcher;
use JHtmlSidebar;
use Joomla\CMS\Access\Access;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Registry\Registry;

defined('_JEXEC') or die();

class Helper
{
    /**
     * @var JEventDispatcher
     */
    protected static $dispatcher = null;

    /**
     * @param string $vName
     *
     * @return void
     * @throws Exception
     */
    public static function addSubmenu($vName)
    {
        JHtmlSidebar::addEntry(
            Text::_('COM_OSDOWNLOADS_SUBMENU_FILES'),
            'index.php?option=com_osdownloads&view=files',
            $vName == 'files'
        );

        JHtmlSidebar::addEntry(
            Text::_('COM_OSDOWNLOADS_SUBMENU_CATEGORIES'),
            'index.php?option=com_categories&extension=com_osdownloads',
            $vName == 'categories'
        );
        if ($vName == 'categories') {
            ToolbarHelper::title(
                Text::sprintf(
                    'COM_CATEGORIES_CATEGORIES_TITLE',
                    Text::_('COM_OSDOWNLOADS')
                ),
                'osdownloads-categories'
            );
        }

        JHtmlSidebar::addEntry(
            Text::_('COM_OSDOWNLOADS_SUBMENU_EMAILS'),
            'index.php?option=com_osdownloads&view=emails',
            $vName == 'emails'
        );

        // Load responsive CSS
        HTMLHelper::_('stylesheet', 'media/jui/css/jquery.searchtools.css');

        static::displayAdminMessages();
    }

    /**
     * @return void
     * @throws Exception
     */
    public static function displayAdminMessages()
    {
        $params = ComponentHelper::getParams('com_osdownloads');

        // Check MailChimp php requirements
        if ($params->get('mailinglist.mailchimp.api')) {
            if ($warning = MailChimp::getPhpUpgradeMessage()) {
                Factory::getApplication()->enqueueMessage($warning, 'warn');
            }
        }
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
        return \JFile::exists(realpath(JPATH_SITE . '/' . ltrim($path, '/')));
    }

    /**
     * Get the files the user has access to, filtering or not by the externalRef.
     *
     * @param int    $userId
     * @param string $externalRef
     *
     * @return array
     */
    public static function getAuthorizedFilesForUser($userId, $externalRef = '')
    {
        // Flush any Access cache
        Access::clearStatics();

        $authorizedViewLevels = Access::getAuthorisedViewLevels($userId);
        $authorizedViewLevels = implode(',', $authorizedViewLevels);

        // Get the files the user has access to and external ref has the suffix .pro
        $db    = Factory::getDbo();
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

    /**
     * Standard item model amendments
     *
     * @param object $item
     *
     * @return void
     */
    public static function prepareItem(&$item)
    {
        if (static::$dispatcher === null) {
            \JLoader::register('ContentHelperRoute', JPATH_SITE . '/components/com_content/helpers/route.php');
            static::$dispatcher = JEventDispatcher::getInstance();
        }

        if (!($item->params instanceof Registry)) {
            $item->params = new Registry($item->params);
        }

        if ((bool)$item->require_agree && (int)$item->agreement_article_id) {
            $item->agreementLink = \JRoute::_(\ContentHelperRoute::getArticleRoute($item->agreement_article_id));

        } else {
            $item->agreementLink = '';
        }

        \JPluginHelper::importPlugin('content');

        // Make compatible with content plugins
        $item->text = null;

        static::$dispatcher->trigger(
            'onContentPrepare',
            ['com_osdownloads.file', &$item, &$item->params, null]
        );

        $prepareEvent = [
            'afterDisplayTitle'    => static::$dispatcher->trigger(
                'onContentAfterTitle',
                ['com_osdownloads.file', &$item, &$item->params, null]
            ),
            'beforeDisplayContent' => static::$dispatcher->trigger(
                'onContentBeforeDisplay',
                ['com_osdownloads.file', &$item, &$item->params, null]
            ),
            'afterDisplayContent'  => static::$dispatcher->trigger(
                'onContentAfterDisplay',
                ['com_osdownloads.file', &$item, &$item->params, null]
            )
        ];
        foreach ($prepareEvent as &$results) {
            $results = trim(join("\n", $results));
        }
        $item->event = (object)$prepareEvent;
    }
}
