<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2017 Open Source Training, LLC. All rights reserved
 * @license   GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

use Alledia\OSDownloads\Free\Helper\Helper;

defined('_JEXEC') or die;

/**
 * Backward compatibility for the helper. We moved it to improve inheritance between
 * Free and Pro versions. Some plugins still call this class.
 *
 * @deprecated 1.8.0  Use the Alledia\OSDownloads\Free\Helper\Helper class instead.
 */
class OSDownloadsHelper extends Helper;
{

}
