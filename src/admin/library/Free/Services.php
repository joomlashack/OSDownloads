<?php
/**
 * @package   com_osdownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2018 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace Alledia\OSDownloads\Free;

use Alledia\OSDownloads\MailingLists\Manager;
use Pimple\Container as Pimple;
use Pimple\ServiceProviderInterface;
use JFactory;

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
        $pimple['app'] = function (Container $c) {
            return JFactory::getApplication();
        };

        $pimple['db'] = function (Container $c) {
            return JFactory::getDbo();
        };

        $pimple['helperRoute'] = function (Container $c) {
            return new Helper\Route();
        };

        $pimple['helperSEF'] = function (Container $c) {
            return new Helper\SEF();
        };

        $pimple['helperView'] = function (Container $c) {
            return new Helper\View();
        };

        $pimple['mailingLists'] = function (Container $c) {
            return new Manager();
        };
    }
}
