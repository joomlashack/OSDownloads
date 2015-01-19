<?php
/**
 * @package   OSDownloads
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2014 Alledia.com, All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

use Alledia\Framework\Factory;
use Alledia\OSDownloads\Free\File;

jimport('joomla.application.component.controller');

class OSDownloadsController extends JControllerLegacy
{

    public function __construct($default = array())
    {
        parent::__construct($default);
    }

    public function display($cachable = false, $urlparams = false)
    {
        $view = JRequest::getCmd("view", "category");
        JRequest::setVar("view", $view);
        parent::display();
    }

    public function executePlugin($item, $email, $mailchimp_api, $list_id)
    {
        require_once JPATH_SITE . '/administrator/components/com_osdownloads/library/Free/MCAPI.php';

        $mc = new MCAPI($mailchimp_api);
        $merge_vars = array();
        $mc->listSubscribe($list_id, $email, $merge_vars);
    }

    public function getdownloadlink()
    {
        JTable::addIncludePath(JPATH_COMPONENT.'/tables');

        $emailrow = JTable::getInstance('Email', 'OsdownloadsTable');
        $item     = JTable::getInstance('Document', 'OsdownloadsTable');
        $item->load((int) JRequest::getVar("id"));

        $mainframe     = JFactory::getApplication();
        $params        = clone($mainframe->getParams('com_osdownloads'));
        $mailchimp     = $params->get("connect_mailchimp", 0);
        $mailchimp_api = $params->get("mailchimp_api", 0);
        $list_id       = $params->get("list_id", 0);
        $email = JRequest::getVar("email");

        if ($mailchimp && $email) {
            $this->executePlugin($item, $email, $mailchimp_api, $list_id);
        }

        if ($email) {
            //$db->setQuery("SELECT id FROM #__osdownloads_emails WHERE email='{$email}'");
            //if (!$db->loadResult()) {
                $emailrow->email = $email;

                $emailrow->document_id = $item->id;
                $emailrow->downloaded_date = JFactory::getDate()->toSQL();
                $emailrow->store();
            // }
        }

        JRequest::setVar("layout", "download");
        JRequest::setVar("view", "item");

        $this->display();
    }

    public function download()
    {
        $app = JFactory::getApplication();

        $extension = Factory::getExtension('OSDownloads', 'component');
        $extension->loadLibrary();

        $id = (int) $app->input->getVar("id");
        $db = JFactory::getDBO();

        $query = "SELECT documents.*
                  FROM `#__osdownloads_documents` documents
                  WHERE documents.id = " . $db->q($id);

        $db->setQuery($query);
        $item = $db->loadObject();

        // Get file info
        $file        = realpath(JPATH_SITE . "/media/com_osdownloads/files/" . $item->file_path);
        $index       = strpos($item->file_path, "_");
        $realName    = substr($item->file_path, $index + 1);
        $contentType = File::getContentTypeFromFileName($item->file_path);

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

        jexit();
    }
}
