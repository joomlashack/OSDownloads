<?php
/**
 * @package   OSDownloads
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2015 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace Alledia\OSDownloads\Free\Joomla\Table;

defined('_JEXEC') or die();

use Alledia\Framework\Joomla\Table\Base as BaseTable;
use Alledia\OSDownloads\Free\MailChimpAPI;


class Email extends BaseTable
{
    public $id;
    public $email;
    public $document_id;
    public $downloaded_date;

    public function __construct(&$db)
    {
        parent::__construct('#__osdownloads_emails', 'id', $db);
    }

    public function addToMailchimpList($mailchimpAPIKey, $mailchimpListId)
    {
        if (!empty($this->email)) {

            $mc = new MailChimpAPI($mailchimpAPIKey);
            $merge_vars = array();
            $mc->listSubscribe($mailchimpListId, $this->email, $merge_vars);
        }
    }
}
