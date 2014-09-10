<?php
/**
 * @version 1.0.0
 * @author Open Source Training (www.ostraining.com)
 * @copyright (C) 2014 Open Source Training
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/
// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

class OSDownloadsController extends JControllerLegacy
{

    public function __construct($default = array())
    {
        parent::__construct($default);
    }

    public function display($cachable = false, $urlparams = false)
    {
        $view = JRequest::getVar("view", "category");
        JRequest::setVar("view", $view);
        parent::display();
    }

    public function executePlugin($item, $email, $mailchimp_api, $list_id)
    {
        require_once 'classes'."/MCAPI.class.php";

        $mc = new MCAPI($mailchimp_api);
        $merge_vars = array();
        $mc->listSubscribe($list_id, $email, $merge_vars);
    }

    public function getdownloadlink()
    {
        JTable::addIncludePath(JPATH_COMPONENT.'/tables');

        $emailrow = JTable::getInstance('Email', 'OsdownloadsTable');
        $item     = JTable::getInstance('Document', 'OsdownloadsTable');
        $item->load(JRequest::getVar("id"));

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
        $id = JRequest::getVar("id");
        $db = JFactory::getDBO();

        $query = "UPDATE `#__osdownloads_documents` SET downloaded = downloaded + 1 WHERE id = {$id}";
        $db->setQuery($query);
        $db->query();

        $query	= "SELECT documents.*
                    FROM `#__osdownloads_documents` documents
                    WHERE documents.id = {$id}";

        $db->setQuery($query);
        $item = $db->loadObject();
        $file = "./media/OSDownloads/" . $item->file_path;
        $index = strpos($item->file_path, "_");
        $realname = substr($item->file_path, $index + 1);

        header("Content-Type: application/force-download");
        header('Content-Description: File Transfer');
        header("Content-Disposition: attachment; filename=\"".$realname."\";");
        @readfile($file);

        exit();
    }
}
