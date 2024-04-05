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
use MailchimpMarketing\ApiClient;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die();

// phpcs:enable PSR1.Files.SideEffects

class MailChimp extends AbstractClient
{
    /**
     * @var ApiClient
     */
    protected static $apiManager = null;

    /**
     * @inheritDoc
     */
    protected function registerObservers(): void
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
    public function onAfterStore(bool $result, Table $table): void
    {
        if ($result && static::isEnabled()) {
            $email  = $table->email ?? null;
            $listId = static::getParams()->get('mailinglist.mailchimp.list_id', 0);

            if ($email && $listId) {
                try {
                    $mc     = static::getMailChimp();
                    $member = static::getMember($email, $listId);

                    if ($member == false) {
                        $mc->lists->addListMember($listId, [
                            'email_address' => $email,
                            'status'        => 'subscribed',
                        ]);

                    } elseif ($member->status != 'subscribed') {
                        $mc->lists->updateListMember($listId, $email, [
                            'status' => 'subscribed',
                        ]);
                    }

                } catch (\Throwable $error) {
                    $this->logError($error->getMessage());
                }
            }
        }
    }

    /**
     * @param string  $email
     * @param ?string $listId
     *
     * @return ?object
     */
    public static function getMember(string $email, ?string $listId = null): ?object
    {
        $mc = static::getMailChimp();

        $response = $mc->searchMembers->search($email, null, null, $listId);
        $members  = $response->exact_matches->members ?? [];

        return array_shift($members) ?: null;
    }

    /**
     * @param Event $event
     *
     * @return void
     */
    public function onTableAfterStore(Event $event): void
    {
        if ($event->count() == 2) {
            $result = $event->getArgument(0);
            $table  = $event->getArgument(1);

            $this->onAfterStore($result, $table);
        }
    }

    /**
     * @return ApiClient
     */
    public static function getMailChimp(): ApiClient
    {
        if (static::$apiManager === null) {
            static::$apiManager = new ApiClient();

            $params = static::getParams();
            $api    = $params->get('mailinglist.mailchimp.api');
            if ($api && str_contains($api, '-')) {
                [$apiKey, $server] = explode('-', $api, 2);
                static::$apiManager = new ApiClient();
                static::$apiManager->setConfig([
                    'apiKey' => $apiKey,
                    'server' => $server,
                ]);
            }
        }

        return static::$apiManager;
    }

    /**
     * @inheritDoc
     */
    public static function isEnabled(): bool
    {
        return (bool)static::getParams()->get('mailinglist.mailchimp.enable', false);
    }
}
