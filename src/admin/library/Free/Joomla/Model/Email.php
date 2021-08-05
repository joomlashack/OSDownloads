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

namespace Alledia\OSDownloads\Free\Joomla\Model;

defined('_JEXEC') or die();

use Alledia\Framework\Joomla\Model\Base as BaseModel;
use Alledia\OSDownloads\Factory;
use Alledia\OSDownloads\Free\Joomla\Component\Site as FreeComponentSite;
use Alledia\OSDownloads\Free\Helper\Helper;
use OsdownloadsTableEmail;

class Email extends BaseModel
{
    protected function prepareRow(&$row)
    {
        // Method to allow inheritance
    }

    /**
     * @param string $email
     * @param int    $documentId
     *
     * @return OsdownloadsTableEmail
     */
    public function insert($email, $documentId)
    {
        /**
         * @var FreeComponentSite      $component
         * @var OsdownloadsTableEmail $row
         */
        $component = FreeComponentSite::getInstance();
        $row       = $component->getTable('Email');

        if (Helper::validateEmail($email)) {
            $row->email = $email;

            $row->document_id     = (int)$documentId;
            $row->downloaded_date = Factory::getDate()->toSql();

            $this->prepareRow($row);

            $row->store();

            return $row;
        }

        return null;
    }

    /**
     * @param $emailId
     *
     * @return OsdownloadsTableEmail
     */
    public function getEmail($emailId)
    {
        /** @var OsdownloadsTableEmail $row */
        $component = FreeComponentSite::getInstance();
        $row       = $component->getTable('Email');

        $row->load((int)$emailId);

        return $row;
    }
}
