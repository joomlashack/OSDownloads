<?php
/**
 * @package   OSDownloads
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2016 Open Source Training, LLC. All rights reserved
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
            buildCategoryPath($categories, $query['id']);
            for ($i = count($categories) - 1; $i >= 0; $i--) {
                $segments[] = $categories[$i];
            }
        }
    }

    if ($view == "item" && isset($query['id'])) {
        $segments[] = "file";
        $segments[] = getAliasFromId($query['id']);

        unset($query['id']);
    }

    $task = '';
    if (isset($query['task'])) {
        $task = $query['task'];

        // Route Download Link
        if ($task === 'routedownload') {
            unset($query['task']);
            $segments[] = 'routedownload';

            if (isset($query['tmpl']) && $query['tmpl'] === 'component') {
                unset($query['tmpl']);
            }

            if (isset($query['id'])) {
                $segments[] = getAliasFromId($query['id']);
                unset($query['id']);
            }
        }

        // Download link
        if ($task === 'download') {
            unset($query['task']);
            $segments[] = 'download';

            if (isset($query['tmpl']) && $query['tmpl'] === 'component') {
                unset($query['tmpl']);
            }

            if (isset($query['id'])) {
                $segments[] = getAliasFromId($query['id']);
                unset($query['id']);
            }
        }
    }

    return $segments;
}

function OSDownloadsParseRoute($segments)
{
    $app = JFactory::getApplication();

    $query = array();
    if ($segments[0] === "category") {
        $query['view'] = 'downloads';
    }

    // Support partially routed URLS
    if (!isset($segments[1]) && in_array($segments[0], array('file', 'routedownload', 'download'))) {
        $segments[1] = $app->input->getInt('id');
    }

    if ($segments[0] === "file") {
        $query['view'] = 'item';
        $query['id']   = getIdFromAlias($segments[1]);
    }

    if ($segments[0] === "routedownload") {
        $query['task'] = 'routedownload';
        $query['tmpl'] = 'component';
        $query['id']   = getIdFromAlias($segments[1]);
    }

    if ($segments[0] === "download") {
        $query['view'] = 'item';
        $query['task'] = 'download';
        $query['tmpl'] = 'component';
        $query['id']   = getIdFromAlias($segments[1]);
    }

    return $query;
}

function buildCategoryPath(&$segments, $id)
{
    if (!$id) {
        return;
    }

    $db = JFactory::getDBO();

    $sql = $db->getQuery(true)
        ->select('*')
        ->from('#__categories')
        ->where('extension = ' . $db->quote('com_osdownloads'))
        ->where('id = ' . (int)$id);
    $db->setQuery($sql);

    $category = $db->loadObject();

    if ($category) {
        $segments[] = $category->alias;
    }

    if ($category && $category->parent_id) {
        buildCategoryPath($segments, $category->parent_id);
    }
}

function sanitizeAlias($value)
{
    jimport('joomla.filter.output');

    return JFilterOutput::stringURLSafe($value);
}

function getIdFromAlias($alias)
{
    $db = JFactory::getDBO();

    $alias = sanitizeAlias($alias);

    $sql = $db->getQuery(true)
        ->select('id')
        ->from('#__osdownloads_documents')
        ->where('alias = ' . $db->quote($alias));
    $db->setQuery($sql);

    return $db->loadResult();
}

function getAliasFromId($id)
{
    $db = JFactory::getDBO();

    $id = (int) $id;

    $sql = $db->getQuery(true)
        ->select("alias")
        ->from('#__osdownloads_documents')
        ->where('id = ' . $db->quote($id));
    $db->setQuery($sql);

    return $db->loadResult();
}
