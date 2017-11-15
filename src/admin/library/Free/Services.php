<?php
/**
 * @package   com_osdownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2017 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace Alledia\OSDownloads\Free;

use Pimple\Container AS Pimple;
use Pimple\ServiceProviderInterface;

defined('_JEXEC') or die();

/**
 * Class Services
 *
 * Pimple services for OSDownloads.
 *
 * @package OSDownloads
 */
class Services implements ServiceProviderInterface
{
    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Pimple $pimple An Container instance
     */
    public function register(Pimple $pimple)
    {
        // Services
        $pimple['helperRoute'] = function (Container $c) {
            return new Helper\Route;
        };
    }
}
