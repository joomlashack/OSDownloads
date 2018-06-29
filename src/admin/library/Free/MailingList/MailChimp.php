<?php
/**
 * @package    OSDownloads
 * @contact    www.joomlashack.com, help@joomlashack.com
 * @copyright  2018 Open Source Training, LLC. All rights reserved
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
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

namespace Alledia\OSDownloads\Free\MailingList;

use JObservableInterface;
use JObserverInterface;

defined('_JEXEC') or die();

class MailChimp implements \JObserverInterface
{
    /**
     * @var \JTable
     */
    protected $table = null;

    public function __construct(JObservableInterface $table)
    {
        $table->attachObserver($this);
        $this->table = $table;
    }

    /**
     * Creates the associated observer instance and attaches it to the $observableObject
     *
     * @param   JObservableInterface $observableObject The observable subject object
     * @param   array                $params           Params for this observer
     *
     * @return  JObserverInterface
     *
     * @since   3.1.2
     */
    public static function createObserver(JObservableInterface $observableObject, $params = array())
    {
        $observer = new self($observableObject);

        return $observer;
    }

    public function onAfterStore($result)
    {
        \JFactory::getApplication()->enqueueMessage(__METHOD__ . '<br/>RESULT:' . print_r($result));
    }

    public function addToMailchimpList()
    {
        $app    = Factory::getApplication();
        $params = $app->getParams('com_osdownloads');
        $apiKey = $params->get("mailinglist.mailchimp.api", 0);
        $listId = $params->get("mailinglist.mailchimp.list_id", 0);

        if (!empty($this->email)) {
            $mc = new \Mailchimp\Mailchimp($apiKey);

            // Check if the email already exists
            try {
                $result = $mc->get("lists/{$listId}/members/" . md5(strtolower($this->email)));
                $result = $result->toArray();
            } catch (Exception $e) {
                $result = array('status' => 'unsubscribed');
            }

            if ($result['status'] === 'unsubscribed') {
                // The email is not subscribed. Let's subscribe it.
                $mc->post("lists/{$listId}/members/", array(
                    'email_address' => $this->email,
                    'status'        => 'subscribed'
                ));
            }
        }
    }


}
