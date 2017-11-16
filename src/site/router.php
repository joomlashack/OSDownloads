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

        $view   = ArrayHelper::getValue($query, 'view');
        $layout = ArrayHelper::getValue($query, 'layout');
        $id     = ArrayHelper::getValue($query, 'id');
        $task   = ArrayHelper::getValue($query, 'task');

        unset(
            $query['view'],
            $query['layout'],
            $query['id'],
            $query['task'],
            $query['tmpl']
        );

        $segments = array();
        if ($task) {
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
                    $segments[] = ArrayHelper::getValue($query, 'data');

                    unset($query['data']);
                    break;
            }

        } else {
            switch ($view) {
                case 'downloads':
                    $segments[] = 'category';

                    $container->helperSEF->appendCategoriesToSegments($segments, $id);
                    break;

                case 'item':
                    if ($layout === 'thankyou') {
                        $segments[] = "thankyou";
                    } else {
                        $segments[] = "file";
                    }

                    $catId = $container->helperSEF->getCategoryIdFromFile($id);

                    if (!empty($catId)) {
                        $container->helperSEF->appendCategoriesToSegments($segments, $catId);
                    }

                    // Append the file alias
                    $segments[] = $container->helperSEF->getFileAlias($id);
                    break;
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
        
        $data     = $segments;
        $viewTask = array_shift($data);

        $vars = array();
        switch ($viewTask) {
            case 'category':
                $vars['view'] = 'downloads';

                if ($data) {
                    // Get category Id from category alias
                    $category = $container->helperSEF->getCategoryFromAlias($data);

                    if (!empty($category)) {
                        $vars['id'] = $category->id;
                    }
                }
                break;

            case 'file':
                $vars['view'] = 'item';
                $vars['id']   = $container->helperSEF->getFileIdFromAlias(array_pop($data));
                break;

            case 'thankyou':
                $vars['view']   = 'item';
                $vars['layout'] = 'thankyou';
                $vars['tmpl']   = 'component';
                $vars['task']   = 'routedownload';
                $vars['id']     = $container->helperSEF->getFileIdFromAlias(array_pop($data));
                break;

            case 'download':
                $vars['task'] = 'download';
                $vars['tmpl'] = 'component';
                $vars['id']   = $container->helperSEF->getFileIdFromAlias(array_pop($data));
                break;

            case 'routedownload':
                $vars['task'] = 'routedownload';
                $vars['tmpl'] = 'component';
                $vars['id']   = $container->helperSEF->getFileIdFromAlias(array_pop($data));
                break;

            case 'confirmemail':
                $vars['task'] = 'confirmemail';
                $vars['data'] = array_pop($data);
                break;
        }

        return $vars;
    }
}
