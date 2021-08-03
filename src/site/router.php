<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2005-2021 Joomlashack.com. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 *
 * This file is part of OSDownloads.
 *
 * OSDownloads is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * OSDownloads is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OSDownloads.  If not, see <http://www.gnu.org/licenses/>.
 */

use Alledia\OSDownloads\Free\Container;
use Alledia\OSDownloads\Free\Factory as OSDFactory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Component\Router\RouterBase;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die();

if (!defined('OSDOWNLOADS_LOADED')) {
    require_once JPATH_ADMINISTRATOR . '/components/com_osdownloads/include.php';
}

/**
 * @param array $query
 *
 * @return array
 */
function OsdownloadsBuildRoute(&$query)
{
    $router = new OsdownloadsRouter();

    return $router->build($query);
}

/**
 * @param array $segments
 *
 * @return array
 * @throws Exception
 */
function OsdownloadsParseRoute($segments)
{
    $router = new OsdownloadsRouter();

    return $router->parse($segments);
}

/**
 * Routing class from com_osdownloads
 */
class OsdownloadsRouter extends RouterBase
{
    /**
     * An array with custom segments:
     *
     * array(
     *     'files' => 'files',
     * )
     *
     * @var string[]
     */
    protected $customSegments;

    /**
     * @var object[]
     */
    protected static $categories = null;

    /**
     * @var object[]
     */
    protected static $files = null;

    /**
     * The DI container
     *
     * @var Container
     */
    protected $container;

    /**
     * @inheritDoc
     */
    public function __construct($app = null, $menu = null)
    {
        parent::__construct($app, $menu);

        Log::addLogger(
            ['text_file' => 'com_osdownloads.router.errors.php'],
            Log::ALL,
            ['com_osdownloads.router']
        );

        $this->container = OSDFactory::getPimpleContainer();
    }

    /**
     * Allow to set custom segments for routes.
     *
     * @param string[] $segments
     */
    public function setCustomSegments($segments = [])
    {
        $params = ComponentHelper::getParams('com_osdownloads');

        // Default values
        $default = ['files' => $params->get('route_segment_files', 'files')];

        $this->customSegments = array_merge($default, $segments);
    }

    /**
     * Get a list of custom segments.
     *
     * @return string[]
     */
    public function getCustomSegments()
    {
        if (!isset($this->customSegments)) {
            $this->setCustomSegments();
        }

        return $this->customSegments;
    }

    /**
     * Check if the category and path are correct. If category is empty, or we
     * have a wrong path, trigger a 404 error.
     *
     * @param stdClass $category
     * @param array    $segments
     *
     * @return void
     * @throws Exception
     */
    protected function checkCategoryAndPath($category, $segments)
    {
        // Check if the category was foud
        if (empty($category)) {
            throw new Exception(Text::_('COM_OSDOWNLOADS_ERROR_NOT_FOUND'), 404);
        }

        // Check if the path is correct
        $path = implode('/', $segments);
        if ($path !== $category->path) {
            throw new Exception(Text::_('COM_OSDOWNLOADS_ERROR_NOT_FOUND'), 404);
        }
    }

