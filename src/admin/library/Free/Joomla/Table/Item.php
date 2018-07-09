<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2018 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace Alledia\OSDownloads\Free\Joomla\Table;

defined('_JEXEC') or die();

use Alledia\Framework\Joomla\Table\Base as BaseTable;
use JApplicationHelper;
use JFactory;
use Joomla\Registry\Registry;


class Item extends BaseTable
{
    public function __construct(&$db)
    {
        parent::__construct('#__osdownloads_documents', 'id', $db);
    }

    public function load($keys = null, $reset = true)
    {
        if (parent::load($keys, $reset)) {
            if (property_exists($this, 'params')) {
                $this->params = new Registry($this->params);
            }

            return true;
        }

        return false;
    }

    public function store($updateNulls = false)
    {
        $date = JFactory::getDate();
        $user = JFactory::getUser();

        $this->modified_time = $date->toSql();

        if (isset($this->id) && !empty($this->id)) {
            // Existing item
            $this->modified_user_id = $user->get('id');
        } else {
            // New item
            $this->downloaded      = 0;
            $this->created_time    = $date->toSql();
            $this->created_user_id = $user->get('id');
        }

        if (isset($this->alias) && isset($this->name) && $this->alias == "") {
            $this->alias = $this->name;
        }
        $this->alias = JApplicationHelper::stringURLSafe($this->alias);

        if (isset($this->catid)) {
            unset($this->catid);
        }

        if (property_exists($this, 'params')) {
            if (!is_string($this->params)) {
                if ($this->params instanceof Registry) {
                    $params = $this->params->toString();
                } else {
                    $params = new Registry($this->params);
                    $params = $params->toString();
                }
                $this->params = $params;
            }
        }

        return parent::store($updateNulls);
    }
}
