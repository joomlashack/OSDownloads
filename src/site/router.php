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
     * An array with custom segments:
     *
     * array(
     *     'files' => 'files',
     * )
     *
     * @var array
     */
    protected $customSegments;

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
        $this->setCustomSegments();
    }

    /**
     * Set the container to the class
     */
    public function setContainer()
    {
        $this->container = OSDFactory::getContainer();
    }

    /**
     * Allow to set custom segments for routes.
     *
     * @param array $segments
     */
    public function setCustomSegments($segments = array())
    {
        $params = JFactory::getApplication()->getParams('com_osdownloads');

        // Default values
        $default = array(
            'files' => $params->get('route_segment_files', 'files'),
        );

        $this->customSegments = array_merge($default, $segments);
    }

    /**
     * Get a list of custom segments.
     *
     * @return array
     */
    public function getCustomSegments()
    {
        return $this->customSegments;
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
            JError::raiseError(404, JText::_('COM_OSDOWNLOADS_ERROR_NOT_FOUND'));
        }

        // Check if the path is correct
        $path = implode('/', $segments);
        if ($path !== $category->path) {
            JError::raiseError(404, JText::_('COM_OSDOWNLOADS_ERROR_NOT_FOUND'));
        }
    }

    /**
     * Returns the menu item based on the item id we provide.
     *
     * @param  int $itemId
     * @return JMenuItem|null
     */
    protected function getMenuItem($itemId)
    {
        $menu = Factory::getApplication()->getMenu()->getItem($itemId);

        return $menu;
    }

    /**
     * Get the view set for the menu item, based on the item id.
     *
     * @param  int $itemId
     * @return string
     */
    protected function getMenuItemQueryView($itemId)
    {
        $menuItem = $this->getMenuItem($itemId);
        $view     = null;

        if (!empty($menuItem)) {
            if ('com_osdownloads' === $menuItem->component) {
                $view = $menuItem->query['view'];
            }
        }

        return $view;
    }

    /**
     * Get the id set for the menu item, based on the item id.
     *
     * @param  int $itemId
     * @return string
     */
    protected function getMenuItemQueryId($itemId)
    {
        $menuItem = $this->getMenuItem($itemId);
        $id       = null;

        if (!empty($menuItem)) {
            if ('com_osdownloads' === $menuItem->component) {
                $id = $menuItem->query['id'];
            }
        }

        return $id;
    }

    /**
     * Prepend the menu path to the given path, if a menu exists. Returns the
     * modified array of segments.
     *
     * @param  int   $fileId
     * @param  int   $categoryId
     * @param  array $segments
     * @param  array $middlePath
     * @param  array $endPath
     *
     * @return array
     */
    protected function buildRoutePrependingMenuPath($fileId, $categoryId, $segments, $middlePath, $endPath)
    {
        $skipCategoryAndFileSegments = false;
        $categorySegmentToSkip       = false;
        $menu                        = null;

        // Do we have a file?
        if (!empty($fileId)) {
            // Is there a menu item for the file?
            $menu = $this->container->helperSEF->getMenuItemForFile($fileId);
            if (!empty($menu)) {
                // Yes, add the segments from the menu
                $segments = $this->container->helperSEF->appendMenuPathToSegments($segments, $menu);
                $skipCategoryAndFileSegments = true;
            }
        }

        // No menu for the file.
        if (empty($menu)) {
            // Is there a menu for any parent category of the file, or given category?
            $menu = $this->container->helperSEF->getMenuItemForCategoryTreeRecursively($categoryId);

            if (!empty($menu)) {
                // Yes, add the segments from the menu
                $segments = $this->container->helperSEF->appendMenuPathToSegments($segments, $menu);

                // Get the segments of the category from the menu to exclude them from the route
                $menuCatId    = $this->container->helperSEF->getIdFromLink($menu->link);
                $menuCategory = $this->container->helperSEF->getCategory($menuCatId);

                // Check if is an object, because if it is related to the root menu, it won't have
                // a category register
                if (is_object($menuCategory)) {
                    $categorySegmentToSkip = $menuCategory->path;
                }
            }
        }

        // Middle segments
        if (!empty($middlePath)) {
            $segments = array_merge($segments, $middlePath);
        }

        // Do we need to skip the category and end path because the found menu already defines it?
        if ($skipCategoryAndFileSegments) {
            return $segments;
        }

        // Categories segments
        $segments = $this->container->helperSEF->appendCategoriesToSegments($segments, $categoryId, $categorySegmentToSkip);

        // End segments
        if (!empty($endPath)) {
            $segments = array_merge($segments, $endPath);
        }

        return $segments;
    }

    /**
     * Build the route for the com_osdownloads component.
     *
     * @param   array &$query An array of URL arguments
     *
     * @see https://goo.gl/X8U2wh  Examples of routes
     *
     * @return  array  The URL arguments to use to assemble the subsequent URL.
     */
    public function build(&$query)
    {
        /*====================================================
        =            Extract variables from query            =
        ====================================================*/

        $segments  = array();

        $id     = ArrayHelper::getValue($query, 'id');
        $view   = ArrayHelper::getValue($query, 'view');
        $layout = ArrayHelper::getValue($query, 'layout');
        $task   = ArrayHelper::getValue($query, 'task');
        $data   = ArrayHelper::getValue($query, 'data');
        $itemId = ArrayHelper::getValue($query, 'Itemid');

        unset($query['view']);
        unset($query['layout']);
        unset($query['id']);
        unset($query['task']);
        unset($query['tmpl']);

        // Try to get the view. If no view is provided but we have Itemid,
        // find out the view.
        if (empty($view) && !empty($itemId)) {
            $view = $this->getMenuItemQueryView($itemId);
        }

        if (empty($id) && !empty($itemId)) {
            $id = $this->getMenuItemQueryId($itemId);
        }

        /**

            TODO:
            - If an OSDownloads menu, extract all menu item link vars to the query, not only view and id (most commons);

         */

        /*=====  End of Extract variables from query  ======*/


        /*==============================================
        =            Try to build the route            =
        ==============================================*/

        if (!empty($task)) {
            switch ($task) {
                case 'routedownload':
                case 'download':
                    $categoryId = $this->container->helperSEF->getCategoryIdFromFileId($id);

                    $middlePath = array();
                    $endPath    = array();


                    // The task/layout segments
                    $middlePath[] = $task;

                    // Check if the thankyou layout was requested
                    if ('thankyou' === $layout) {
                        $middlePath[] = 'thankyou';
                    }

                    // File segment
                    $endPath[] = $this->container->helperSEF->getFileAlias($id);

                    // Build the complete route
                    $segments = $this->buildRoutePrependingMenuPath($id, $categoryId, $segments, $middlePath, $endPath);

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
                    $middlePath = array();
                    $endPath    = array();

                    // Build the complete route
                    $segments = $this->buildRoutePrependingMenuPath(null, $id, $segments, $middlePath, $endPath);

                    break;

                /**
                 *
                 * List of files
                 *
                 */
                case 'downloads':
                    $middlePath = array();
                    $endPath    = array();

                    // Append the file alias
                    $endPath[] = $this->customSegments['files'];

                    // Build the complete route
                    $segments = $this->buildRoutePrependingMenuPath(null, $id, $segments, $middlePath, $endPath);

                    break;

                /**
                 *
                 * A single file
                 *
                 */
                case 'item':
                    $categoryId = $this->container->helperSEF->getCategoryIdFromFileId($id);

                    $middlePath = array();
                    $endPath    = array();

                    // Check if the thankyou layout was requested
                    if ('thankyou' === $layout) {
                        $middlePath[] = 'thankyou';
                    }

                    // Append the file alias
                    $endPath[] = $this->container->helperSEF->getFileAlias($id);

                    // Build the complete route
                    $segments = $this->buildRoutePrependingMenuPath($id, $categoryId, $segments, $middlePath, $endPath);

                    break;
            }
        }

        /*=====  End of Try to build the route  ======*/

        return $segments;
    }

    /**
     * Parse routes detecting query args. We try to detect using the following parts:
     *
     *   - menu path
     *   - task
     *   - layout
     *   - category path
     *   - file alias
     *
     * @param array $segments
     *
     * @see  https://goo.gl/X8U2wh  Examples of routes
     *
     * @return array
     */
    public function parse(&$segments)
    {
        $vars = array();

        /**
         *
         * Check the last segment, it could be:
         *
         *   A) file alias
         *   B) category alias
         *   C) 'files' or custom segment for list of files
         *   D) thank you layout
         *   E) task
         *
         */

        $availableTasks = array('routedownload', 'download');
        $lastSegment    = end($segments);

        /*----------  File Alias?  ----------*/

        $file = $this->container->helperSEF->getFileFromAlias($lastSegment);

        if (!empty($file)) {
            // We found a file
            array_pop($segments);

            // Remove category path, if there
            $category = $this->container->helperSEF->getCategory($file->cate_id);
            $categoryPath = explode('/', $category->path);

            while (!empty($segments)) {
                if (in_array(end($segments), $categoryPath)) {
                    array_pop($segments);
                } else {
                    break;
                }
            }

            $vars['id'] = $file->id;

            // Do we have the thankyou layout?
            if ('thankyou' === end($segments)) {
                $vars['layout'] = 'thankyou';
                array_pop($segments);
            }

            // Do we have a task segment?
            if (!empty($segments)) {
                if (in_array(end($segments), $availableTasks)) {
                    $vars['task'] = array_pop($segments);
                    $vars['tmpl'] = 'component';
                }
            }
        }

        // Task as the last segment, based on menu item?
        if (!empty($segments) && in_array(end($segments), $availableTasks)) {
            $vars['task'] = array_pop($segments);
            $vars['tmpl'] = 'component';

            // Look for the id found in the menu item
            $path = implode('/', $segments);
            $menu = $this->container->helperSEF->getMenuItemsFromPath($path);

            if (!empty($menu)) {
                $id = $this->container->helperSEF->getIdFromLink($menu->link);

                if (!empty($id)) {
                    $vars['id'] = $id;

                    return $vars;
                } else {
                    JError::raiseError(404, JText::_('COM_OSDOWNLOADS_ERROR_NOT_FOUND'));
                }
            }
        }

        /**
         *
         * Check the first segment, it could be the confirmemail task
         *
         */

        $firstSegment = reset($segments);
        if ('confirmemail' === $firstSegment) {
            $vars['task'] = 'confirmemail';
            $vars['tmpl'] = 'component';

            // Check if we have the data segment
            if (!empty(end($segments))) {
                $vars['data'] = end($segments);

                return $vars;
            }

            /**

                TODO:
                - Throw error

             */
        }

        /**
         *
         * If no task were found, try to detect the views
         *
         */
        if (!isset($vars['task'])) {
            // Is a single file route?
            if (is_object($file)) {
                // Yes
                if (isset($vars['id']) && !isset($vars['view'])) {
                    $vars['view'] = 'item';

                    return $vars;
                }
            } else {
                // No, is it a list of files?
                if ($this->customSegments['files'] === end($segments)) {
                    // Yes
                    $vars['view'] = 'downloads';

                    // Try to detect the category
                    $category = $this->container->helperSEF->getCategoryFromAlias(end($segments));

                    if (!empty($category)) {
                        $vars['id'] = $category->id;

                        return $vars;
                    }
                } else {
                    // No, is it a list of categories?
                    $category = $this->container->helperSEF->getCategoryFromAlias(end($segments));

                    if (!empty($category)) {
                        $vars['view'] = 'categories';
                        $vars['id']    = $category->id;

                        return $vars;
                    }
                }
            }
        }

        // Check menu items
        $path = implode('/', $segments);
        $menu = $this->container->helperSEF->getMenuItemsFromPath($path);

        if (!empty($menu)) {
            if (!isset($vars['view'])) {
                $vars['view'] = $this->getMenuItemQueryView($menu->id);
                $vars['id']   = $this->getMenuItemQueryId($menu->id);

                return $vars;
            }
        }

        // Is a list of categories related to the Root?
        if (empty($segments) || (is_array($segments) && empty($segments[0]))) {
            $vars['view'] = 'categories';
            $vars['id']   = '0';

            return $vars;
        }

        /**

            TODO:
            - No valid route found? Throw an error

         */


        return $vars;
    }
}
