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

namespace Alledia\OSDownloads\Free\Joomla\Table;

use Alledia\Framework\Joomla\AbstractTable;
use Alledia\OSDownloads\Factory;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die();
// phpcs:enable PSR1.Files.SideEffects
// phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore

/**
 * @property string $typeAlias
 * @property int    $id
 * @property int    $cate_id
 * @property string $name
 * @property string $alias
 * @property string $brief
 * @property string $description_1
 * @property string $description_2
 * @property string $description_3
 * @property int    $require_email
 * @property int    $require_agree
 * @property string $download_text
 * @property string $download_color
 * @property string $documentation_link
 * @property string $demo_link
 * @property string $support_link
 * @property string $other_name
 * @property string $other_link
 * @property string $file_path
 * @property string $file_url
 * @property int    $downloaded
 * @property string $direct_page
 * @property int    $published
 * @property int    $ordering
 * @property string $external_ref
 * @property int    $access
 * @property int    $created_user_id
 * @property string $created_time
 * @property int    $modified_user_id
 * @property string $modified_time
 * @property int    $agreement_article_id
 * @property string $publish_up
 * @property string $publish_down
 * @property string $params
 */
class Item extends AbstractTable
{
    /**
     * @inheritdoc
     */
    protected $_columnAlias = [
        'title' => 'name',
        'catid' => 'cate_id',
    ];

    /**
     * @inheritDoc
     */
    public function __construct(&$_db)
    {
        parent::__construct('#__osdownloads_documents', 'id', $_db);
    }

    /**
     * @inheritDoc
     */
    public function store($updateNulls = true)
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

        // check publishing dates
        $this->publish_up   = $this->get('publish_up') ?: null;
        $this->publish_down = $this->get('publish_down') ?: null;

        if ($this->publish_down && $this->publish_up && $this->publish_down < $this->publish_up) {
            $this->setError(Text::_('JGLOBAL_START_PUBLISH_AFTER_FINISH'));

            return false;
        }

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
