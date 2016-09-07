<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace Alledia\OSDownloads\Free\Joomla\Controller\Admin;

use Alledia\Framework\Factory;
use Alledia\Framework\Joomla\Controller\Base as BaseController;
use Alledia\OSDownloads\Free\Joomla\Component\Site as FreeComponentSite;
use JControllerLegacy;
use JText;

defined('_JEXEC') or die();

jimport('joomla.application.component.controller');


class Emails extends JControllerLegacy
{
    public function delete()
    {
        $app = Factory::getApplication();

        $id_arr = $app->input->getVar('cid');
        $str_id = implode(',', $id_arr);
        $db = Factory::getDBO();
        $query = "DELETE FROM `#__osdownloads_emails` WHERE id IN (".$str_id.")";
        $db->setQuery($query);
        $db->query();
        $this->setRedirect("index.php?option=com_osdownloads&view=emails", JText::_("COM_OSDOWNLOADS_EMAIL_IS_DELETED"));
    }
}
