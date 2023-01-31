<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2005-2023 Joomlashack.com. All rights reserved
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

namespace Alledia\OSDownloads\Free\MailingList;

use Alledia\OSDownloads\Factory;
use Alledia\OSDownloads\MailingLists\AbstractClient;
use Joomla\CMS\Table\Table;
use Joomla\Event\Dispatcher;
use Joomla\Event\Event;

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
     * @inheritDoc
     */
    protected function registerObservers()
    {
        $dispatcher = Factory::getDispatcher();

        if ($dispatcher instanceof \JEventDispatcher) {
            $dispatcher->register('onOSDownloadsAfterSaveEmail', [$this, 'onAfterStore']);

        } elseif ($dispatcher instanceof Dispatcher) {
            $dispatcher->addListener('onOSDownloadsAfterSaveEmail', [$this, 'onTableAfterStore']);
        }
    }

    /**
     * @param bool  $result
     * @param Table $table
     *
     * @return void
     */
    public function onAfterStore(bool $result, Table $table)
    {
        if ($result && static::isEnabled()) {
            try {
                $email  = $table->email ?? null;
                $mc     = static::getMailChimp();
                $listId = static::getParams()->get('mailinglist.mailchimp.list_id', 0);

                if ($email && $mc && $listId) {
                    // Check if the email already exists
                    try {
                        $member = $mc->get("lists/{$listId}/members/" . md5(strtolower($email)));
                        $member = $member->toArray();

                    } catch (\Exception $e) {
                        $member = ['status' => 'unsubscribed'];
                    }

                    if ($member['status'] !== 'subscribed') {
                        // The email is not subscribed. Let's subscribe it.
                        $mc->post("lists/{$listId}/members/", [
                            'email_address' => $email,
                            'status'        => 'subscribed'
                        ]);
                    }
                }

            } catch (\Throwable $e) {
                $this->logError($e->getMessage());
            }
        }
    }

    /**
     * @param Event $event
     */
    public function onTableAfterStore(Event $event)
    {
        if ($event->count() == 2) {
            $result = $event->getArgument(0);
            $table  = $event->getArgument(1);

            $this->onAfterStore($result, $table);
        }
    }

    /**
     * @return ?\Mailchimp\Mailchimp
     */
    public static function getMailChimp(): ?\Mailchimp\Mailchimp
    {
        if (static::$apiManager === null) {
            static::$apiManager = false;
            try {
                $params = static::getParams();
                $apiKey = $params->get('mailinglist.mailchimp.api', 0);
                if ($apiKey) {
                    static::$apiManager = new \Mailchimp\Mailchimp($apiKey);
                }

            } catch (\Throwable $e) {
                // Just ignore this
            }
        }

        return static::$apiManager ?: null;
    }

    /**
     * @inheritDoc
     */
    public static function isEnabled(): bool
    {
        return (bool)static::getParams()->get('mailinglist.mailchimp.enable', false);
    }
}
