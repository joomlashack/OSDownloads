<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2017 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

use Alledia\Framework\Factory;
use Joomla\Utilities\ArrayHelper;
use Alledia\OSDownloads\Free\Factory as OSDFactory;

defined('_JEXEC') or die();

jimport('joomla.log.log');

if (!defined('OSDOWNLOADS_LOADED')) {
    require_once JPATH_ADMINISTRATOR . '/components/com_osdownloads/include.php';
}

/**
 * Routing class from com_osdownloads
 */
class OsdownloadsRouter extends JComponentRouterBase
{
    /**
     * Class constructor.
     *
     * @param   JApplicationCms $app  Application-object that the router should use
     * @param   JMenu           $menu Menu-object that the router should use
     *
     * @since   3.4
     */
    public function __construct($app = null, $menu = null)
    {
        parent::__construct($app, $menu);

        JLog::addLogger(
            array('text_file' => 'com_osdownloads.router.errors.php'),
            JLog::ALL,
            array('com_osdownloads.router')
        );
    }

    /**
     * Build the route for the com_content component
     *
     * @param   array &$query An array of URL arguments
     *
     * @return  array  The URL arguments to use to assemble the subsequent URL.
     */
    public function build(&$query)
    {
        $container = OSDFactory::getContainer();

        // Build the segments
        $segments = $container->helperSEF->getRouteSegmentsFromQuery($query);

        // Cleanup the query
        $remove = array('view', 'layout', 'id', 'task', 'tmpl', 'data');
        foreach ($query as $key => $value) {
            if (isset($query[$key])) {
                unset($query[$key]);
            }
        }

        return $segments;
    }

    /**
     * @param string[] $segments
     *
     * @return mixed[]
     */
    public function parse(&$segments)
    {
        $container = OSDFactory::getContainer();

        // Get the query vars
        $vars = $container->helperSEF->getQueryFromRouteSegments($segments);

        return $vars;
    }
}