    /**
     * Prepend the menu path to the given path, if a menu exists. Returns the
     * modified array of segments.
     *
     * @param int   $fileId
     * @param int   $categoryId
     * @param array $segments
     * @param array $middlePath
     * @param array $endPath
     * @param array $query
     *
     * @return array
     */
    protected function buildRoutePrependingMenuPath($fileId, $categoryId, $segments, $middlePath, $endPath, &$query)
    {
        $skipCategoryAndFileSegments = false;
        $categorySegmentToSkip       = false;
        $menu                        = null;

        // Do we have a file?
        if (!empty($fileId)) {
            // Is there a menu item for the file?
            $menu = $this->container->helperSEF->getMenuItemForFile($fileId);
            if (!empty($menu)) {
                $skipCategoryAndFileSegments = true;

                $query['Itemid'] = $menu->id;
            }
        }

        // No menu for the file.
        if (empty($menu)) {
            // Is there a menu for any parent category of the file, or given category?
            $menu = $this->container->helperSEF->getMenuItemForCategoryTreeRecursively($categoryId);

            if (!empty($menu)) {
                $query['Itemid'] = $menu->id;
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
        $segments = $this->container->helperSEF->appendCategoriesToSegments(
            $segments,
            $categoryId,
            $categorySegmentToSkip
        );

        // End segments
        if (!empty($endPath)) {
            $segments = array_merge($segments, $endPath);
        }

        return $segments;
    }

    /**
     * Build the route for the com_osdownloads component.
     *
     * @param array &$query An array of URL arguments
     *
     * @return  array  The URL arguments to use to assemble the subsequent URL.
     * @see https://goo.gl/X8U2wh  Examples of routes
     *
     */
    public function build(&$query)
    {
        /*====================================================
        =            Extract variables from query            =
        ====================================================*/

        $segments = [];

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
        unset($query['data']);


        // Try to get the view. If no view is provided but we have Itemid,
        // find out the view.
        if (empty($view) && !empty($itemId)) {
            $menu = $this->container->helperSEF->getMenuItemById($itemId);
            if (!empty($menu)) {
                $view = $menu->query['view'];
            }
        }

        if (empty($id) && !empty($itemId)) {
            $menu = $this->container->helperSEF->getMenuItemById($itemId);
            if (!empty($menu)) {
                $id = $menu->query['id'];
            }
        }

        if (!empty($view)) {
            // Check if we have a menu item. If so, we adjust the item ID.
            $menu = $this->container->helperSEF->getMenuItemByQuery(
                [
                    'view' => $view,
                    'id'   => $id
                ]
            );

            if (!empty($menu)) {
                if (is_object($menu)) {
                    if ($menu->query['id'] == $id) {
                        $query['Itemid'] = $menu->id;

                        if (empty($task)) {
                            return $segments;
                        }
                    }
                } elseif (is_array($menu) && isset($menu[$id])) {
                    if ($menu[$id]->query['id'] == $id) {
                        $query['Itemid'] = $menu[$id]->id;

                        if (empty($task)) {
                            return $segments;
                        }
                    }

                }
            }
        }

        /*=====  End of Extract variables from query  ======*/


        /*==============================================
        =            Try to build the route            =
        ==============================================*/

        if (!empty($task)) {
            switch ($task) {
                case 'routedownload':
                case 'download':
                    $categoryId = $this->container->helperSEF->getCategoryIdFromFileId($id);

                    $middlePath = [];
                    $endPath    = [];


                    // The task/layout segments
                    $middlePath[] = $task;

                    // Check if the thankyou layout was requested
                    if ('thankyou' === $layout) {
                        $middlePath[] = 'thankyou';
                    }

                    // File segment
                    $endPath[] = $this->container->helperSEF->getFileAlias($id);

                    // Build the complete route
                    $segments = $this->buildRoutePrependingMenuPath(
                        $id,
                        $categoryId,
                        $segments,
                        $middlePath,
                        $endPath,
                        $query
                    );


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
                // @TODO: Filter this for the Pro version only
                /**
                 *
                 * List of categories
                 *
                 */
                case 'categories':
                    $middlePath = [];
                    $endPath    = [];

                    // Build the complete route
                    $segments = $this->buildRoutePrependingMenuPath(
                        null,
                        $id,
                        $segments,
                        $middlePath,
                        $endPath,
                        $query
                    );

                    break;

                /**
                 * List of files
                 */
                case 'downloads':
                    $middlePath = [];
                    $endPath    = [];

                    $this->getCustomSegments();

                    // Append the file alias
                    $endPath[] = $this->customSegments['files'];

                    // Build the complete route
                    $segments = $this->buildRoutePrependingMenuPath(
                        null,
                        $id,
                        $segments,
                        $middlePath,
                        $endPath,
                        $query
                    );

                    break;

                /**
                 * A single file
                 */
                case 'item':
                    $categoryId = $this->container->helperSEF->getCategoryIdFromFileId($id);

                    $middlePath = [];
                    $endPath    = [];

                    // Append the file alias
                    $endPath[] = $this->container->helperSEF->getFileAlias($id);

                    // Build the complete route
                    $segments = $this->buildRoutePrependingMenuPath(
                        $id,
                        $categoryId,
                        $segments,
                        $middlePath,
                        $endPath,
                        $query
                    );

                    break;
            }
        }

        /*=====  End of Try to build the route  ======*/

        return $segments;
    }

    /**
     * Parse routes detecting query args.
     *
     * @param array $segments
     *
     * @return array
     * @throws Exception
     * @see  https://goo.gl/X8U2wh  Examples of routes
     *
     */
    public function parse(&$segments)
    {
        $vars = [];

        /**
         *
         * Check the first segment, it could be a task
         *
         */
        $firstSegment = reset($segments);
        $lastSegment  = end($segments);
        $tmpSegments  = $segments;

        switch ($firstSegment) {
            case 'confirmemail':
                $vars['task'] = $firstSegment;
                $vars['tmpl'] = 'component';

                // Check if we have the data segment
                if (empty($lastSegment) || $firstSegment === $lastSegment) {
                    throw new Exception(Text::_('COM_OSDOWNLOADS_ERROR_EXPECTED_DATA_SEGMENT'), 400);
                }

                $vars['data'] = $lastSegment;

                return $vars;

            case 'routedownload':
            case 'download':
                $vars['task'] = $firstSegment;
                $vars['tmpl'] = 'component';

                array_shift($tmpSegments);

                // Check if we have the thankyou segment
                if ('thankyou' === reset($tmpSegments)) {
                    $vars['layout'] = 'thankyou';
                    array_shift($tmpSegments);
                }

                // If you we don't have any other segments, look for the ID on the menu item
                if (empty($tmpSegments)) {
                    $menuItem = $this->container->app->getMenu()->getActive();

                    if (!empty($menuItem)) {
                        $vars['id'] = $menuItem->query['id'];

                        return $vars;
                    }
                }

                // Look for the file ID parsing the remaining path
                if (!empty($tmpSegments)) {
                    array_pop($tmpSegments);

                    $path = implode('/', $tmpSegments);
                    $file = $this->container->helperSEF->getFileFromAlias($lastSegment, $path);

                    if (empty($file)) {
                        // Try to detect menu to complete the path
                        $menu = $this->container->app->getMenu()->getActive();

                        if ('com_osdownloads' === $menu->query['option']) {
                            if (in_array($menu->query['view'], ['downloads', 'categories'])) {
                                // Complete the path using the path from the menu
                                $category = $this->container->helperSEF->getCategory($menu->query['id']);
                                $tmpPath  = $category->path;

                                if (!empty($tmpSegments)) {
                                    $tmpPath .= '/' . implode($tmpSegments);
                                }

                                // Try to get the file with the new path
                                $file = $this->container->helperSEF->getFileFromAlias($lastSegment, $tmpPath);

                                if (!empty($file)) {
                                    // We found a file
                                    $vars['id'] = $file->id;
                                }
                            }
                        }

                    }

                    if (!is_object($file)) {
                        throw new Exception(Text::_('COM_OSDOWNLOADS_ERROR_NOT_FOUND'), 404);
                    }

                    $vars['id'] = $file->id;

                    return $vars;
                }

                break;
        }

        /*
         * Check the last segment. Is it a list of files?
         */
        if (
            isset($this->customSegments['files'])
            && $this->customSegments['files'] === $lastSegment
        ) {
            // Yes
            $vars['view'] = 'downloads';

            array_pop($segments);

            // Try to detect the category
            $category = $this->container->helperSEF->getCategoryFromAlias(
                end($segments),
                implode('/', $segments)
            );

            if (!empty($category)) {
                $vars['id'] = $category->id;
            }

            if (empty($category)) {
                // Try to detect menu to complete the path
                $menu = $this->container->app->getMenu()->getActive();

                if ('com_osdownloads' === $menu->query['option']) {
                    if (in_array($menu->query['view'], ['downloads', 'categories'])) {
                        // Complete the path using the path from the menu
                        $category = $this->container->helperSEF->getCategory($menu->query['id']);
                        $tmpPath  = $category->path;

                        if (!empty($segments)) {
                            $tmpPath .= '/' . implode($segments);
                        }

                        // Try to get the category with the new path
                        $category = $this->container->helperSEF->getCategoryFromAlias(
                            end($segments),
                            $tmpPath
                        );

                        if (!empty($category)) {
                            // We found a category
                            $vars['id'] = $category->id;
                        }
                    }
                }

            }

            if (empty($segments)) {
                $vars['id'] = '0';
            }

            return $vars;
        }

        /**
         * Check the last segment. Is it a single file? Does it has a correct path?
         */
        $tmpSegments = $segments;
        array_pop($tmpSegments);

        $path = implode('/', $tmpSegments);
        $file = $this->container->helperSEF->getFileFromAlias($lastSegment, $path);

        if (empty($file)) {
            // If no file was found, we try to complete the path based on the menu
            $menu = $this->container->app->getMenu()->getActive();

            if ('com_osdownloads' === $menu->query['option']) {
                if (in_array($menu->query['view'], ['downloads', 'categories'])) {
                    // Complete the path using the path from the menu
                    $category = $this->container->helperSEF->getCategory($menu->query['id']);
                    $tmpPath  = $category->path;

                    if (!empty($tmpSegments)) {
                        $tmpPath .= '/' . implode($tmpSegments);
                    }

                    // Try to get the file with the new path
                    $file = $this->container->helperSEF->getFileFromAlias($lastSegment, $tmpPath);
                }
            }
        }

        if (!empty($file)) {
            /*
             * Cool, we found a file
             */
            $vars['view'] = 'item';
            $vars['id']   = $file->id;

            return $vars;
        }

        /**
         * Check the last segment. Is it a category list? Does it has a correct path?
         */
        $tmpSegments = $segments;

        $path = implode('/', $tmpSegments);

        $category = $this->container->helperSEF->getCategoryFromAlias($lastSegment, $path);

        if (is_object($category)) {
            $vars['view'] = 'categories';
            $vars['id']   = $category->id;

            return $vars;
        }

        /**
         * Nope, no valid route found.
         */
        throw new Exception(Text::_('COM_OSDOWNLOADS_ERROR_NOT_FOUND'), 404);
    }
}
