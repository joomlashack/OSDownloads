<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2005-2025 Joomlashack.com. All rights reserved
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
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
 * along with OSDownloads.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace Alledia\OSDownloads\Free\Joomla\Model;

use Alledia\Framework\Joomla\Model\AbstractBaseDatabaseModel;
use Alledia\OSDownloads\Factory;
use Alledia\OSDownloads\Free\Helper\Helper;
use Alledia\OSDownloads\Free\Joomla\Component\Site as FreeComponentSite;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Table\Table;
use OsdownloadsTableEmail;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die();

// phpcs:enable PSR1.Files.SideEffects

class Email extends AbstractBaseDatabaseModel
{
    /**
     * @param object $row
     *
     * @return void
     */
    protected function prepareRow(object $row)
    {
        // Method to allow inheritance
    }

    /**
     * @param string $email
     * @param int    $documentId
     *
     * @return ?OsdownloadsTableEmail
     * @throws \Exception
     */
    public function insert(string $email, int $documentId): ?OsdownloadsTableEmail
    {
        /**
         * @var OsdownloadsTableEmail $row
         */
        $row = Table::getInstance('Email', 'OsdownloadsTable');
        if (Helper::validateEmail($email)) {
            $row->email = $email;

            $row->document_id     = $documentId;
            $row->downloaded_date = Factory::getDate()->toSql();

            $this->prepareRow($row);

            $row->store();

            return $row;
        }

        return null;
    }

    /**
     * @param int $emailId
     *
     * @return OsdownloadsTableEmail
     */
    public function getEmail(int $emailId): OsdownloadsTableEmail
    {
        /** @var OsdownloadsTableEmail $row */
        $component = FreeComponentSite::getInstance();
        $row       = $component->getTable('Email');

        $row->load($emailId);

        return $row;
    }
}
