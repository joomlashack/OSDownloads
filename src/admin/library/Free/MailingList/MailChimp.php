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

use Alledia\OSDownloads\Free\Factory;
use Alledia\OSDownloads\Free\Joomla\Table\Email as EmailTableFree;
use Alledia\OSDownloads\MailingLists\AbstractClient;
use Exception;
use JObservableInterface;
use JObserverInterface;
use Joomla\Registry\Registry;

defined('_JEXEC') or die();

/**
 * Class MailChimp
 *
 * @package Alledia\OSDownloads\Free\MailingList
 */
class MailChimp extends AbstractClient
{
    /**
     * @var EmailTableFree
     */
    protected $table = null;

    /**
     * @var Registry
     */
    protected static $params = null;

    /**
     * @var \Mailchimp\Mailchimp
     */
    protected static $apiManager = null;

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

    /**
     * @param $result
     *
     * @throws Exception
     */
    public function onAfterStore($result)
    {
        $this->addToList();
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function addToList()
    {
        $app    = Factory::getApplication();
        $email  = empty($this->table->email) ? null : $this->table->email;
        $mc     = static::getMailChimp();
        $listId = static::getParams()->get("mailinglist.mailchimp.list_id", 0);

        if ($email && $mc && $listId) {
            // Check if the email already exists
            try {
                $result = $mc->get("lists/{$listId}/members/" . md5(strtolower($email)));
                $app->enqueueMessage('<pre>' . print_r($result, 1) . '</pre>');
                $result = $result->toArray();

            } catch (Exception $e) {
                $result = array('status' => 'unsubscribed');
            }

            if ($result['status'] === 'unsubscribed') {
                // The email is not subscribed. Let's subscribe it.
                $mc->post("lists/{$listId}/members/", array(
                    'email_address' => $email,
                    'status'        => 'subscribed'
                ));
            }
        }
    }

    /**
     * @return \Mailchimp\Mailchimp
     */
    public static function getMailChimp()
    {
        if (static::$apiManager === null) {
            static::$apiManager = false;
            try {
                $params  = static::getParams();
                $enabled = $params->get('mailinglist.mailchimp.enable');
                $apiKey  = $params->get("mailinglist.mailchimp.api", 0);
                if ($enabled && $apiKey) {
                    static::$apiManager = new \Mailchimp\Mailchimp($apiKey);
                }

            } catch (Exception $e) {
                // Just ignore this
            }
        }

        return static::$apiManager ?: null;
    }

    /**
     * @return Registry
     * @throws Exception
     */
    protected static function getParams()
    {
        if (static::$params === null) {
            static::$params = Factory::getApplication()->getParams('com_osdownloads');
        }

        return static::$params;
    }
}
