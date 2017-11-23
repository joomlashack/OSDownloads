<?php
/**
 * @package      SEF Advance
 * @copyright    Copyright (C) 2003-2013 Emir Sakic, http://www.sakic.net. All rights reserved.
 * @contact      www.joomlashack.com, help@joomlashack.com
 * @copyright    2016-2017 Open Source Training, LLC. All rights reserved
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
use Alledia\Framework\Factory;
use Alledia\OSDownloads\Free\Factory as OSDFactory;

defined('_JEXEC') or die;

class sef_osdownloads
{
    /**
     * Creates the SEF Advance URL out of the request
     * Input: $string, string, The request URL (index.php?option=com_example&Itemid=$Itemid)
     * Output: $sefstring, string, SEF Advance URL ($var1/$var2/)
     **/
    public function create($string)
    {
        $container = OSDFactory::getContainer();

        // Remove index.php from the string
        $string   = preg_replace('#^index\.php\?#', '', html_entity_decode($string));
        $query    = array();
        $segments = array();
        parse_str($string, $query);

        /*----------  Build the route  ----------*/
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

                    // If the id is empty, we try to get it using the item id
                    if (empty($id)) {
                        $id = $container->helperSEF->getFileIdFromMenuItemId($itemId);
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

        // Remove tmp=component
        if (isset($query['tmpl']) && 'component' === $query['tmpl']) {
            unset($query['tmpl']);
        }

        /*----------  Convert to string  ----------*/
        $sefString = implode('/', $segments) . '/';

        return $sefString;
    }

    /**
     * Reverts to the query string out of the SEF Advance URL
     * Input:
     *    $segments, array, The SEF Advance URL split in arrays
     *    $pos, int, The position offset for virtual directories (first virtual directory, which is the component name,
     *    begins at $pos+1) Output: $QUERY_STRING, string, query string (var1=$var1&var2=$var2) Note that this will be
     *    added to already defined first part (option=com_example&Itemid=$Itemid)
     **/
    public function revert($segments, $pos)
    {
        $container = OSDFactory::getContainer();
        $vars      = array();

        /* Our first variable always starts at $pos+2...
         * @see https://www.sakic.net/support/sef-advance-extensions/
         */
        $segments = array_slice($segments, $pos+2);

        /*----------  Build the route  ----------*/
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
                $id = $container->helperSEF->getFileIdFromAlias(
                    $container->helperSEF->getLastNoEmptyArrayItem($segments)
                );

                $vars['tmpl'] = 'component';

                break;

            case 'confirmemail':
                $vars['task'] = 'confirmemail';
                $vars['data'] = $container->helperSEF->getLastNoEmptyArrayItem($segments);
                $vars['tmpl'] = 'component';

                break;


            case 'downloads':
                $vars['view'] = 'downloads';

                // Category id
                $category = $container->helperSEF->getCategoryFromAlias(
                    $container->helperSEF->getLastNoEmptyArrayItem($segments)
                );
                $vars['id'] = $category->id;

                break;

            case 'categories':
                $vars['view'] = 'categories';

                // Category id
                $category = $container->helperSEF->getCategoryFromAlias(
                    $container->helperSEF->getLastNoEmptyArrayItem($segments)
                );
                $vars['id'] = $category->id;

                break;

            case 'item':
                $vars['view'] = 'item';

                // File id
                $vars['id'] = $container->helperSEF->getFileIdFromAlias(
                    $container->helperSEF->getLastNoEmptyArrayItem($segments)
                );

                break;
        }

        /*----------  Apply the variables to the input  ----------*/
        $input = Factory::getApplication()->input;
        foreach ($vars as $var => $value) {
            $input->set($var, $value);

            $_GET[$var]     = $value;
            $_REQUEST[$var] = $value;
        }

        // Convert to URL query string
        $query = http_build_query($vars);

        return $query;
    }
}
