<?php
/**
 * @version       1.0.0
 * @author        Open Source Training (www.ostraining.com)
 * @copyright (C) 2014 Open Source Training
 * @license       GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 **/
// no direct access
defined('_JEXEC') or die('Restricted access');

class OsdownloadsTableDocument extends JTable
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
    public $show_email;
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
    public $downloaded;
    public $direct_page;
    public $published;
    public $ordering;

    public function __construct(&$_db)
    {
        parent::__construct('#__osdownloads_documents', 'id', $_db);
    }

    public function store($updateNulls = false)
    {
        if (!$this->id) {
            $this->downloaded = 0;
        }
        if (isset($this->alias) && isset($this->name) && $this->alias == "") {
            $this->alias = preg_replace("/ /", "-", strtolower($this->name));
        }
        $this->alias = JApplicationHelper::stringURLSafe($this->alias);

        return parent::store($updateNulls);
    }
}
