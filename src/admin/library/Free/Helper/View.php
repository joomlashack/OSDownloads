<?php
/**
 * @package   com_osdownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2018 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace Alledia\OSDownloads\Free\Helper;

use Alledia\OSDownloads\Free\Factory as OSDFactory;
use Alledia\OSDownloads\Free\Joomla\Component\Site as FreeComponentSite;
use JFactory;
use JRoute;

defined('_JEXEC') or die();

/**
 * OSDownloads View Helper.
 */
class View
{
    /**
     * Build a list of category breadcrumbs.
     *
     * @param  int     $categoryId
     */
    public function buildCategoryBreadcrumbs($categoryId)
    {
        $paths = array();
        $this->buildPath($paths, $categoryId);

        $app       = JFactory::getApplication();
        $container = OSDFactory::getContainer();

        $pathway    = $app->getPathway();
        $itemID     = $app->input->getInt('Itemid');
        $countPaths = count($paths) - 1;
        $component  = FreeComponentSite::getInstance();

        $pathwayList = $pathway->getPathway();

        for ($i = $countPaths; $i >= 0; $i--) {
            $link   = $container->helperRoute->getFileListRoute($paths[$i]->id, $itemID);
            $route  = JRoute::_($link);
            $exists = false;

            // Check if the current category is already in the pathway, to ignore
            foreach ($pathwayList as $item) {
                if ($item->link === $link) {
                    $exists = true;
                    break;
                }
            }

            if (!$exists) {
                $pathway->addItem($paths[$i]->title, $route);
            }
        }
    }

    /**
     * Build a list of file breadcrumbs.
     *
     * @param  object  $file
     */
    public function buildFileBreadcrumbs($file)
    {
        $container = OSDFactory::getContainer();

        // Check if the current file has a menu item
        $menu = $container->app->getMenu()->getActive();

        if (is_object($menu)) {
            if ('item' === $menu->query['view'] && $menu->query['id'] === $file->id) {
                // Yes, so do nothing
                return;
            }
        }

        $this->buildCategoryBreadcrumbs($file->cate_id);

        $app       = JFactory::getApplication();
        $container = OSDFactory::getContainer();

        $pathway = $app->getPathway();
        $itemID  = $app->input->getInt('Itemid');

        $pathwayList = $pathway->getPathway();

        $link   = $container->helperRoute->getViewItemRoute($file->id, $itemID);
        $route  = JRoute::_($link);
        $exists = false;

        if (!$exists) {
            $pathway->addItem($file->name, $link);
        }
    }

    /**
     * Build an inverse recurcive list of paths for categories' breadcrumbs.
     *
     * @param  array &$paths
     * @param  int   $categoryId
     */
    protected function buildPath(&$paths, $categoryId)
    {
        if (empty($categoryId)) {
            return;
        }

        $db = JFactory::getDbo();

        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__categories')
            ->where(
                array(
                    'extension = ' . $db->quote('com_osdownloads'),
                    'id = ' . $db->quote((int) $categoryId)
                )
            );
        $category = $db->setQuery($query)->loadObject();

        if (!empty($category)) {
            $paths[] = $category;

            if ($category->parent_id) {
                $this->buildPath($paths, $category->parent_id);
            }
        }
    }
}
