<?php
/**
 * @package   OSDownloads
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2015 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

function OSDownloadsBuildRoute(&$query)
{
    $segments = array();

    $view = "";

    if (isset($query['view'])) {
        $view = $query['view'];
        unset($query['view']);
    }

    if ($view == "downloads") {
        $segments[] = "category";
        $categories = array();
        if (isset($query['id'])) {
            buildPath($categories, $query['id']);
            for ($i = count($categories) - 1; $i >= 0; $i--) {
                $segments[] = $categories[$i];
            }
        }
    }
    if ($view == "item" && isset($query['id'])) {
        $segments[] = "file";
        $db = JFactory::getDBO();
        $db->setQuery("SELECT alias FROM `#__osdownloads_documents` WHERE  id = " . $query['id']);
        $segments[] = $db->loadResult();
    }

    return $segments;
}

function buildPath(& $segments, $id)
{
    if (!$id) {
        return;
    }

    $db = JFactory::getDBO();
    $db->setQuery("SELECT * FROM `#__categories` WHERE extension='com_osdownloads' AND id = " . (int)$id);
    $cate = $db->loadObject();

    if ($cate) {
        $segments[] = $cate->alias;
    }

    if ($cate && $cate->parent_id) {
        buildPath($segments, $cate->parent_id);
    }
}

function OSDownloadsParseRoute($segments)
{
    $vars = array();
    if ($segments[0] == "category") {
        $vars['view'] = 'downloads';
    }

    if ($segments[0] == "file") {
        $vars['view'] = 'item';
    }

    return $vars;
}
