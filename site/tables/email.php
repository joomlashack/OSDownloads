<?php
/**
 * @version 1.0.0
 * @author Open Source Training (www.ostraining.com)
 * @copyright (C) 2011- Open Source Training
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class TableEmail extends JTable
{
	var $id;
	var $email;
	var $document_id;
	var $downloaded_date;

	function __construct( &$_db )
	{
		parent::__construct( '#__osdownloads_emails', 'id', $_db );
	}

}
?>
