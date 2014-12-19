<?php
/**
 * @package   OSDownloads
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2014 Alledia.com, All rights reserved
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

use Alledia\Framework\Factory;

class OSDownloadsControllerFile extends JControllerLegacy
{
    /**
     * Alledia Extension
     * @var Licensed
     */
    protected $extension;

    public function __construct($default = array())
    {
        parent::__construct($default);

        $this->registerTask('apply', 'save');
        $this->registerTask('unpublish', 'publish');
        $this->registerTask('orderup', 'reorder');
        $this->registerTask('orderdown', 'reorder');

        // Load the extension
        $this->extension = Factory::getExtension('OSDownloads', 'component');
        $this->extension->loadLibrary();
    }

    public function save()
    {
        JRequest::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));
        JTable::addIncludePath(JPATH_COMPONENT.'/tables');
        jimport('joomla.filesystem.file');
        jimport('joomla.filesystem.folder');

        $row  = JTable::getInstance('Document', 'OsdownloadsTable');
        $post = JRequest::get('post');

        $row->bind($post['jform']);

        $text    = $post['jform']['description_1'];
        $text    = str_replace('<br>', '<br />', $text);
        $pattern = '#<hr\s+id=("|\')system-readmore("|\')\s*\/*>#i';
        $tagPos  = preg_match($pattern, $text);
        if ($tagPos == 0) {
            $row->brief = $text;
            $row->description_1 = "";
        } else {
            list($row->brief, $row->description_1) = preg_split($pattern, $text, 2);
        }

        $row->require_email = (int) $row->require_email;
        $row->require_agree = (int) $row->require_agree;

        if (version_compare(JVERSION, '3.0', 'lt') && (!empty($post['id']))) {
            $row->id = $post['id'];
        }

        $files = JRequest::get('files');
        $file  = $files['jform'];

        if (!empty($file['name'])) {
            $file["name"]['file'] = JFile::makeSafe($file["name"]['file']);

            if (isset($file["name"]['file']) && $file["name"]['file']) {
                $uploadDir = JPATH_SITE . "/media/com_osdownloads/files/";

                if (isset($post["old_file"]) && JFile::exists(JPath::clean($uploadDir . $post["old_file"]))) {
                    unlink(JPath::clean($uploadDir . $post["old_file"]));
                }

                if (!JFolder::exists(JPath::clean($uploadDir))) {
                    JFolder::create(JPath::clean($uploadDir));
                }

                $timestamp = md5(microtime());
                $filepath = JPath::clean($uploadDir . $timestamp . "_" . $file["name"]['file']);
                $row->file_path = $timestamp . "_" . $file["name"]['file'];
                JFile::upload($file["tmp_name"]['file'], $filepath);
            }
        }

        $row->store();
        switch ($this->getTask()) {
            case "apply":
                $this->setRedirect("index.php?option=com_osdownloads&view=file&cid=" . $row->id, JText::_("COM_OSDOWNLOADS_DOCUMENT_IS_SAVED"));
                break;
            default:
                $this->setRedirect("index.php?option=com_osdownloads&view=files", JText::_("COM_OSDOWNLOADS_DOCUMENT_IS_SAVED"));
        }
    }

    public function publish()
    {
        // Check for request forgeries
        JRequest::checkToken() or jexit('Invalid Token');

        $db = JFactory::getDBO();

        $cid     = JRequest::getVar('cid', array(), '', 'array');
        $publish = ($this->getTask() == 'publish' ? 1 : 0);

        JArrayHelper::toInteger($cid);

        $cids = implode(',', $cid);

        $query = 'UPDATE `#__osdownloads_documents`'
        . ' SET published = ' . (int) $publish
        . ' WHERE id IN ('. $cids .')';
        $db->setQuery($query);
        if (!$db->query()) {
            JError::raiseError(500, $db->getErrorMsg());
        }
        $this->setRedirect('index.php?option=com_osdownloads&view=files');
    }

    public function delete()
    {
        JRequest::checkToken() or jexit('Invalid Token');
        JTable::addIncludePath(JPATH_COMPONENT.'/tables');

        jimport('joomla.filesystem.file');

        $db  = JFactory::getDBO();
        $cid = JRequest::getVar('cid', array(), '', 'array');

        JArrayHelper::toInteger($cid);

        $cids = implode(',', $cid);

        $query = 'SELECT * FROM `#__osdownloads_documents` WHERE id IN ('. $cids .')';
        $db->setQuery($query);
        $rows = $db->loadObjectList();

        $uploadDir = JPATH_SITE . "/media/com_osdownloads/files/";

        foreach ($rows as $item) {
            $filepath = JPath::clean($uploadDir . $item->file_path);
            if (JFile::exists($filepath)) {
                JFile::delete($filepath);
            }
        }

        foreach ($cid as $id) {
            $document = JTable::getInstance('Document', 'OsdownloadsTable');
            if (!$document->delete(array('id' => $id))) {
                JError::raiseError(500, $db->getErrorMsg());
            } else {
                $query = 'SELECT id FROM `#__osdownloads_emails` WHERE document_id = '. (int) $id;
                $db->setQuery($query);
                $emails = $db->loadObjectList();

                if (!empty($emails)) {
                    foreach ($emails as $emailId) {
                        if (!empty($emailId)) {
                            $email = JTable::getInstance('Email', 'OsdownloadsTable');
                            $email->delete(array('id' => $emailId->id));
                        }
                    }
                }
            }
        }

        $this->setRedirect('index.php?option=com_osdownloads&view=files', JText::_("COM_OSDOWNLOADS_FILES_ARE_DELETED"));

    }

    public function saveorder()
    {
        // Check for request forgeries
        JRequest::checkToken() or jexit('Invalid Token');
        JTable::addIncludePath(JPATH_COMPONENT.'/tables');

        // Initialize some variables
        $db = JFactory::getDBO();

        $cid = JRequest::getVar('cid', array(), 'post', 'array');
        JArrayHelper::toInteger($cid);

        if (empty($cid)) {
            return JError::raiseWarning(500, 'No items selected');
        }

        $total = count($cid);
        $row   = JTable::getInstance('Document', 'OsdownloadsTable');
        $groupings = array();

        $order = JRequest::getVar('order', array(0), 'post', 'array');
        JArrayHelper::toInteger($order);

        // update ordering values
        for ($i = 0; $i < $total; $i++) {
            $row->load((int) $cid[$i]);
            // track postions
            $groupings[] = $row->cate_id;

            if ($row->ordering != $order[$i]) {
                $row->ordering = $order[$i];
                if (!$row->store()) {
                    //return JError::raiseWarning( 500, $db->getErrorMsg());
                }
            }
        }

        // execute updateOrder for each parent group
        $groupings = array_unique($groupings);
        foreach ($groupings as $group) {
            $row->reorder('cate_id = '.$db->Quote($group));
        }

        $this->setMessage(JText::_('COM_OSDOWNLOADS_NEW_ORDERING_SAVED'));
        $this->setRedirect('index.php?option=com_osdownloads&view=files');
    }

    public function reorder()
    {
        global $mainframe;
        JTable::addIncludePath(JPATH_COMPONENT.'/tables');

        // Check for request forgeries
        JRequest::checkToken() or jexit('Invalid Token');

        // Initialize some variables
        $db = JFactory::getDBO();

        $cid = JRequest::getVar('cid', array(), 'post', 'array');
        JArrayHelper::toInteger($cid);

        $task = $this->getTask();
        $inc  = ($task == 'orderup' ? -1 : 1);

        if (empty($cid)) {
            return JError::raiseWarning(500, 'No items selected');
        }

        $row = JTable::getInstance('Document', 'OsdownloadsTable');
        $row->load((int) $cid[0]);

        $row->move($inc, 'cate_id = '.$db->Quote($row->cate_id));
        $this->setMessage(JText::_('COM_OSDOWNLOADS_NEW_ORDERING_SAVED'));
        $this->setRedirect('index.php?option=com_osdownloads&view=files');
    }
}
