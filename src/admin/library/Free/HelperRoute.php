<?php
/**
 * @package   com_osdownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace Alledia\OSDownloads\Free;

defined('_JEXEC') or die();

/**
 * OSDownloads Component Route Helper.
 */
abstract class HelperRoute
{
	/**
	 * Get the file download route.
	 *
	 * @param   integer  $id        The id of the file.
	 *
	 * @return  string  The file download route.
	 */
	public static function getFileDownloadRoute($id)
	{
		$id = (int) preg_replace('/[^0-9]/', '', $id);

		// Create the link
		$link = 'index.php?option=com_osdownloads&task=download&tmpl=component&id=' . $id;
		
		return $link;
	}

	/**
	 * Get the file route.
	 *
	 * @param   integer  $id        The id of the file.
	 * @param   integer  $itemId    The menu item id
	 *
	 * @return  string  The file route.
	 */
	public static function getFileRoute($id, $itemId = 0)
	{
		$id = (int) preg_replace('/[^0-9]/', '', $id);

		// Create the link
		$link = "index.php?option=com_osdownloads&view=downloads&id={$id}";

		// Should we add the item id?
		if (! empty($itemId)) {
			$itemId = (int) preg_replace('/[^0-9]/', '', $itemId);

			$link .= "&Itemid={$itemId}";
		}

		return $link;
	}

	/**
	 * Get the file list route.
	 *
	 * @param   integer  $id        The id of the file.
	 * @param   integer  $itemId    The menu item id
	 *
	 * @return  string  The file route.
	 */
	public static function getFileListRoute()
	{
		// Create the link
		$link = "index.php?option=com_osdownloads&view=downloads";

		return $link;
	}
}
