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

        $string   = preg_replace('#^index\.php\?#', '', html_entity_decode($string));
        $query    = array();
        $segments = array();
        parse_str($string, $query);

        $itemId = ArrayHelper::getValue($query, 'Itemid');
        $id     = ArrayHelper::getValue($query, 'id');

        // Sometime the ID is empty. We try to recover it from the menu item
        if (empty($id)) {
            $id = $container->helperSEF->getFileIdFromMenuItemId($itemId);
        }

        if (isset($query['task'])) {
            if (ArrayHelper::getValue($query, 'layout') === 'thankyou') {
                // /osdownloads/thankyou/{file-alias}
                $segments[] = 'thankyou';
                $segments[] = $container->helperSEF->getFileAlias($id);
            } else {
                switch ($query['task']) {
                    case 'routedownload':
                        // /osdownloads/routedownload/{file-alias}
                        $segments[] = 'routedownload';
                        $segments[] = $container->helperSEF->getFileAlias($id);
                        break;

                    case 'download':
                        // /osdownloads/download/{file-alias}
                        $segments[] = 'download';
                        $segments[] = $container->helperSEF->getFileAlias($id);
                        break;

                    case 'confirmemail':
                        // /osdownloads/confirmemail/{data}
                        $segments[] = 'confirmemail';
                        $segments[] = ArrayHelper::getValue($query, 'data');
                        break;
                }
            }

        } else {
            if (isset($query['view'])) {
                switch ($query['view']) {
                    case 'categories':
                        // /osdownloads/categories/{categories-aliases}
                        $segments[] = 'categories';

                        $categories = $container->helperSEF->getCategoriesFromMenu($itemId);
                        $segments   = array_merge($segments, $categories);
                        break;

                    case 'downloads':
                        // /osdownloads/downloads/{category-alias}
                        $segments[] = 'downloads';

                        if (!empty($id)) {
                            $segments[] = $container->helperSEF->getCategoryAlias($id);
                        }
                        break;

                    case 'item':
                        // /osdownloads/item/{file-alias}
                        $segments[] = 'item';
                        $segments[] = $container->helperSEF->getFileAlias($id);
                        break;
                }
            }
        }

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

        /* Our first variable always starts at $pos+2...
         * @see https://www.sakic.net/support/sef-advance-extensions/
         */
        $segments = array_slice($segments, $pos+2);

        $vars = array();

        if (isset($segments[0])) {
            switch ($segments[0]) {
                case 'categories':
                    $vars['view'] = 'categories';
                    array_shift($segments);

                    $ids = array();
                    foreach ($segments as $alias) {
                        $id = $container->helperSEF->getCategoryId($alias);

                        if (!empty($id)) {
                            $ids[] = $id;
                        }
                    }

                    $vars['id'] = $ids;
                    break;

                case 'downloads':
                    $vars['view'] = 'downloads';
                    $vars['id']   = $container->helperSEF->getCategoryId($segments[1]);
                    break;

                case 'item':
                    $vars['view'] = 'item';
                    $vars['id']   = $container->helperSEF->getFileIdFromAlias($segments[1]);
                    break;

                case 'routedownload':
                    $vars['task'] = 'routedownload';
                    $vars['tmpl'] = 'component';
                    $vars['id']   = $container->helperSEF->getFileIdFromAlias($segments[1]);
                    break;

                case 'download':
                    $vars['task'] = 'download';
                    $vars['view'] = 'downloads';
                    $vars['tmpl'] = 'component';
                    $vars['id']   = $container->helperSEF->getFileIdFromAlias($segments[1]);
                    break;

                case 'thankyou':
                    $vars['view']   = 'item';
                    $vars['layout'] = 'thankyou';
                    $vars['tmpl']   = 'component';
                    $vars['task']   = 'routedownload';
                    $vars['id']     = $container->helperSEF->getFileIdFromAlias($segments[1]);
                    break;

                case 'confirmemail':
                    $vars['task'] = 'confirmemail';
                    $vars['data'] = $segments[1];
                    break;
            }
        }

        // Apply the variables to the input
        $input = Factory::getApplication()->input;
        foreach ($vars as $var => $value) {
            $input->set($var, $value);
        }

        $query = http_build_query($vars);

        return $query;
    }
}
