<?php
/**
 * @package   OSDownloads
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2014 Alledia.com, All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

class OsdownloadsTableEmail extends JTable
{
    public $id;
    public $email;
    public $document_id;
    public $downloaded_date;

    public function __construct(&$db)
    {
        parent::__construct('#__osdownloads_emails', 'id', $db);
    }

    public function addToMailchimpList($item, $mailchimpAPIKey, $mailchimpListId)
    {
        if (!empty($this->email)) {
            require_once JPATH_SITE . '/administrator/components/com_osdownloads/library/Free/MCAPI.php';

            $mc = new MCAPI($mailchimpAPIKey);
            $merge_vars = array();
            $mc->listSubscribe($mailchimpListId, $this->email, $merge_vars);
        }
    }
}
