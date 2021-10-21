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

namespace Alledia\OSDownloads\Free\Joomla\Table;

defined('_JEXEC') or die();

use Alledia\Framework\Joomla\Table\Base;
use Alledia\OSDownloads\Factory;
use JDatabaseDriver;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;

class Item extends Base
{
    /**
     * @inheritdoc
     */
    protected $_columnAlias = [
        'title' => 'name',
        'catid' => 'cate_id'
    ];

    /**
     * @inheritDoc
     */
    public function __construct(JDatabaseDriver $_db)
    {
        parent::__construct('#__osdownloads_documents', 'id', $_db);
    }

    public function store($updateNulls = false)
    {
        $date = Factory::getDate();
        $user = Factory::getUser();

        $this->modified_time = $date->toSql();

        $isNew = empty($this->id);
        if ($isNew) {
            // New document
            $this->downloaded      = 0;
            $this->created_time    = $date->toSql();
            $this->created_user_id = $user->get('id');

        } else {
            $this->modified_user_id = $user->get('id');
        }

        if (empty($this->alias)) {
            $this->alias = $this->get('name');
        }
        $this->alias = ApplicationHelper::stringURLSafe($this->alias);

        if ($this->params instanceof Registry) {
            $params = $this->params->toString();

        } elseif (!is_string($this->params)) {
            $params = new Registry($this->params);
            $params = $params->toString();
        }
        $this->params = $params ?? null;

        $result = $this->trigger('onOSDownloadsBeforeSaveFile', [&$this, $isNew]);
        if (!in_array(false, $result, true)) {
            $result = parent::store($updateNulls);

            $this->trigger('onOSDownloadsAfterSaveFile', [$result, &$this]);

            return true;
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function delete($pk = null)
    {
        $result = $this->trigger('onOSDownloadsBeforeDeleteFile', [&$this, $pk]);
        if (in_array(false, $result, true) == false) {
            $result = parent::delete($pk['id']);

            $this->trigger('onOSDownloadsAfterDeleteFile', [$result, $this->get('id'), $pk]);

            return true;
        }

        return false;
    }

    /**
     * @param string $event
     * @param array  $arguments
     *
     * @return array
     */
    protected function trigger(string $event, array $arguments): array
    {
        try {
            PluginHelper::importPlugin('osdownloads');

            return Factory::getApplication()->triggerEvent($event, $arguments);

        } catch (\Throwable $error) {
            return [];
        }
    }

    /**
     * @inheritDoc
     */
    public function load($keys = null, $reset = true)
    {
        if (parent::load($keys, $reset)) {
            $this->params = new Registry($this->get('params'));

            return true;
        }

        return false;
    }
}
