<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2005-2019 Joomlashack.com. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 *
 * This file is part of OSDownloads.
 *
 * OSDownloads is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * OSDownloads is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OSDownloads.  If not, see <http://www.gnu.org/licenses/>.
 */

defined('_JEXEC') or die;

use Alledia\Framework\Factory;
use Joomla\Utilities\ArrayHelper;
use Alledia\OSDownloads\Free\Factory as OSDFactory;

class OSDownloadsControllerFile extends JControllerForm
{
    /**
     * Alledia Extension
     * @var \Alledia\Framework\Joomla\Extension\Licensed
     */
    protected $extension;

    public function __construct($default = array())
    {
        parent::__construct($default);

        $this->registerTask('apply', 'save');
        $this->registerTask('unpublish', 'publish');
        $this->registerTask('save2new', 'save');
        $this->registerTask('save2copy', 'save');

        // Load the extension
        $this->extension = Factory::getExtension('OSDownloads', 'component');
        $this->extension->loadLibrary();
    }

    public function publish()
    {
        // Check for request forgeries
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $app       = JFactory::getApplication();
        $db        = JFactory::getDBO();
        $date      = JFactory::getDate();
        $user      = JFactory::getUser();
        $container = OSDFactory::getContainer();

        $cid     = $app->input->get('cid', array(), 'array');
        $publish = ($this->getTask() == 'publish' ? 1 : 0);

        ArrayHelper::toInteger($cid);

        $cids = implode(',', $cid);

        $query = 'UPDATE `#__osdownloads_documents`'
        . ' SET published = ' . (int) $publish . ','
        . ' modified_user_id = ' . (int) $user->get('id') . ','
        . ' modified_time = ' . $date->toSql()
        . ' WHERE id IN ('. $cids .')';
        $db->setQuery($query);
        if (!$db->query()) {
            JError::raiseError(500, $db->getErrorMsg());
        }
        $this->setRedirect(
            $container->helperRoute->getAdminFileListRoute()
        );
    }

    public function delete()
    {
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
        JTable::addIncludePath(JPATH_COMPONENT.'/tables');

        jimport('joomla.filesystem.file');

        $db        = JFactory::getDBO();
        $cid       = JFactory::getApplication()->input->get('cid', array(), 'array');
        $container = OSDFactory::getContainer();

        ArrayHelper::toInteger($cid);

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

        $this->setRedirect(
            $container->helperRoute->getAdminFileListRoute(),
            JText::_("COM_OSDOWNLOADS_FILES_ARE_DELETED")
        );
    }
}
