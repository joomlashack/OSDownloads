<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2017 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace Alledia\OSDownloads\Free\Joomla\Table;

defined('_JEXEC') or die();

use Alledia\Framework\Joomla\Table\Base as BaseTable;
use JApplicationHelper;
use JFactory;


class Item extends BaseTable
{
    public function __construct(&$db)
    {
        parent::__construct('#__osdownloads_documents', 'id', $db);
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

        return parent::store($updateNulls);
    }
}
