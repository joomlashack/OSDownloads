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

    /**
     * @var string[]
     */
    protected $headers = null;

    /**
     * @var bool
     */
    protected $isLocal = true;

    public function display($tpl = null)
    {
        $app = JFactory::getApplication();
        $id  = $app->input->getInt('id');

        $component = FreeComponentSite::getInstance();
        $model     = $component->getModel('Item');
        $item      = $model->getItem($id);

        if (empty($item)) {
            $this->setLayout('error_not_available');
            parent::display();
            return;
        }

        if ($item->file_url) {
            $this->realName = basename($item->file_url);

            if (Helper::isLocalPath($item->file_url)) {
                $fileFullPath = realpath(JPATH_SITE . '/' . ltrim($item->file_url, '/'));
                if (is_file($fileFullPath)) {
                    $this->fileSize = filesize($fileFullPath);
                } else {
                    $fileFullPath = null;
                }

            } else {
                $this->isLocal = false;

                // Triggers the onOSDownloadsGetExternalDownloadLink event
                JPluginHelper::importPlugin('osdownloads');

                $dispatcher = JEventDispatcher::getInstance();
                $dispatcher->trigger('onOSDownloadsGetExternalDownloadLink', array(&$item));

                $fileFullPath = $item->file_url;

                $this->headers = File::getHeaders($fileFullPath);
                if (!empty($this->headers['Content-Length'])) {
                    $this->fileSize = $this->headers['Content-Length'];
                }

                // Adjust for redirects
                if (!empty($this->headers['Location'])) {
                    $fileFullPath = $this->headers['Location'];
                }
            }

        } else {
            $fileFullPath   = realpath(JPATH_SITE . "/media/com_osdownloads/files/" . $item->file_path);
            $this->realName = substr($item->file_path, strpos($item->file_path, "_") + 1);
            $this->fileSize = filesize($fileFullPath);
        }

        if (empty($fileFullPath)) {
            $this->setLayout('error_not_available');
            parent::display();
            return;
        }

        $this->contentType  = File::getContentTypeFromFileName($fileFullPath);
        $this->fileFullPath = $fileFullPath;

        $model->incrementDownloadCount($id);

        parent::display($tpl);
    }
}
