<?php
/**
 * @package   com_osdownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2017 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace Alledia\OSDownloads\Free;

defined('_JEXEC') or die();

/**
 * Class Factory
 *
 * @package OSDownloads
 */
abstract class Factory extends \JFactory
{
    /**
     * @var Container
     */
    protected static $container;

    /**
     * Get the current container instance. Creates if not set yet.
     *
     * @return Container
     */
    public static function getContainer()
    {
        if (!empty(static::$container)) {
            return static::$container;
        }

        // Instantiate the container and services
        $container = new Container;
        $container->register(new Services);

        static::$container = $container;

        return static::$container;
    }
}
