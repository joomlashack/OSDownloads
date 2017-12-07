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
    protected $container;

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

        $this->setContainer();
    }

    /**
     * Set the container to the class
     */
    public function setContainer()
    {
        $this->container = OSDFactory::getContainer();
    }

    /**
     * Check if the category and path are correct. If category is empty, or we
     * have a wrong path, trigger a 404 error.
     *
     * @param  stdClass $category
     * @param  array    $segments
     */
    protected function checkCategoryAndPath($category, $segments)
    {
        // Check if the category was foud
        if (empty($category)) {
            JError::raiseError(404, JText::_('COM_OSDOWNLOADS_NOT_FOUND'));
        }

        // Check if the path is correct
        $path = implode('/', $segments);
        if ($path !== $category->path) {
            JError::raiseError(404, JText::_('COM_OSDOWNLOADS_NOT_FOUND'));
        }
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

        $id     = ArrayHelper::getValue($query, 'id');
        $view   = ArrayHelper::getValue($query, 'view');
        $layout = ArrayHelper::getValue($query, 'layout');
        $task   = ArrayHelper::getValue($query, 'task');
        $data   = ArrayHelper::getValue($query, 'data');

        unset($query['view']);
        unset($query['layout']);
        unset($query['id']);
        unset($query['task']);
        unset($query['tmpl']);

        /*----------  Subsection comment block  ----------*/

        if (!empty($task)) {
            switch ($task) {
                case 'routedownload':
                case 'download':
                    if ($layout !== 'thankyou') {
                        $segments[] = $task;
                    } else {
                        $segments[] = 'thankyou';
                    }

                    // Append the categories before the alias of the file
                    $catId = $this->container->helperSEF->getCategoryIdFromFileId($id);

                    $this->container->helperSEF->appendCategoriesToSegments($segments, $catId);

                    $segments[] = $this->container->helperSEF->getFileAlias($id);

                    break;

                case 'confirmemail':
                    $segments[] = 'confirmemail';
                    $segments[] = $data;

                    break;
            }

            return $segments;
        }

        // If there is no recognized tasks, try to get the view
        if (!empty($view)) {
            switch ($view) {
                /**

                    TODO:
                    - Filter this for the Pro version only

                 */

                /**
                 *
                 * List of categories
                 *
                 */
                case 'categories':
                    $this->container->helperSEF->appendCategoriesToSegments($segments, $id);

                    break;

                /**
                 *
                 * List of files
                 *
                 */
                case 'downloads':
                    $this->container->helperSEF->appendCategoriesToSegments($segments, $id);
                    $segments[] = 'files';

                    break;

                /**
                 *
                 * A single file
                 *
                 */
                case 'item':
                    $catId = $this->container->helperSEF->getCategoryIdFromFileId($id);
                    if (!empty($catId)) {
                        $this->container->helperSEF->appendCategoriesToSegments($segments, $catId);
                    }

                    $segments[] = "files";

                    // Append the file alias
                    $segments[] = $this->container->helperSEF->getFileAlias($id);

                    // Check if the thankyou layout was requested
                    if ('thankyou' === $layout) {
                        $segments[] = 'thankyou';
                    }

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
        $vars = array();

        if (!empty($segments)) {
            switch ($segments[0]) {
                /**
                 *
                 * Tasks
                 *
                 */
                case 'routedownload':
                case 'download':
                    $vars['task'] = array_shift($segments);

                    if ('thankyou' === reset($segments)) {
                        array_shift($segments);

                        $vars['layout'] = 'thankyou';
                    }

                    $alias = array_pop($segments);

                    $id = $this->container->helperSEF->getFileIdFromAlias($alias);

                    // Check if the file exists
                    if (empty($id)) {
                        JError::raiseError(404, JText::_('COM_OSDOWNLOADS_NOT_FOUND'));
                    }

                    $category = $this->container->helperSEF->getCategoryFromFileId($id);

                    $this->checkCategoryAndPath($category, $segments);

                    // File id
                    $vars['id'] = $id;

                    $vars['tmpl'] = 'component';

                    break;

                case 'confirmemail':
                    $vars['task'] = 'confirmemail';
                    $vars['data'] = end($segments);
                    $vars['tmpl'] = 'component';

                    break;

                default:
                    /**
                     *
                     * Thank you page?
                     *
                     * The thank you page is identified by the last segment and
                     * relates to the view "item".
                     */
                    if ('thankyou' === end($segments)) {
                        // Get the index of the second to last segment
                        $indexSecToLast = count($segments) - 2;

                        // If exists, we check if it is a valid file alias
                        if (isset($segments[$indexSecToLast])) {
                            // Look for a file with the given alias
                            $id = $this->container->helperSEF->getFileIdFromAlias($segments[$indexSecToLast]);

                            if (empty($id)) {
                                JError::raiseError(404, JText::_('COM_OSDOWNLOADS_NOT_FOUND'));
                            }

                            $segments = array_splice($segments, 0, -3);

                            // Check the category path
                            $category = $this->container->helperSEF->getCategoryFromFileId($id);

                            $this->checkCategoryAndPath($category, $segments);

                            $vars['view']   = 'item';
                            $vars['tmpl']   = 'component';
                            $vars['layout'] = 'thankyou';
                            $vars['id']     = $id;

                            break;
                        }
                    }

                    /**
                     *
                     * A single file
                     *
                     * Single files have the file alias as the last segment in the route.
                     * The second to last is "files". We check it, if compatible we check
                     * the last one looking for a file with the respective alias.
                     *
                     */
                    // Get the index of the second to last segment
                    $indexSecToLast = count($segments) - 2;

                    // If exists, we check if it is the "files" segment
                    if (isset($segments[$indexSecToLast])) {
                        if ('files' === $segments[$indexSecToLast]) {
                            // Look for a file with the given alias
                            $id = $this->container->helperSEF->getFileIdFromAlias(end($segments));

                            $segments = array_splice($segments, 0, -2);

                            // Check if the file exists
                            if (empty($id)) {
                                JError::raiseError(404, JText::_('COM_OSDOWNLOADS_NOT_FOUND'));
                            }

                            $category = $this->container->helperSEF->getCategoryFromFileId($id);

                            $this->checkCategoryAndPath($category, $segments);

                            $vars['view'] = 'item';
                            $vars['id']   = $id;

                            break;
                        }
                    }

                    /**
                     *
                     * A list of files
                     *
                     * The list of files has the last segment equals to "files".
                     */
                    if ('files' === end($segments)) {
                        $vars['view'] = 'downloads';

                        // Get the category id
                        array_pop($segments);

                        // If there is no more segments, we should display the root category
                        if (empty($segments)) {
                            $vars['id'] = 0;

                            break;
                        }

                        $category = $this->container->helperSEF->getCategoryFromAlias(end($segments));

                        $this->checkCategoryAndPath($category, $segments);

                        // It is ok, return the id
                        $vars['id'] = $category->id;

                        break;
                    }

                    /**

                        TODO:
                        - Filter this for the Pro version only

                     */


                    /**
                     *
                     * A list of categories
                     *
                     * The list of categories is identified by segments representing the nested categories.
                     * It is the default view as well, if there is no segments.
                     */
                    $vars['view'] = 'categories';

                    if (empty($segments) || empty($segments[0])) {
                        $vars['id'] = 0;

                        break;
                    }

                    // Try to locate the repective category based on the alias
                    $category = $this->container->helperSEF->getCategoryFromAlias(end($segments));

                    $this->checkCategoryAndPath($category, $segments);

                    // It is ok, return the id
                    $vars['id'] = $category->id;

                    break;
            }
        }

        return $vars;
    }
}
