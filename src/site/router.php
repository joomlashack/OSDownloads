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

        if ('com_osdownloads' === $menuItem->component) {
            $view = $menuItem->query['view'];
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

        if ('com_osdownloads' === $menuItem->component) {
            $id = $menuItem->query['id'];
        }

        return $id;
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
                    $skipCategoryAndFileSegments = false;
                    $categorySegmentToSkip       = '';

                    $catId = $this->container->helperSEF->getCategoryIdFromFileId($id);

                    // Is there a menu item for the file?
                    $menu = $this->container->helperSEF->getMenuItemForFile($id);
                    if (!empty($menu)) {
                        // Yes, add the segments from the menu
                        $segments = $this->container->helperSEF->appendMenuPathToSegments($segments, $menu);
                        $skipCategoryAndFileSegments = true;
                    } else {
                        // No. Is there a menu for any parent category of the file?
                        $menu = $this->container->helperSEF->getMenuItemForCategoryTreeRecursively($catId);

                        if (!empty($menu)) {
                            // Yes, add the segments from the menu
                            $segments     = $this->container->helperSEF->appendMenuPathToSegments($segments, $menu);

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

                    /**

                        TODO:
                        - REMOVE the segments of categories already covered by the menu item segments

                     */

                    // The task/layout segments
                    $segments[] = $task;

                    if ($layout === 'thankyou') {
                        $segments[] = 'thankyou';
                    }

                    if ($skipCategoryAndFileSegments) {
                        break;
                    }

                    // Categories segments
                    $segments = $this->container->helperSEF->appendCategoriesToSegments($segments, $catId, $categorySegmentToSkip);

                    // File segment
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
                    $segments = $this->container->helperSEF->appendCategoriesToSegments($segments, $id);

                    break;

                /**
                 *
                 * List of files
                 *
                 */
                case 'downloads':
                    $segments = $this->container->helperSEF->appendCategoriesToSegments($segments, $id);
                    $segments[] = $this->customSegments['files'];

                    break;

                /**
                 *
                 * A single file
                 *
                 */
                case 'item':
                    $catId = $this->container->helperSEF->getCategoryIdFromFileId($id);
                    if (!empty($catId)) {
                        $segments = $this->container->helperSEF->appendCategoriesToSegments($segments, $catId);
                    }

                    $segments[] = $this->customSegments['files'];

                    // Append the file alias
                    $segments[] = $this->container->helperSEF->getFileAlias($id);

                    // Check if the thankyou layout was requested
                    if ('thankyou' === $layout) {
                        $segments[] = 'thankyou';
                    }

                    break;
            }
        }

        /*=====  End of Try to build the route  ======*/

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

        // Check the second to last segment
        $indexSecToLast = count($segments) - 2;
        if (!empty($segments)) {
            if (isset($segments[$indexSecToLast])) {
                switch ($segments[$indexSecToLast]) {
                    /**
                     *
                     * Tasks
                     *
                     */
                    case 'routedownload':
                    case 'download':
                    case 'thankyou':
                        // If thank you, we need to get the third to last segment
                        $segmentsToCleanUp = 1;
                        if ('thankyou' === $segments[$indexSecToLast]) {
                            $task = $segments[$indexSecToLast - 1];

                            $segmentsToCleanUp = 2;
                        } else {
                            $task = $segments[$indexSecToLast];
                        }

                        $alias = array_pop($segments);

                        // Get the file id
                        $id = $this->container->helperSEF->getFileIdFromAlias($alias);

                        // Check if the file exists
                        if (empty($id)) {
                            JError::raiseError(404, JText::_('COM_OSDOWNLOADS_ERROR_NOT_FOUND'));
                        }

                        // Clean up the segments
                        $segments = array_splice($segments, 0, - $segmentsToCleanUp);

                        // Check the categories path
                        $category = $this->container->helperSEF->getCategoryFromFileId($id);
                        $this->checkCategoryAndPath($category, $segments);

                        // Set the vars
                        $vars['task'] = $task;
                        $vars['id'] = $id;
                        $vars['tmpl'] = 'component';

                        return $vars;

                    case 'confirmemail':
                        // Set the vars
                        $vars['task'] = 'confirmemail';
                        $vars['data'] = end($segments);
                        $vars['tmpl'] = 'component';

                        return $vars;
                }
            }

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
                        JError::raiseError(404, JText::_('COM_OSDOWNLOADS_ERROR_NOT_FOUND'));
                    }

                    $segments = array_splice($segments, 0, -3);

                    // Check the category path
                    $category = $this->container->helperSEF->getCategoryFromFileId($id);
                    $this->checkCategoryAndPath($category, $segments);

                    // Set the vars
                    $vars['view']   = 'item';
                    $vars['tmpl']   = 'component';
                    $vars['layout'] = 'thankyou';
                    $vars['id']     = $id;

                    return $vars;
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
                if ($this->customSegments['files'] === $segments[$indexSecToLast]) {
                    // Look for a file with the given alias
                    $id = $this->container->helperSEF->getFileIdFromAlias(end($segments));

                    $segments = array_splice($segments, 0, -2);

                    // Check if the file exists
                    if (empty($id)) {
                        JError::raiseError(404, JText::_('COM_OSDOWNLOADS_ERROR_NOT_FOUND'));
                    }

                    $category = $this->container->helperSEF->getCategoryFromFileId($id);

                    $this->checkCategoryAndPath($category, $segments);

                    // Set the vars
                    $vars['view'] = 'item';
                    $vars['id']   = $id;

                    return $vars;
                }
            }

            /**
             *
             * A list of files
             *
             * The list of files has the last segment equals to "files".
             */
            if (end($segments) === $this->customSegments['files']) {
                $vars['view'] = 'downloads';

                // Get the category id
                array_pop($segments);

                // If there is no more segments, we should display the root category
                if (empty($segments)) {
                    $vars['id'] = 0;

                    return $vars;
                }

                $category = $this->container->helperSEF->getCategoryFromAlias(end($segments));

                $this->checkCategoryAndPath($category, $segments);

                // It is ok, return the id
                $vars['id'] = $category->id;

                return $vars;
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

                return $vars;
            }

            // Try to locate the repective category based on the alias
            $category = $this->container->helperSEF->getCategoryFromAlias(end($segments));

            $this->checkCategoryAndPath($category, $segments);

            // It is ok, return the id
            $vars['id'] = $category->id;

            return $vars;
        }

        return $vars;
    }
}
