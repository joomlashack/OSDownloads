<?php
/**
 * @package   OSDownloads
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2014 Alledia.com, All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

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
    public $file_url;
    public $downloaded;
    public $published;
    public $ordering;
    public $external_ref;
    public $access;

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

        return parent::store($updateNulls);
    }
}
