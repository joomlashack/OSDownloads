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
use Joomla\CMS\Component\Router\RouterBase;

defined('_JEXEC') or die();

jimport('joomla.log.log');

if (!defined('OSDOWNLOADS_LOADED')) {
    require_once JPATH_ADMINISTRATOR . '/components/com_osdownloads/include.php';
}

/**
 * Routing class from com_osdownloads
 */
class OsdownloadsRouter extends RouterBase
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
        $segments  = array();
        $container = OSDFactory::getContainer();

        $itemId = ArrayHelper::getValue($query, 'Itemid');
        $id     = ArrayHelper::getValue($query, 'id');
        $view   = ArrayHelper::getValue($query, 'view');
        $layout = ArrayHelper::getValue($query, 'layout');
        $task   = ArrayHelper::getValue($query, 'task');
        $data   = ArrayHelper::getValue($query, 'data');

        /*----------  Subsection comment block  ----------*/

        if (isset($query['task'])) {
            unset($query['task']);

            switch ($task) {
                case 'routedownload':
                case 'download':
                    if ($layout !== 'thankyou') {
                        $segments[] = $task;
                    } else {
                        $segments[] = 'thankyou';
                    }

                    // Append the categories before the alias of the file
                    $catId = $container->helperSEF->getCategoryIdFromFile($id);

                    $container->helperSEF->appendCategoriesToSegments($segments, $catId);

                    $segments[] = $container->helperSEF->getFileAlias($id);

                    break;

                case 'confirmemail':
                    $segments[] = 'confirmemail';
                    $segments[] = $data;

                    break;
            }

            if (isset($query['tmpl'])) {
                unset($query['tmpl']);
            }

            if (isset($query['id'])) {
                unset($query['id']);
            }

            return $segments;
        }

        // If there is no recognized tasks, try to get the view
        if (isset($query['view'])) {
            unset($query['view']);

            if (isset($query['id'])) {
                unset($query['id']);
            }

           switch ($view) {
                case 'downloads':
                    $segments[] = 'downloads';
                    $container->helperSEF->appendCategoriesToSegments($segments, $id);

                    break;

                case 'categories':
                    $segments[] = 'categories';
                    $container->helperSEF->appendCategoriesToSegments($segments, $id);

                    break;

                case 'item':
                    // Task segments
                    $segments[] = "item";

                    $catId = $container->helperSEF->getCategoryIdFromFile($id);
                    if (!empty($catId)) {
                        $container->helperSEF->appendCategoriesToSegments($segments, $catId);
                    }

                    // Append the file alias
                    $segments[] = $container->helperSEF->getFileAlias($id);

                    break;
            }
        }

        // Remove tmp=component
        if (isset($query['tmpl']) && 'component' === $query['tmpl']) {
            unset($query['tmpl']);
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
        $vars      = array();
        $container = OSDFactory::getContainer();

        $firstSegment = array_shift($segments);

        switch ($firstSegment) {
            case 'routedownload':
            case 'download':
                $vars['task'] = $firstSegment;

                if ('thankyou' === reset($segments)) {
                    array_shift($segments);
                    $vars['layout'] = 'thankyou';
                }

                // File id
                $id = $container->helperSEF->getFileIdFromAlias(last($segments));

                $vars['tmpl'] = 'component';

                break;

            case 'confirmemail':
                $vars['task'] = 'confirmemail';
                $vars['data'] = $data;
                $vars['tmpl'] = 'component';

                break;


            case 'downloads':
                $vars['view'] = 'downloads';

                // Category id
                $category = $container->helperSEF->getCategoryFromAlias(last($segments));
                $vars['id'] = $category->id;

                break;

            case 'categories':
                $vars['view'] = 'categories';

                // Category id
                $category = $container->helperSEF->getCategoryFromAlias(last($segments));
                $vars['id'] = $category->id;

                break;

            case 'item':
                $vars['view'] = 'item';

                // File id
                $vars['id'] = $container->helperSEF->getFileIdFromAlias(last($segments));

                break;
        }

        return $vars;
    }
}
