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

use Joomla\String\StringHelper;

defined('_JEXEC') or die();

class OsdownloadsTableDocument extends JTable
{
    protected $_columnAlias = array(
        'title' => 'name',
        'catid' => 'cate_id'
    );

    /**
     * @var JEventDispatcher
     */
    protected $_dispatcher = null;

    public function __construct(&$_db)
    {
        parent::__construct('#__osdownloads_documents', 'id', $_db);
    }

    public function store($updateNulls = false)
    {
        $date = JFactory::getDate();
        $user = JFactory::getUser();

        $this->modified_time = $date->toSql();

        $isNew = !empty($this->id);
        if ($isNew) {
            // New document
            $this->downloaded      = 0;
            $this->created_time    = $date->toSql();
            $this->created_user_id = $user->get('id');

        } else {
            $this->modified_user_id = $user->get('id');
        }

        if (empty($this->alias)) {
            $this->alias = $this->name;
        }
        $this->alias = JApplicationHelper::stringURLSafe($this->alias);

        $result = $this->trigger('onOSDownloadsBeforeSaveFile', array(&$this, $isNew)) !== false;
        if ($result) {
            $result = parent::store($updateNulls);

            $this->trigger('onOSDownloadsAfterSaveFile', array($result, &$this));
        }

        return $result;
    }

    public function delete($pk = null)
    {
        // Trigger events to osdownloads plugins
        $result = $this->trigger('onOSDownloadsBeforeDeleteFile', array(&$this, $pk)) !== false;
        if ($result) {
            $result = parent::delete($pk['id']);

            $this->trigger('onOSDownloadsAfterDeleteFile', array($result, $this->id, $pk));
        }

        return $result;
    }

    protected function trigger($event, array $arguments)
    {
        if ($this->_dispatcher === null) {
            $this->_dispatcher = JEventDispatcher::getInstance();
            JPluginHelper::importPlugin('osdownloads');
        }

        return $this->_dispatcher->trigger($event, $arguments);
    }
}
