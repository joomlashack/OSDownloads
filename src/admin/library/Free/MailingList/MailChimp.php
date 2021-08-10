<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2005-2021 Joomlashack.com. All rights reserved
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
use Exception;
use JEventDispatcher;
use Joomla\CMS\Table\Table;

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

    protected function registerObservers()
    {
        $dispatcher = Factory::getDispatcher();

        if ($dispatcher instanceof JEventDispatcher) {
            $dispatcher->register('onOSDownloadsAfterSaveEmail', [$this, 'onAfterStore']);
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
                $mc     = $this->getMailChimp();
                $listId = $this->getParams()->get('mailinglist.mailchimp.list_id', 0);

                if ($email && $mc && $listId) {
                    // Check if the email already exists
                    try {
                        $member = $mc->get("lists/{$listId}/members/" . md5(strtolower($email)));
                        $member = $member->toArray();

                    } catch (Exception $e) {
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

            } catch (Exception $e) {
                $this->logError($e->getMessage());
            }
        }
    }

    /**
     * @return ?\Mailchimp\Mailchimp
     */
    public function getMailChimp(): ?\Mailchimp\Mailchimp
    {
        if (static::$apiManager === null) {
            static::$apiManager = false;
            try {
                $params = $this->getParams();
                $apiKey = $params->get('mailinglist.mailchimp.api', 0);
                if ($apiKey) {
                    static::$apiManager = new \Mailchimp\Mailchimp($apiKey);
                }

            } catch (Exception $e) {
                // Just ignore this
            }
        }

        return static::$apiManager ?: null;
    }

    /**
     * @inheritDoc
     */
    public function isEnabled(): bool
    {
        return $this->getParams()->get('mailinglist.mailchimp.enable');
    }
}
