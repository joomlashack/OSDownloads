<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

use Alledia\Framework\Factory;
use Alledia\OSDownloads\Free\Joomla\Component\Site as FreeComponentSite;
use Alledia\OSDownloads\Free\File;
use Alledia\OSDownloads\Free\Helper;
use Alledia\OSDownloads\Free\Joomla\View\Legacy as LegacyView;


class OSDownloadsViewDownload extends LegacyView
{
    /**
     * @var string
     */
    protected $realName = null;

    /**
     * @var string
     */
    protected $contentType = null;

    /**
     * @var string
     */
    protected $fileSize = null;

    /**
     * @var string
     */
    protected $fileFullPath = null;

    public function display($tpl = null)
    {
        $app = JFactory::getApplication();
        $id  = $app->input->getInt('id');

        $component = FreeComponentSite::getInstance();
        $model     = $component->getModel('Item');
        $item      = $model->getItem($id);

        if (empty($item)) {
            throw new Exception(JText::_("COM_OSDOWNLOADS_THIS_DOWNLOAD_ISNT_AVAILABLE"), 404);
        }

        // The priority is for a relative local path
        if (Helper::isLocalPath($item->file_url)) {
            $fileFullPath   = realpath(JPATH_SITE . $item->file_url);
            $this->realName = basename($item->file_url);
        } else {
            $fileFullPath   = realpath(JPATH_SITE . "/media/com_osdownloads/files/" . $item->file_path);
            $this->realName = substr($item->file_path, strpos($item->file_path, "_") + 1);
        }

        if (empty($fileFullPath)) {
            throw new Exception(JText::_("COM_OSDOWNLOADS_THIS_DOWNLOAD_ISNT_AVAILABLE"), 404);
        }

        $this->contentType  = File::getContentTypeFromFileName($item->file_path);
        $this->fileSize     = filesize($fileFullPath);
        $this->fileFullPath = $fileFullPath;

        parent::display($tpl);
    }
}
