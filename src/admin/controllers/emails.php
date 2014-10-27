<?php
/**
 * @package   OSDownloads
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2014 Alledia.com, All rights reserved
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

class OSDownloadsControllerEmails extends JControllerLegacy
{
    public function delete()
    {
        $app = JFactory::getApplication();

        $id_arr = $app->input->getVar('cid');
        $str_id = implode(',', $id_arr);
        $db = JFactory::getDBO();
        $query = "DELETE FROM `#__osdownloads_emails` WHERE id IN (".$str_id.")";
        $db->setQuery($query);
        $db->query();
        $this->setRedirect("index.php?option=com_osdownloads&view=emails", JText::_("COM_OSDOWNLOADS_EMAIL_IS_DELETED"));
    }
}
