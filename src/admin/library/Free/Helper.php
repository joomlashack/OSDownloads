<?php
/**
 * @package   com_osdownloads
 * @contact   www.alledia.com, hello@alledia.com
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
}
