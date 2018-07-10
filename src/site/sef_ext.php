<?php
/**
 * @package      SEF Advance
 * @copyright    Copyright (C) 2003-2013 Emir Sakic, http://www.sakic.net. All rights reserved.
 * @contact      www.joomlashack.com, help@joomlashack.com
 * @copyright    2016-2018 Open Source Training, LLC. All rights reserved
 * @license      GNU/GPL, see LICENSE.TXT
 *
 * This program is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This header must not be removed. Additional contributions/changes
 * may be added to this header as long as no information is deleted.
 */
/**
 * SEF Advance component extension
 *
 * This extension will give the SEF Advance style URLs to the example component
 * Place this file (sef_ext.php) in the main component directory
 * Note that the class must be named: sef_componentname
 *
 * Copyright (C) 2003-2007 Emir Sakic, http://www.sakic.net, All rights reserved.
 *
 * Comments: for SEF Advance > v3.6
 **/

use Joomla\Utilities\ArrayHelper;
use Joomla\Registry\Registry;
use Alledia\Framework\Factory;
use Alledia\OSDownloads\Free\Factory as OSDFactory;

defined( '_JEXEC' ) or die;

class sef_osdownloads {

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
     * The DI container
     * @var object
     */
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
     * @param string[] $segments
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
     * @return string[]
     */
    public function getCustomSegments()
    {
        return $this->customSegments;
    }

    /**
    * Creates the SEF Advance URL out of the request
    * Input: $string, string, The request URL (index.php?option=com_example&Itemid=$Itemid)
    * Output: $sefstring, string, SEF Advance URL ($var1/$var2/)
    **/
    public function create ($string)
    {
        /*====================================================
        =            Extract variables from query            =
        ====================================================*/

        $string   = preg_replace('#^index\.php\?#', '', html_entity_decode($string));
        $query    = array();
        $segments = array();
        parse_str($string, $query);

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

        if (!empty($view) && empty($itemId)) {
            // Check if we have a menu item. If so, we adjust the item ID.
            $menu = $this->container->helperSEF->getMenuItemByQuery(
                array(
                    'view' => $view,
                    'id'   => $id
                )
            );

            if (!empty($menu)) {
                if (is_object($menu)) {
                    if ($menu->query['id'] == $id) {
                        $itemId = $menu->id;
                    }
                } elseif (is_array($menu) && isset($menu[$id])) {
                    if ($menu[$id]->query['id'] == $id) {
                        $itemId = $menu->id;
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


                    // The task/layout segments
                    $segments[] = $task;

                    // Check if the thankyou layout was requested
                    if ('thankyou' === $layout) {
                        $segments[] = 'thankyou';
                    }

                    // Categories segments
                    $segments = $this->container->helperSEF->appendCategoriesToSegments($segments, $categoryId);

                    // File segment
                    $segments[] = $this->container->helperSEF->getFileAlias($id);

                    break;

                case 'confirmemail':
                    $segments[] = 'confirmemail';
                    $segments[] = $data;

                    break;
            }

            $sefString = implode('/', $segments) . '/';

            return $sefString;
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
                    // Build the complete route
                    // Categories segments
                    $segments = $this->container->helperSEF->appendCategoriesToSegments($segments, $id);

                    break;

                /**
                 *
                 * List of files
                 *
                 */
                case 'downloads':
                    // Build the complete route
                    // Categories segments
                    $segments = $this->container->helperSEF->appendCategoriesToSegments($segments, $id);

                    // Append the file alias
                    $segments[] = $this->customSegments['files'];

                    break;

                /**
                 *
                 * A single file
                 *
                 */
                case 'item':
                    $categoryId = $this->container->helperSEF->getCategoryIdFromFileId($id);

                    // Build the complete route
                    // Categories segments
                    $segments = $this->container->helperSEF->appendCategoriesToSegments($segments, $categoryId);

                    // Append the file alias
                    $segments[] = $this->container->helperSEF->getFileAlias($id);

                    break;
            }
        }

        /*=====  End of Try to build the route  ======*/

        $sefString = implode('/', $segments) . '/';

        return $sefString;
    }

    /**
     * Build and return the query string based on the array of vars.
     *
     * @param  array $vars
     *
     * @return array
     */
    protected function getQuery($vars)
    {
        // Apply the variables to the input
        $input = Factory::getApplication()->input;

        foreach ($vars as $var => $value) {
            $input->set($var, $value);
        }

        $query = '&' . http_build_query($vars);

        return $query;
    }

    /**
    * Reverts to the query string out of the SEF Advance URL
    * Input:
    *    $url_array, array, The SEF Advance URL split in arrays
    *    $pos, int, The position offset for virtual directories (first virtual directory, which is the component name, begins at $pos+1)
    * Output: $QUERY_STRING, string, query string (var1=$var1&var2=$var2)
    *    Note that this will be added to already defined first part (option=com_example&Itemid=$Itemid)
    **/
    public function revert ($segments, $pos)
    {
        $segments = array_splice($segments, $pos + 2);
        $segments = array_map('trim', $segments);
        $segments = array_filter($segments);

        $vars = array();

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
                    JError::raiseError(400, JText::_('COM_OSDOWNLOADS_ERROR_EXPECTED_DATA_SEGMENT'));
                }

                $vars['data'] = $lastSegment;

                return $this->getQuery($vars);
                break;

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

                        return $this->getQuery($vars);
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
                            if (in_array($menu->query['view'], array('downloads', 'categories'))) {
                                // Complete the path using the path from the menu
                                $category = $this->container->helperSEF->getCategory($menu->query['id']);
                                $tmpPath = $category->path;

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
                        JError::raiseError(404, JText::_('COM_OSDOWNLOADS_ERROR_NOT_FOUND'));
                    }

                    $vars['id'] = $file->id;

                    return $this->getQuery($vars);
                }

