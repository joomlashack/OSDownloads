<?php
/**
 * @package   OSDownloads
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2014 Alledia.com, All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace Alledia\OSDownloads\Free\Joomla\Controller;

defined('_JEXEC') or die();

use Alledia\Framework\Joomla\Controller\Base as BaseController;
use JRequest;


class Site extends BaseController
{
    public function display($cachable = false, $urlparams = false)
    {
        $view = JRequest::getCmd("view", "category");
        JRequest::setVar("view", $view);
        parent::display();
    }

    public function getdownloadlink()
    {
        $app              = JFactory::getApplication();
        $params           = clone($app->getParams('com_osdownloads'));
        $mailchimpConnect = $params->get("connect_mailchimp", 0);
        $mailchimpAPIKey  = $params->get("mailchimp_api", 0);
        $mailchimpListId  = $params->get("list_id", 0);

        $id    = (int) JRequest::getVar("id");
        $email = JRequest::getVar("email");

        $model = JModelLegacy::getInstance('OSDownloadsModelItem');
        $item = $model->getItem($id);

        // Must verify the e-mail before download?
        if ($item->require_email == 1 || ($item->require_email == 2 && !empty($email))) {

            if (empty($email)) {
                JRequest::setVar("layout", "error_empty_email");
            }

            $modelEmail = JModelLegacy::getInstance('OSDownloadsModelEmail');
            $emailRow = $modelEmail->insert($email, $item->id);

            if ($mailchimpConnect) {
                // @TODO: After confirm only
                $emailRow->addToMailchimpList($item, $mailchimpAPIKey, $mailchimpListId);
            }

            // Send e-mail with link

            // redirect view to a message
        } else {
            // Directly output the file to download
            JRequest::setVar("layout", "download");
        }

        JRequest::setVar("view", "item");

        $this->display();
    }

    protected function outputFileContentAsResponse($filePath)
    {
        // Get file info
        $file        = realpath(JPATH_SITE . "/media/com_osdownloads/files/" . $filePath);
        $index       = strpos($filePath, "_");
        $realName    = substr($filePath, $index + 1);
        $contentType = File::getContentTypeFromFileName($filePath);

        header("Content-Disposition: attachment; filename=\"" . $realName . "\";");
        header('Content-Description: File Transfer');
        header('Content-Transfer-Encoding: binary');
        header('Content-Type: ' . $contentType);
        header('Connection: Keep-Alive');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));

        @readfile($file);
    }

    public function download()
    {
        $app = JFactory::getApplication();

        $extension = Factory::getExtension('OSDownloads', 'component');
        $extension->loadLibrary();

        $model = JModelLegacy::getInstance('OSDownloadsModelItem');
        $item = $model->getItem();

        // @TODO: check e-mail valid and permission before download
        $this->outputFileContentAsResponse($item->file_path);

        jexit();
    }
}
