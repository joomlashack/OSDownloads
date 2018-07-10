<?php
/**
 * @package    OSDownloads
 * @contact    www.joomlashack.com, help@joomlashack.com
 * @copyright  2016-2018 Open Source Training, LLC. All rights reserved
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
use Alledia\OSDownloads\MailingLists\AbstractClient;
use Exception;
use JObservableInterface;
use JObserverInterface;

defined('_JEXEC') or die();

/**
 * Class MailChimp
 *
 * @package Alledia\OSDownloads\Free\MailingList
 */
class MailChimp extends AbstractClient
{
    /**
     * @var \Mailchimp\Mailchimp
     */
    protected static $apiManager = null;

    /**
     * @param $result
     *
     * @return void
     */
    public function onAfterStore($result)
    {
        if ($result && static::isEnabled()) {
            try {
                $email  = empty($this->table->email) ? null : $this->table->email;
                $mc     = static::getMailChimp();
                $listId = static::getParams()->get("mailinglist.mailchimp.list_id", 0);

                if ($email && $mc && $listId) {
                    // Check if the email already exists
                    try {
                        $result = $mc->get("lists/{$listId}/members/" . md5(strtolower($email)));
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

            } catch (Exception $e) {
                \JLog::addLogger(array('text_file' => 'osdownloads.log.php'));
                \JLog::add($e->getMessage(), \JLog::ALERT, 'mailchimp-api');
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
                $apiKey  = $params->get("mailinglist.mailchimp.api", 0);
                if ($apiKey) {
                    static::$apiManager = new \Mailchimp\Mailchimp($apiKey);
                }

            } catch (Exception $e) {
                // Just ignore this
            }
        }

        return static::$apiManager ?: null;
    }

    public static function isEnabled()
    {
        return static::getParams()->get('mailinglist.mailchimp.enable');
    }
}
