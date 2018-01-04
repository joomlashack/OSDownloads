<?php
/**
 * @package     OSDownloads
 * @subpackage  com_osdownloads
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Contact Component Category Tree
 *
 * @since  1.6
 */
class OsdownloadsCategories extends JCategories
{
	/**
	 * Class constructor
	 *
	 * @param   array  $options  Array of options
	 *
	 * @since   1.6
	 */
	public function __construct($options = array())
	{
		$options['table']      = '#__osdownloads_documents';
		$options['extension']  = 'com_osdownloads';
		$options['statefield'] = 'published';

		parent::__construct($options);
	}
}
