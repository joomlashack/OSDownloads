<?php
/**
 * @package   OSDownloads
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2014 Alledia.com, All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('legacy.model.legacy');

abstract class AbstractOSDownloadsModelEmail extends JModelLegacy
{
    protected function prepareRow(&$row)
    {
        // Method to allow inheritance
    }

    public function insert($email, $documentId)
    {
        JTable::addIncludePath(JPATH_SITE . '/components/com_osdownloads/tables');
        $row = JTable::getInstance('Email', 'OsdownloadsTable');

        $row->email = $email;

        $row->document_id = $documentIdn;
        $row->downloaded_date = JFactory::getDate()->toSQL();

        $this->prepareRow($row);

        $row->store();

        return $row;
    }
}
