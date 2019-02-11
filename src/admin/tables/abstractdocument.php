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

class OSDownloadsTableAbstractDocument extends OSDownloadsTableAbstract
{
    public function __construct(&$_db)
    {
        parent::__construct('#__osdownloads_documents', 'id', $_db);
    }

    public function store($updateNulls = false)
    {
        $isNew = false;
        $date  = JFactory::getDate();
        $user  = JFactory::getUser();

        $this->modified_time = $date->toSql();

        if (isset($this->id) && !empty($this->id)) {
            // Existing document
            $this->modified_user_id = $user->get('id');
        } else {
            // New document
            $this->downloaded      = 0;
            $this->created_time    = $date->toSql();
            $this->created_user_id = $user->get('id');
            $isNew = true;
        }


        if (isset($this->alias) && isset($this->name) && $this->alias == "") {
            $this->alias = preg_replace("/ /", "-", strtolower($this->name));
        }

        $this->alias = JApplicationHelper::stringURLSafe($this->alias);

        // Trigger events to osdownloads plugins
        $dispatcher = $this->getDispatcher();
        $pluginResults = $dispatcher->trigger('onOSDownloadsBeforeSaveFile', array(&$this, $isNew));

        $result = false;
        if ($pluginResults !== false) {
            $result = parent::store($updateNulls);

            $dispatcher->trigger('onOSDownloadsAfterSaveFile', array($result, &$this));
        }

        return $result;
    }

    public function delete($pk = null)
    {
        // Trigger events to osdownloads plugins
        $dispatcher = $this->getDispatcher();
        $pluginResults = $dispatcher->trigger('onOSDownloadsBeforeDeleteFile', array(&$this, $pk));

        $result = false;
        if ($pluginResults !== false) {
            $result = parent::delete($pk['id']);

            $dispatcher->trigger('onOSDownloadsAfterDeleteFile', array($result, $this->id, $pk));
        }

        return $result;
    }
}
