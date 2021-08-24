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

use Alledia\OSDownloads\Container;
use Alledia\OSDownloads\Factory;
use Alledia\OSDownloads\Free\Helper\SEF;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Component\Router\RouterBase;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Router\Router;
use Joomla\CMS\Uri\Uri;

defined('_JEXEC') or die();

$includePath = JPATH_ADMINISTRATOR . '/components/com_osdownloads/include.php';
if (is_file($includePath) && include $includePath) {
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
        protected $customSegments = null;

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
        protected $container = null;

        /**
         * @var SEF
         */
        protected $helper = null;

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

            $this->container = Factory::getPimpleContainer();
            $this->helper    = $this->container->helperSEF;

            $router = $this->app::getRouter();

            $router->attachParseRule([$this, 'parseProcessAfter'], Router::PROCESS_AFTER);
        }

        /**
         * Allow to set custom segments for routes.
         *
         * @param string[] $segments
         */
        protected function setCustomSegments(array $segments = [])
        {
            $params = ComponentHelper::getParams('com_osdownloads');

            // Default values
            $default = ['files' => $params->get('route_segment_files') ?: 'files'];

            $this->customSegments = array_merge($default, $segments);
        }

        /**
         * Get a list of custom segments.
         *
         * @return string[]
         */
        protected function getCustomSegments(): array
        {
            if (!isset($this->customSegments)) {
                $this->setCustomSegments();
            }

            return $this->customSegments;
        }

        /**
         * Prepend the menu path to the given path, if a menu exists. Returns the
         * modified array of segments.
         *
         * @param ?int      $fileId
         * @param ?int      $categoryId
         * @param ?string[] $segments
         * @param ?string[] $middlePath
         * @param ?string[] $endPath
         * @param string[]  $query
         *
         * @return string[]
         */
        protected function buildRoutePrependingMenuPath(
            ?int $fileId,
            ?int $categoryId,
            array $segments,
            ?array $middlePath,
            ?array $endPath,
            array &$query
        ): array {
            $skipCategoryAndFileSegments = false;
            $categorySegmentToSkip       = false;

            $menu = $fileId ? $this->helper->getMenuItemForFile($fileId) : null;

            if ($menu) {
                // There is a menu item for the file
                $skipCategoryAndFileSegments = true;

                $query['Itemid'] = $menu->id;
            }

            if (empty($menu)) {
                if ($menu = $this->helper->getMenuItemForCategoryTreeRecursively($categoryId)) {
                    $query['Itemid'] = $menu->id;

                    $menuCatId    = $this->helper->getIdFromLink($menu->link);
                    $menuCategory = $this->helper->getCategory($menuCatId);

                    if (is_object($menuCategory)) {
                        // Related to the root menu, so it won't have a category segment
                        $categorySegmentToSkip = $menuCategory->path;
                    }
                }
            }

            if ($middlePath) {
                $segments = array_merge($segments, $middlePath);
            }

            if ($skipCategoryAndFileSegments) {
                return $segments;
            }

            $segments = $this->helper->appendCategoriesToSegments(
                $segments,
                $categoryId,
                $categorySegmentToSkip
            );

            if ($endPath) {
                $segments = array_merge($segments, $endPath);
            }

            return $segments;
        }

        /**
         * @inheritDoc
         */
        public function build(&$query)
        {
            if ($id = $query['id'] ?? null) {
                unset($query['id']);
            }
            if ($view = $query['view'] ?? null) {
                unset($query['view']);
            }
            if ($layout = $query['layout'] ?? null) {
                unset($query['layout']);
            }
            if ($task = $query['task'] ?? null) {
                unset($query['task']);
            }
            if ($data = $query['data'] ?? null) {
                unset($query['data']);
            }
            if (isset($query['tmpl'])) {
                unset($query['tmpl']);
            }

            $activeMenu = $this->menu->getActive();

            $itemId = $query['Itemid'] ?? $activeMenu->id ?? null;
            $menu   = $itemId ? $this->menu->getItem($itemId) : null;

            if ($view === null && $menu) {
                $view = $menu->query['view'];
            }

            if ($id === null && $menu) {
                $id = $menu->query['id'];
            }

            $segments = [];

            if ($view) {
                // See if there is a menu for this view
                $viewMenu = $this->helper->getMenuItemByQuery(
                    [
                        'view' => $view,
                        'id'   => $id
                    ]
                );

                if (is_object($viewMenu)) {
                    if ($viewMenu->query['id'] == $id) {
                        $query = [
                            'option' => $query['option'],
                            'Itemid' => $viewMenu->id
                        ];

                        if (empty($task)) {
                            return [];
                        }
                    }

                } elseif (is_array($viewMenu) && isset($viewMenu[$id])) {
                    if ($viewMenu[$id]->query['id'] == $id) {
                        $query['Itemid'] = $viewMenu[$id]->id;

                        if (empty($task)) {
                            return $segments;
                        }
                    }

                }
            }

            if ($task) {
                switch ($task) {
                    case 'download':
                    case 'routedownload':
                        $categoryId = $this->helper->getCategoryIdFromFileId($id);

                        $middlePath = [];
                        $endPath    = [];

                        // The task/layout segments
                        $middlePath[] = $task;

                        // Check if the thankyou layout was requested
                        if ('thankyou' === $layout) {
                            $middlePath[] = 'thankyou';
                        }

                        // File segment
                        $endPath[] = $this->helper->getFileAlias($id);

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
            if ($view) {
                switch ($view) {
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

                    case 'item':
                        $categoryId = $this->helper->getCategoryIdFromFileId($id);

                        $middlePath = [];
                        $endPath    = [];

                        $endPath[] = $this->helper->getFileAlias($id);

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

            return $segments;
        }

        /**
         * @inheritDoc
         */
        public function preprocess($query)
        {
            $view   = $query['view'] ?? null;
            $id     = $query['id'] ?? null;
            $itemId = $query['Itemid'] ?? null;

            $parts = $query;
            if ($itemId) {
                if ($view && $menu = $this->menu->getItem($itemId)) {
                    if ($menu->query['view'] != $view) {
                        switch ($view) {
                            case 'item':
                                if ($id && ($viewMenu = $this->helper->getMenuItemForFile($id))) {
                                    $parts['Itemid'] = $viewMenu->id;
                                }
                                break;
                        }
                    }
                }
            } else {
                switch ($view) {
                    case 'downloads':
                        $listMenu = $this->helper->getMenuItemForListOfFiles($id)
                            ?: $this->helper->getMenuItemForListOfFiles(0);

                        if ($listMenu) {
                            $parts['Itemid'] = $listMenu->id;
                        }
                        break;
                }
            }

            return $parts;
        }

        /**
         * @inheritDoc
         * @throws Exception
         */
        public function parse(&$segments)
        {
            $vars = [];

            $firstSegment = reset($segments);
            $lastSegment  = end($segments);
            $tmpSegments  = $segments;
            $activeMenu   = $this->app->getMenu()->getActive();

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

                case 'download':
                case 'routedownload':
                    $vars['task'] = $firstSegment;
                    $vars['tmpl'] = 'component';

                    array_shift($tmpSegments);

                    // Check if we have the thankyou segment
                    if ('thankyou' === reset($tmpSegments)) {
                        $vars['layout'] = 'thankyou';
                        array_shift($tmpSegments);
                    }


                    if (empty($tmpSegments)) {
                        // Look for the file id on the menu item
                        if ($activeMenu) {
                            $vars['id']     = $activeMenu->query['id'];
                            $vars['Itemid'] = $activeMenu->id;

                            $segments   = explode('/', $activeMenu->route);
                            $segments[] = $firstSegment;

                            return $vars;
                        }

                    } else {
                        // Look for the file id using the remaining segments
                        array_pop($tmpSegments);

                        $path = implode('/', $tmpSegments);
                        $file = $this->helper->getFileFromAlias($lastSegment, $path);

                        if (empty($file)) {
                            if ('com_osdownloads' === $activeMenu->query['option']) {
                                if (in_array($activeMenu->query['view'], ['downloads', 'categories'])) {
                                    // Complete the path using the path from the menu
                                    $category = $this->helper->getCategory($activeMenu->query['id']);
                                    $tmpPath  = $category->path;

                                    if (!empty($tmpSegments)) {
                                        $tmpPath .= '/' . implode($tmpSegments);
                                    }

                                    // Try to get the file with the new path
                                    $file = $this->helper->getFileFromAlias($lastSegment, $tmpPath);

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

            if (
                isset($this->customSegments['files'])
                && $this->customSegments['files'] === $lastSegment
            ) {
                $vars['view'] = 'downloads';

                array_pop($segments);

                // Try to detect the category
                $category = $this->helper->getCategoryFromAlias(
                    end($segments),
                    implode('/', $segments)
                );

                if (!empty($category)) {
                    $vars['id'] = $category->id;
                }

                if (empty($category)) {
                    // Try to detect menu to complete the path
                    if ('com_osdownloads' === $activeMenu->query['option']) {
                        if (in_array($activeMenu->query['view'], ['downloads', 'categories'])) {
                            // Complete the path using the path from the menu
                            $category = $this->helper->getCategory($activeMenu->query['id']);
                            $tmpPath  = $category->path;

                            if (!empty($segments)) {
                                $tmpPath .= '/' . implode($segments);
                            }

                            // Try to get the category with the new path
                            $category = $this->helper->getCategoryFromAlias(
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
             * Check the last segment. Is it a single file? Does it have a correct path?
             */
            $tmpSegments = $segments;
            array_pop($tmpSegments);

            $path = implode('/', $tmpSegments);
            $file = $this->helper->getFileFromAlias($lastSegment, $path);

            if (empty($file)) {
                // If no file was found, we try to complete the path based on the menu
                if ($activeMenu && 'com_osdownloads' === $activeMenu->query['option']) {
                    if (in_array($activeMenu->query['view'], ['downloads', 'categories'])) {
                        // Complete the path using the path from the menu
                        $category = $this->helper->getCategory($activeMenu->query['id']);
                        if ($category) {
                            $tmpPath = $category->path;

                            if ($tmpSegments) {
                                $tmpPath .= '/' . implode($tmpSegments);
                            }

                            // Try to get the file with the new path
                            $file = $this->helper->getFileFromAlias($lastSegment, $tmpPath);
                        }
                    }
                }
            }

            if ($file) {
                // Cool, we found a file
                $vars['view'] = 'item';
                $vars['id']   = $file->id;

                return $vars;
            }

            /**
             * Check the last segment. Is it a category list? Does it have a correct path?
             */
            $path = implode('/', $segments);

            $category = $this->helper->getCategoryFromAlias($firstSegment, $path);

            if (is_object($category)) {
                $vars['view'] = 'downloads';
                $vars['id']   = $category->id;

                return $vars;
            }

            throw new Exception(Text::_('COM_OSDOWNLOADS_ERROR_NOT_FOUND'), 404);
        }

        /**
         * @param Router $router
         * @param Uri    $uri
         *
         * @return void
         */
        public function parseProcessAfter(Router $router, Uri $uri)
        {
            // Kinda crazy but needed in Joomla 4
            $uri->setPath(null);
        }
    }
}