                break;
        }

        /**
         *
         * Check the last segment. Is it a list of files?
         *
         */
        if ($this->customSegments['files'] === $lastSegment) {
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

                if (is_object($menu)) {
                    if ('com_osdownloads' === $menu->query['option']) {
                        if (in_array($menu->query['view'], array('downloads', 'categories'))) {
                            // Complete the path using the path from the menu
                            $category = $this->container->helperSEF->getCategory($menu->query['id']);
                            $tmpPath = $category->path;

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
            }

            if (empty($segments)) {
                $vars['id'] = '0';
            }

            // Detect Itemid
            $menuItem = $this->container->helperSEF->getMenuItemByQuery($vars);
            if (is_object($menuItem)) {
                $vars['Itemid'] = $menuItem->id;
            }

            return $this->getQuery($vars);
        }

        /**
         *
         * Check the last segment. Is it a single file? Does it has a correct path?
         *
         */
        $tmpSegments = $segments;
        array_pop($tmpSegments);

        $path = implode('/', $tmpSegments);
        $file = $this->container->helperSEF->getFileFromAlias($lastSegment, $path);

        if (empty($file)) {
            // If no file was found, we try to complete the path based on the menu
            $menu = $this->container->app->getMenu()->getActive();

            if ('com_osdownloads' === $menu->query['option']) {
                if (in_array($menu->query['view'], array('downloads', 'categories'))) {
                    // Complete the path using the path from the menu
                    $category = $this->container->helperSEF->getCategory($menu->query['id']);
                    $tmpPath = $category->path;

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

            // Detect Itemid
            $menuItem = $this->container->helperSEF->getMenuItemByQuery($vars);
            if (is_object($menuItem)) {
                $vars['Itemid'] = $menuItem->id;
            }

            return $this->getQuery($vars);
        }

        /**
         *
         * Check the last segment. Is it a category list? Does it has a correct path?
         *
         */
        $tmpSegments = $segments;

        $path = implode('/', $tmpSegments);

        $category = $this->container->helperSEF->getCategoryFromAlias($lastSegment, $path);

        if (is_object($category)) {
            $vars['view'] = 'categories';
            $vars['id']   = $category->id;

            // Detect Itemid
            $menuItem = $this->container->helperSEF->getMenuItemByQuery($vars);
            if (is_object($menuItem)) {
                $vars['Itemid'] = $menuItem->id;
            }

            return $this->getQuery($vars);
        }

        /**
         *
         * Nope, no valid route found.
         *
         */
        JError::raiseError(404, JText::_('COM_OSDOWNLOADS_ERROR_NOT_FOUND'));
    }
}
?>
