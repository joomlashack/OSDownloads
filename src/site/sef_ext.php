<?php
/*
* @package      SEF Advance
* @copyright    Copyright (C) 2003-2013 Emir Sakic, http://www.sakic.net. All rights reserved.
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
        $string   = preg_replace('#^index\.php\?#', '', html_entity_decode($string));
        $query    = array();
        $segments = array();
        parse_str($string, $query);

        if (isset($query['task'])) {
            if (ArrayHelper::getValue($query, 'layout') === 'thankyou') {
                // /osdownloads/thankyou/{file-alias}
                $segments[] = 'thankyou';
                $segments[] = $this->getFileAlias($query['id']);
            } else {
                switch ($query['task']) {
                    case 'routedownload':
                        // /osdownloads/routedownload/{file-alias}
                        $segments[] = 'routedownload';
                        $segments[] = $this->getFileAlias($query['id']);
                        break;

                    case 'download':
                        // /osdownloads/download/{file-alias}
                        $segments[] = 'download';
                        $segments[] = $this->getFileAlias($query['id']);
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

                        $categories = $this->getCategoriesFromMenu($query['Itemid']);
                        $segments = array_merge($segments, $categories);
                        break;

                    case 'downloads':
                        // /osdownloads/downloads/{category-alias}
                        $segments[] = 'downloads';
                        $segments[] = $this->getCategoryAlias($query['id']);
                        break;

                    case 'item':
                        // /osdownloads/item/{file-alias}
                        $segments[] = 'item';
                        $segments[] = $this->getFileAlias($query['id']);
                        break;
                }
            }
        }


        // var_dump($query);
        // var_dump($segments);
        // die;

        $sefString = implode('/', $segments) . '/';

        return $sefString;
    }

    /**
    * Reverts to the query string out of the SEF Advance URL
    * Input:
    *    $segments, array, The SEF Advance URL split in arrays
    *    $pos, int, The position offset for virtual directories (first virtual directory, which is the component name, begins at $pos+1)
    * Output: $QUERY_STRING, string, query string (var1=$var1&var2=$var2)
    *    Note that this will be added to already defined first part (option=com_example&Itemid=$Itemid)
    **/
    public function revert($segments, $pos)
    {
        array_shift($segments);

        $vars = array();

        if (isset($segments[0])) {

            switch ($segments[0]) {
                case 'categories':
                    $vars['view'] = 'categories';
                    array_shift($segments);

                    $ids = array();
                    foreach ($segments as $alias) {
                        $id = $this->getCategoryId($alias);

                        if (!empty($id)) {
                            $ids[] = $id;
                        }
                    }

                    $vars['id'] = $ids;
                    break;

                case 'downloads':
                    $vars['view'] = 'downloads';
                    $vars['id']   = $this->getCategoryId($segments[1]);
                    break;

                case 'item':
                    $vars['view'] = 'item';
                    $vars['id']   = $this->getFileIdFromAlias($segments[1]);
                    break;

                case 'routedownload':
                    $vars['task'] = 'routedownload';
                    $vars['tmpl'] = 'component';
                    $vars['id']   = $this->getFileIdFromAlias($segments[1]);
                    break;

                case 'download':
                    $vars['task'] = 'download';
                    $vars['view'] = 'downloads';
                    $vars['tmpl'] = 'component';
                    $vars['id']   = $this->getFileIdFromAlias($segments[1]);
                    break;

                case 'thankyou':
                    $vars['view']   = 'item';
                    $vars['layout'] = 'thankyou';
                    $vars['tmpl']   = 'component';
                    $vars['task']   = 'routedownload';
                    $vars['id']     = $this->getFileIdFromAlias($segments[1]);
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

    /**
     * Return the alias of a menu based on it's menu item id.
     *
     * @param int $itemId
     *
     * @return string
     */
    protected function getMenuAlias($itemId)
    {
        $db = Factory::getDbo();

        $query = $db->getQuery(true)
            ->select('alias')
            ->from('#__menu')
            ->where('id = ' . $db->quote((int) $itemId));

        return $db->setQuery($query)->loadResult();
    }

    /**
     * Return the alias of a menu based on it's menu item id.
     *
     * @param int $alias
     *
     * @return string
     */
    protected function getMenuItemId($alias)
    {
        $db = Factory::getDbo();

        $query = $db->getQuery(true)
            ->select('id')
            ->from('#__menu')
            ->where('alias = ' . $db->quote($alias));

        return (int)$db->setQuery($query)->loadResult();
    }

    /**
     * Return the list of selected categories for the specific menu.
     *
     * @param int $itemId
     *
     * @return array
     */
    protected function getCategoriesFromMenu($itemId)
    {
        $db = Factory::getDbo();

        $query = $db->getQuery(true)
            ->select('params')
            ->from('#__menu')
            ->where('id = ' . $db->quote((int) $itemId));

        $params = $db->setQuery($query)->loadResult();
        $params = new Registry($params);

        $categories = $params->get('category_id', array());

        $list = array();
        if (!empty($categories)) {
            foreach ($categories as $categoryId) {
                $list[] = $this->getCategoryAlias($categoryId);
            }
        }

        return $list;
    }

    /**
     * Returns the category's alias based on the id.
     *
     * @param int $id
     *
     * @return string
     */
    protected function getCategoryAlias($id)
    {
        $db = JFactory::getDBO();

        $query = $db->getQuery(true)
            ->select('alias')
            ->from('#__categories')
            ->where(
                array(
                    'extension IN ("com_osdownloads", "system")',
                    'id = ' . (int)$id
                )
            );

        $alias = $db->setQuery($query)->loadResult();

        return urlencode($alias);
    }

    /**
     * Returns the category's id based on the alias.
     *
     * @param string $alias
     *
     * @return int
     */
    protected function getCategoryId($alias)
    {
        $alias = urldecode($alias);

        $db = JFactory::getDBO();

        $query = $db->getQuery(true)
            ->select('id')
            ->from('#__categories')
            ->where(
                array(
                    'extension IN ("com_osdownloads", "system")',
                    'alias = ' . $db->quote($alias)
                )
            );

        return (int)$db->setQuery($query)->loadResult();
    }

    /**
     * Returns the alias of a file based on the file id.
     *
     * @param int $id
     *
     * @return string
     */
    protected function getFileAlias($id)
    {
        $db = JFactory::getDbo();

        $query = $db->getQuery(true)
            ->select('alias')
            ->from('#__osdownloads_documents')
            ->where('id = ' . $db->quote((int)$id));

        $alias = $db->setQuery($query)->loadResult();

        if (empty($alias)) {
            JLog::add(
                JText::sprintf(
                    'COM_OSDOWNLOADS_ERROR_FILE_NOT_FOUND',
                    $id,
                    'getFileAlias'
                ),
                JLog::WARNING
            );
        }

        return urlencode($alias);
    }

    /**
     * Returns the id of a file based on the file's alias.
     *
     * @param string $alias
     *
     * @return string
     */
    protected function getFileIdFromAlias($alias)
    {
        $alias = urldecode($alias);

        $db = JFactory::getDbo();

        $query = $db->getQuery(true)
            ->select('id')
            ->from('#__osdownloads_documents')
            ->where('alias = ' . $db->quote($alias));

        $id = $db->setQuery($query)->loadResult();

        if (empty($id)) {
            JLog::add(
                JText::sprintf(
                    'COM_OSDOWNLOADS_ERROR_FILE_NOT_FOUND',
                    $alias,
                    'getFileIdFromAlias'
                ),
                JLog::WARNING
            );
        }

        return $id;
    }
}
