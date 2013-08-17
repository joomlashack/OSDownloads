<?php
/**
 * @version 1.0.0
 * @author Open Source Training (www.ostraining.com)
 * @copyright (C) 2011- Open Source Training
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class TableDocument extends JTable
{
	var $id;
	var $cate_id;
	var $documents;
	var $name;
	var $alias;
	var $brief;
	var $description_1;
	var $description_2;
	var $description_3;
	var $show_email;
	var $require_email;
	var $require_agree;
	var $download_text;
	var $download_color;
	var $documentation_link;
	var $demo_link;
	var $support_link;
	var $other_name;
	var $other_link;
	var $file_path;
	var $downloaded;
	var $direct_page;
	var $published;
	var $ordering;

	function __construct( &$_db )
	{
		parent::__construct( '#__osdownloads_documents', 'id', $_db );
	}

	function store()
	{
		if (!$this->id)
		{
			$this->downloaded = 0;
		}
		if (isset($this->alias) && isset($this->name) && $this->alias == "")
		{
			$this->alias = preg_replace("/ /", "-", strtolower($this->name));
		}
		$this->alias = JApplication::stringURLSafe($this->alias);
		parent::store();
	}

}
?>
