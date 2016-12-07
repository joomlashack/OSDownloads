<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace Alledia\OSDownloads\Free\Joomla\Table;

defined('_JEXEC') or die();

use Alledia\Framework\Joomla\Table\Base as BaseTable;
use JApplicationHelper;
use JFactory;


class Item extends BaseTable
{
    public $id;
    public $cate_id;
    public $documents;
    public $name;
    public $alias;
    public $brief;
    public $description_1;
    public $description_2;
    public $description_3;
    public $require_email;
    public $require_agree;
    public $download_text;
    public $download_color;
    public $documentation_link;
    public $demo_link;
    public $support_link;
    public $other_name;
    public $other_link;
    public $file_path;
    public $file_url;
    public $downloaded;
    public $direct_page;
    public $published = true;
    public $ordering;
    public $external_ref;
    public $access;
    public $agreement_article_id;
    public $created_user_id;
    public $created_time;
    public $modified_user_id;
    public $modified_time;

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
