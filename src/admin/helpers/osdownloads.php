<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2018 Open Source Training, LLC. All rights reserved
 * @license   GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

use Alledia\OSDownloads\Free;
use Alledia\OSDownloads\Pro;

defined('_JEXEC') or die;

/**
 * Backward compatibility for the helper. We moved it to improve inheritance between
 * Free and Pro versions. Some plugins still call this class, including com_files.
 *
 */

if (!defined('OSDOWNLOADS_LOADED')) {
    require_once dirname(__DIR__) . '/include.php';
}

if (class_exists('\\Alledia\\OSDownloads\\Pro\\Helper\\Helper')) {
    class OSDownloadsHelper extends Pro\Helper\Helper
    {

    }
} else {
    class OSDownloadsHelper extends Free\Helper\Helper
    {

    }
}
