<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2018 Open Source Training, LLC. All rights reserved
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

jimport('legacy.controller.admin');

require_once JPATH_ADMINISTRATOR . '/components/com_osdownloads/models/items.php';

class OSDownloadsControllerFiles extends JControllerAdmin
{
    public function getModel($name = '', $prefix = '', $config = array())
    {
        return OSDownloadsModelItems::getInstance('Items', 'OSDownloadsModel');
    }
}
