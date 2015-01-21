<?php
/**
 * @package   OSDownloads
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2014 Alledia.com, All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

use Alledia\Framework\Factory;
use Alledia\OSDownloads\Free\Joomla\Component\Site as FreeComponentSite;

jimport('joomla.application.component.view');

class OSDownloadsViewDownload extends JViewLegacy
{
    public function display($tpl = null)
    {
        $app    = JFactory::getApplication();
        $params = $app->getParams('com_osdownloads');
        $id     = JRequest::getVar('id', 0, 'int');

        $component = FreeComponentSite::getInstance();
        $model     = $component->getModel('Item');
        $item      = $model->getItem($id);

        if (empty($item)) {
            JError::raiseWarning(404, JText::_("COM_OSDOWNLOADS_THIS_DOWNLOAD_ISNT_AVAILABLE"));
            return;
        }

        $fileFullPath = realpath(JPATH_SITE . "/media/com_osdownloads/files/" . $item->file_path);

        $this->assign('realName', substr($item->file_path, strpos($item->file_path, "_") + 1));
        $this->assign('contentType', File::getContentTypeFromFileName($item->file_path));
        $this->assign('fileSize', filesize($fileFullPath));

        parent::display($tpl);
        jexit();
    }
}
