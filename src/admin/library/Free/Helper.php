<?php
/**
 * @package   com_osdownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace Alledia\OSDownloads\Free;

use stdClass;

defined('_JEXEC') or die();

class Helper
{
    /**
     * Validate an email address. Used this to have the option to accept
     * plus signs in the email validation. The native PHP filter
     * doesn't support + in the email address, which is now allowed.
     *
     * @param string $email
     * @param bool   $acceptPlusSign
     *
     * @return bool
     */
    public static function validateEmail($email, $acceptPlusSign = true)
    {
        if ($acceptPlusSign) {
            $pattern = '/^([a-z0-9_\-\.\+])+\@([a-z0-9_\-\.])+\.([a-z]{2,25})$/i';

            return (bool)preg_match($pattern, $email);
        }

        $valid = filter_var($email, FILTER_VALIDATE_EMAIL);

        return !empty($valid);
    }

    /**
     * Check if the path is a local path and exists. Otherwise it can means
     * we have an external URL.
     *
     * @param string $path
     *
     * @return bool
     */
    public static function isLocalPath($path)
    {
        // Is an external URL or empty path?
        if (empty($path) || preg_match('#(?:^//|[a-z0-9]+?://)#i', $path)) {
            return false;
        }

        // If the file exists, it is a local path
        return  \JFile::exists(realpath(JPATH_SITE . $path));
    }
}
