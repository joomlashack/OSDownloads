<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2005-2019 Joomlashack.com. All rights reserved
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

defined('_JEXEC') or die;

require_once __DIR__ . '/abstract.php';

class OsDownloadsTableEmail extends OSDownloadsTableAbstract
{
    public $id;
    public $email;
    public $document_id;
    public $downloaded_date;

    public function __construct(&$_db)
    {
        parent::__construct('#__osdownloads_emails', 'id', $_db);
    }

    public function store($updateNulls = false)
    {
        // Trigger events to osdownloads plugins
        $dispatcher = $this->getDispatcher();
        $pluginResults = $dispatcher->trigger('onOSDownloadsBeforeSaveEmail', array(&$this));

        $result = false;
        if ($pluginResults !== false) {
            $result = parent::store($updateNulls);

            $dispatcher->trigger('onOSDownloadsAfterSaveEmail', array($result, &$this));
        }

        return $result;
    }

    public function delete($pk = null)
    {
        $id = $this->id;

        // Trigger events to osdownloads plugins
        $dispatcher = $this->getDispatcher();
        $pluginResults = $dispatcher->trigger('onOSDownloadsBeforeDeleteEmail', array(&$this, $pk));

        $result = false;
        if ($pluginResults !== false) {
            $result = parent::delete($pk);

            $dispatcher->trigger('onOSDownloadsAfterDeleteEmail', array($result, $this->id, $pk));
        }

        return $result;
    }
}
