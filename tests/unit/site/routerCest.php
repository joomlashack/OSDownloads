<?php
require 'src/site/router.php';

use Codeception\Example;
use Codeception\Util\Stub;

class RouterCest
{
    protected $router;

    /**
     * Prepares data before each test.
     *
     * @param  UnitTester $I
     */
    public function _before(UnitTester $I)
    {
        global $files;
        global $categories;
        global $menus;

        /**
         *
         * Dummy category set
         *
         */
        $categories = [
            1 => (object) [
                'id'        => 1,
                'alias'     => 'category-1',
                'path'      => 'category-1',
                'parent_id' => null,
            ],
            2 => (object) [
                'id'        => 2,
                'alias'     => 'category-2',
                'path'      => 'category-1/category-2',
                'parent_id' => 1,
            ],
            3 => (object) [
                'id'        => 3,
                'alias'     => 'category-3',
                'path'      => 'category-1/category-2/category-3',
                'parent_id' => 2,
            ],
            4 => (object) [
                'id'        => 4,
                'alias'     => 'category-4',
                'path'      => 'category-4',
                'parent_id' => null,
            ],
        ];
        // Add index by alias
        $categories['category-1'] = &$categories[1];
        $categories['category-2'] = &$categories[2];
        $categories['category-3'] = &$categories[3];
        $categories['category-4'] = &$categories[4];

        /**
         *
         * Dummy file set
         *
         */
        $files = [
            1 => (object) [
                'id'      => 1,
                'alias'   => 'file-1',
                'cate_id' => 1,
            ],
            2 => (object) [
                'id'      => 2,
                'alias'   => 'file-2',
                'cate_id' => 2,
            ],
            3 => (object) [
                'id'      => 3,
                'alias'   => 'file-3',
                'cate_id' => 3,
            ],
            4 => (object) [
                'id'      => 4,
                'alias'   => 'file-4',
                'cate_id' => 4,
            ],
        ];
        $files['file-1'] = &$files[1];
        $files['file-2'] = &$files[2];
        $files['file-3'] = &$files[3];
        $files['file-4'] = &$files[4];

        /**
         *
         * Dummy menu set
         *
         * The menu set is specified inside each test.
         *
         */
        $menus = [];

        /**
         *
         * Dummy router
         *
         */
        $container = new class {
            public $helperSEF;
        };

        $container->helperSEF = new class {
            public function appendCategoriesToSegments($segments, $catId, $categorySegmentToSkip = null)
            {
                global $categories;

                if (empty($catId)) {
                    return;
                }

                $category = $categories[$catId];

                $path = $category->path;

                if (!empty($categorySegmentToSkip)) {
                    $path = str_replace($categorySegmentToSkip, '', $path);
                }

                if (!empty($path)) {
                    $segments = array_merge(
                        $segments,
                        explode('/', $path)
                    );
                }

                // Remove empty segments
                $segments = array_filter($segments);

                return $segments;
            }

            public function appendMenuPathToSegments($segments, $menu)
            {
                if (is_array($segments) && isset($menu->path)) {
                    $segments = array_merge(
                        $segments,
                        explode('/', $menu->path)
                    );
                }

                return $segments;
            }

            public function getCategoryFromAlias($alias)
            {
                global $categories;

                if (isset($categories[$alias])) {
                    return $categories[$alias];
                }

                return false;
            }

            public function getCategory($id)
            {
                global $categories;

                if (isset($categories[$id])) {
                    return $categories[$id];
                }

                return false;
            }

            public function getCategoryIdFromFileId($alias)
            {
                global $files;

                if (isset($files[$alias])) {
                    return $files[$alias]->cate_id;
                }

                return false;
            }

            public function getCategoryFromFileId($fileId)
            {
                global $categories;
                global $files;

                if (!isset($files[$fileId])) {
                    return false;
                }

                $catId = $files[$fileId]->cate_id;

                if (empty($catId)) {
                    return null;
                }

                return $categories[$catId];
            }

            public function getFileAlias($id)
            {
                global $files;

                if (isset($files[$id])) {
                    return $files[$id]->alias;
                }

                return false;
            }

            public function getFileIdFromAlias($alias)
            {
                global $files;

                if (isset($files[$alias])) {
                    return $files[$alias]->id;
                }

                return false;
            }

            public function getFileFromAlias($alias)
            {
                global $files;

                if (isset($files[$alias])) {
                    return $files[$alias];
                }

                return false;
            }

            public function getMenuItemForFile($id)
            {
                global $files;
                global $menus;

                if (isset($menus['file-' . $id])) {
                    return $menus['file-' . $id];
                }

                return false;
            }

            public function getMenuItemsForComponent()
            {
                global $menus;

                $filteredMenus = array();

                foreach ($menus as $menu) {
                    if (substr_count($menu->link, 'option=com_osdownloads')) {
                        $filteredMenus[] = $menu;
                    }
                }

                return $filteredMenus;
            }

            public function getMenuItemsFromPath($path)
            {
                global $menus;

                foreach ($menus as $menu) {
                    if ($menu->path === $path) {
                        return $menu;
                    }
                }

                return null;
            }

            public function getMenuItemForCategoryTreeRecursively($categoryId)
            {
                global $categories;
                global $menus;

                if (isset($menus['category-' . $categoryId])) {
                    return $menus['category-' . $categoryId];
                }

                if (isset($categories[$categoryId])) {
                    $category = $categories[$categoryId];

                    if (!empty($category->parent_id)) {
                        return $this->getMenuItemForCategoryTreeRecursively($category->parent_id);
                    }
                }

                // Check the root category, since no other category seems to be on a menu
                if ($categoryId > 0) {
                    return $this->getMenuItemForCategoryTreeRecursively(0);
                }

                return false;
            }

            public function getIdFromLink($link)
            {
                $vars = [];

                parse_str($link, $vars);

                return $vars['id'];
            }

            public function getMenuItemByQuery($query)
            {
                global $menus;

                if (!empty($menus)) {
                    foreach ($menus as $menuItem) {
                        if (array_intersect($query, $menuItem->query) == $query) {
                            return $menuItem;
                        }
                    }
                }

                return false;
            }

            public function getMenuItemById($id)
            {
                global $menus;

                if (isset($menus[$id])) {
                    return $menus[$id];
                }

                return false;
            }
        };

        $container->app = new class
        {
            public function getMenu()
            {
                $menu = new class
                {
                    public function getActive()
                    {
                        global $menus;
                        global $activeItemId;

                        foreach ($menus as $menu) {
                            if ($menu->id == $activeItemId) {
                                return $menu;
                            }
                        }

                        return false;
                    }
                };

                return $menu;
            }
        };

        /**
         * A mock for the router with the custom data set.
         *
         * @var OsdownloadsRouter
         */
        $this->router  = Stub::make(
            'OsdownloadsRouter',
            [
                'container'      => $container,
                'customSegments' => ['files' => 'files'],
            ]
        );
    }



    /*=======================================
    =            CUSTOM SEGMENTS            =
    =======================================*/

    /**
     * Should return the correct segment calling the method getCustomSegments
     * for the file list segment.
     *
     * @example {"custom_segment": "files"}
     * @example {"custom_segment": "customA"}
     * @example {"custom_segment": "custom_B"}
     */
    public function getCustomSegmentsForTheRoutes(UnitTester $I, Example $example)
    {
        // Force a new set of custom segments
        $mock = Stub::copy(
            $this->router,
            [
                'customSegments' => [
                    'files' => $example['custom_segment'],
                ]
            ]
        );

        $segments = $mock->getCustomSegments();

        $I->assertArrayHasKey('files', $segments);
        $I->assertEquals($example['custom_segment'], $segments['files']);
    }

    /**
     * Try to customize the "files" segment
     *
     * @example {"segments": {"files": "files"}, "expected": "files"}
     * @example {"segments": {"files": "customA"}, "expected": "customA"}
     * @example {"segments": {"files": "custom_B"}, "expected": "custom_B"}
     * @example {"segments": {"other": "any_one"}, "expected": "files"}
     */
    public function setCustomSegmentsForTheRoutes(UnitTester $I, Example $example)
    {
        $this->router->setCustomSegments($example['segments']);

        $customSegments = $this->router->getCustomSegments();

        $I->assertArrayHasKey('files', $customSegments);
        $I->assertEquals($example['expected'], $customSegments['files']);
    }

    /*=====  End of CUSTOM SEGMENTS  ======*/



    /*=====================================
    =            GENERAL TESTS            =
    =====================================*/

    /**
     * Try to build route segments and check if the used query elements were
     * removed from the query.
     */
    public function checkIfQueryVarsWhereRemovedAfterBuildRoute(UnitTester $I)
    {
        $query = [
            'view'      => 'item',
            'layout'    => 'edit',
            'id'        => '1',
            'task'      => 'download',
            'tmpl'      => 'component',
            'extra_arg' => '1',
        ];

        $segments = $this->router->build($query);

        $I->assertArrayNotHasKey('view', $query, 'The key view should be removed from the query');
        $I->assertArrayNotHasKey('layout', $query, 'The key layout should be removed from the query');
        $I->assertArrayNotHasKey('id', $query, 'The key id should be removed from the query');
        $I->assertArrayNotHasKey('task', $query, 'The key task should be removed from the query');
        $I->assertArrayNotHasKey('tmpl', $query, 'The key tmpl should be removed from the query');

        $I->assertArrayHasKey('extra_arg', $query, 'Extra arguments should no be removed from the query');
    }

    /*=====  End of GENERAL TESTS  ======*/



    /*======================================
    =            DOWNLOAD TASKS            =
    ======================================*/

    /**
     * Try to build route segments for the routedownload and download tasks without menus.
     *
     * @example {"task": "routedownload", "id": "1", "route": "routedownload/category-1/file-1", "layout": "any-layout"}
     * @example {"task": "routedownload", "id": "1", "route": "routedownload/category-1/file-1"}
     * @example {"task": "routedownload", "id": "2", "route": "routedownload/category-1/category-2/file-2"}
     * @example {"task": "routedownload", "id": "3", "route": "routedownload/category-1/category-2/category-3/file-3"}
     * @example {"task": "routedownload", "id": "4", "route": "routedownload/category-4/file-4"}
     *
     * @example {"task": "routedownload", "id": "1", "route": "routedownload/thankyou/category-1/file-1", "layout": "thankyou"}
     * @example {"task": "routedownload", "id": "2", "route": "routedownload/thankyou/category-1/category-2/file-2", "layout": "thankyou"}
     * @example {"task": "routedownload", "id": "3", "route": "routedownload/thankyou/category-1/category-2/category-3/file-3", "layout": "thankyou"}
     * @example {"task": "routedownload", "id": "4", "route": "routedownload/thankyou/category-4/file-4", "layout": "thankyou"}
     *
     * @example {"task": "download", "id": "1", "route": "download/category-1/file-1", "layout": "any-layout"}
     * @example {"task": "download", "id": "1", "route": "download/category-1/file-1"}
     * @example {"task": "download", "id": "2", "route": "download/category-1/category-2/file-2"}
     * @example {"task": "download", "id": "3", "route": "download/category-1/category-2/category-3/file-3"}
     * @example {"task": "download", "id": "4", "route": "download/category-4/file-4"}
     *
     * @example {"task": "download", "id": "1", "route": "download/thankyou/category-1/file-1", "layout": "thankyou"}
     * @example {"task": "download", "id": "2", "route": "download/thankyou/category-1/category-2/file-2", "layout": "thankyou"}
     * @example {"task": "download", "id": "3", "route": "download/thankyou/category-1/category-2/category-3/file-3", "layout": "thankyou"}
     * @example {"task": "download", "id": "4", "route": "download/thankyou/category-4/file-4", "layout": "thankyou"}
     */
    public function buildRouteForDownloadTasksWithoutMenuItems(UnitTester $I, Example $example)
    {
        $query = [
            'task' => $example['task'],
            'id'   => $example['id'],
        ];

        if (isset($example['layout'])) {
            $query['layout'] = $example['layout'];
        }

        $route = implode('/', $this->router->build($query));

        $I->assertEquals($example['route'], $route);
    }

    /**
     * Try to build route segments for the routedownload and download tasks with menus for categories.
     *
     * Menu items tree:
     *   - menu-category-root
     *   - menu-category-1
     *       - menu-category-2
     *
     * @example {"task": "routedownload", "id": "1", "route": "routedownload/file-1", "layout": "any-layout"}
     * @example {"task": "routedownload", "id": "1", "route": "routedownload/file-1"}
     * @example {"task": "routedownload", "id": "2", "route": "routedownload/file-2"}
     * @example {"task": "routedownload", "id": "3", "route": "routedownload/category-3/file-3"}
     * @example {"task": "routedownload", "id": "4", "route": "routedownload/category-4/file-4"}
     *
     * @example {"task": "routedownload", "id": "1", "route": "routedownload/thankyou/file-1", "layout": "thankyou"}
     * @example {"task": "routedownload", "id": "2", "route": "routedownload/thankyou/file-2", "layout": "thankyou"}
     * @example {"task": "routedownload", "id": "3", "route": "routedownload/thankyou/category-3/file-3", "layout": "thankyou"}
     * @example {"task": "routedownload", "id": "4", "route": "routedownload/thankyou/category-4/file-4", "layout": "thankyou"}
     *
     * @example {"task": "download", "id": "1", "route": "download/file-1", "layout": "any-layout"}
     * @example {"task": "download", "id": "1", "route": "download/file-1"}
     * @example {"task": "download", "id": "2", "route": "download/file-2"}
     * @example {"task": "download", "id": "3", "route": "download/category-3/file-3"}
     * @example {"task": "download", "id": "4", "route": "download/category-4/file-4"}
     *
     * @example {"task": "download", "id": "1", "route": "download/thankyou/file-1", "layout": "thankyou"}
     * @example {"task": "download", "id": "2", "route": "download/thankyou/file-2", "layout": "thankyou"}
     * @example {"task": "download", "id": "3", "route": "download/thankyou/category-3/file-3", "layout": "thankyou"}
     * @example {"task": "download", "id": "4", "route": "download/thankyou/category-4/file-4", "layout": "thankyou"}
     */
    public function buildRouteForDownloadTasksWithMenuItemForCategory(UnitTester $I, Example $example)
    {
        // Menus
        global $menus;

        $menus = [
            'category-0' => (object) [
                'id'        => '100',
                'alias'     => 'menu-category-root',
                'path'      => 'menu-category-root',
                'link'      => 'index.php?option=com_osdownloads&view=downloads&id=0',
                'parent_id' => '1',
                'published' => '1',
                'access'    => '1',
                'type'      => 'component',
                'client_id' => '0',
            ],
            'category-1' => (object) [
                'id'        => '101',
                'alias'     => 'menu-category-1',
                'path'      => 'menu-category-1',
                'link'      => 'index.php?option=com_osdownloads&view=downloads&id=1',
                'parent_id' => '1',
                'published' => '1',
                'access'    => '1',
                'type'      => 'component',
                'client_id' => '0',
            ],
            'category-2' => (object) [
                'id'        => '102',
                'alias'     => 'menu-category-2',
                'path'      => 'menu-category-1/menu-category-2',
                'link'      => 'index.php?option=com_osdownloads&view=downloads&id=2',
                'parent_id' => '101',
                'published' => '1',
                'access'    => '1',
                'type'      => 'component',
                'client_id' => '0',
            ],
        ];

        // Query
        $query = [
            'task' => $example['task'],
            'id'   => $example['id'],
        ];

        if (isset($example['layout'])) {
            $query['layout'] = $example['layout'];
        }


        $route = implode('/', $this->router->build($query));

        $I->assertEquals($example['route'], $route);
    }

    /**
     * Try to build route segments for the routedownload and download tasks with menus for the file.
     *
     * Menu items tree:
     *   - menu-category-1
     *       - menu-file-1
     *       - menu-category-2
     *   - menu-file-2
     *   - menu-other-extension-1
     *       - menu-file-3
     *
     * @example {"task": "routedownload", "id": "1", "route": "routedownload", "layout": "any-layout"}
     * @example {"task": "routedownload", "id": "1", "route": "routedownload"}
     * @example {"task": "routedownload", "id": "2", "route": "routedownload"}
     * @example {"task": "routedownload", "id": "3", "route": "routedownload"}
     *
     * @example {"task": "routedownload", "id": "1", "route": "routedownload/thankyou", "layout": "thankyou"}
     * @example {"task": "routedownload", "id": "2", "route": "routedownload/thankyou", "layout": "thankyou"}
     * @example {"task": "routedownload", "id": "3", "route": "routedownload/thankyou", "layout": "thankyou"}
     *
     * @example {"task": "download", "id": "1", "route": "download", "layout": "any-layout"}
     * @example {"task": "download", "id": "1", "route": "download"}
     * @example {"task": "download", "id": "2", "route": "download"}
     * @example {"task": "download", "id": "3", "route": "download"}
     *
     * @example {"task": "download", "id": "1", "route": "download/thankyou", "layout": "thankyou"}
     * @example {"task": "download", "id": "2", "route": "download/thankyou", "layout": "thankyou"}
     * @example {"task": "download", "id": "3", "route": "download/thankyou", "layout": "thankyou"}
     */
    public function buildRouteForDownloadTasksWithMenuItemForTheFile(UnitTester $I, Example $example)
    {
        // Menus
        global $menus;

        $menus = [
            'category-1' => (object) [
                'id'        => '101',
                'alias'     => 'menu-category-1',
                'path'      => 'menu-category-1',
                'link'      => 'index.php?option=com_osdownloads&view=downloads&id=1',
                'parent_id' => '1',
                'published' => '1',
                'access'    => '1',
                'type'      => 'component',
                'client_id' => '0',
            ],

            'category-2' => (object) [
                'id'        => '102',
                'alias'     => 'menu-category-2',
                'path'      => 'menu-category-1/menu-category-2',
                'link'      => 'index.php?option=com_osdownloads&view=downloads&id=2',
                'parent_id' => '101',
                'published' => '1',
                'access'    => '1',
                'type'      => 'component',
                'client_id' => '0',
            ],

            'file-1' => (object) [
                'id'        => '103',
                'alias'     => 'menu-file-1',
                'path'      => 'menu-category-1/menu-file-1',
                'link'      => 'index.php?option=com_osdownloads&view=item&id=1',
                'parent_id' => '101',
                'published' => '1',
                'access'    => '1',
                'type'      => 'component',
                'client_id' => '0',
            ],

            'file-2' => (object) [
                'id'        => '104',
                'alias'     => 'menu-file-2',
                'path'      => 'menu-file-2',
                'link'      => 'index.php?option=com_osdownloads&view=item&id=2',
                'parent_id' => '1',
                'published' => '1',
                'access'    => '1',
                'type'      => 'component',
                'client_id' => '0',
            ],

            'other-extension-1' => (object) [
                'id'        => '105',
                'alias'     => 'menu-other-extension-1',
                'path'      => 'menu-other-extension-1',
                'link'      => 'index.php?option=com_3rdext&view=home&id=4',
                'parent_id' => '1',
                'published' => '1',
                'access'    => '1',
                'type'      => 'component',
                'client_id' => '0',
            ],

            'file-3' => (object) [
                'id'        => '106',
                'alias'     => 'menu-file-3',
                'path'      => 'menu-file-3',
                'link'      => 'index.php?option=com_osdownloads&view=item&id=3',
                'parent_id' => '105',
                'published' => '1',
                'access'    => '1',
                'type'      => 'component',
                'client_id' => '0',
            ],
        ];

        $query = [
            'task' => $example['task'],
            'id'   => $example['id'],
        ];

        if (isset($example['layout'])) {
            $query['layout'] = $example['layout'];
        }

        $route = implode('/', $this->router->build($query));

        $I->assertEquals($example['route'], $route);
    }

    /**
     * Try to parse route segments for the routedownload and download tasks routes.
     *
     * @example {"task": "routedownload", "id": "1", "route": "routedownload/category-1/file-1"}
     * @example {"task": "routedownload", "id": "2", "route": "routedownload/category-1/category-2/file-2"}
     * @example {"task": "routedownload", "id": "3", "route": "routedownload/category-1/category-2/category-3/file-3"}
     * @example {"task": "routedownload", "id": "4", "route": "routedownload/category-4/file-4"}
     *
     * @example {"task": "download", "id": "1", "route": "download/category-1/file-1"}
     * @example {"task": "download", "id": "2", "route": "download/category-1/category-2/file-2"}
     * @example {"task": "download", "id": "3", "route": "download/category-1/category-2/category-3/file-3"}
     * @example {"task": "download", "id": "4", "route": "download/category-4/file-4"}
     */
    public function parseRouteSegmentsForRoutedownloadAndDownloadTasksWithoutMenu(UnitTester $I, Example $example)
    {
        // Menus
        global $menus;

        $menus = [];

        $segments = explode('/', $example['route']);

        $vars = $this->router->parse($segments);

        $I->assertArrayHasKey('task', $vars);
        $I->assertEquals($example['task'], $vars['task']);

        $I->assertArrayHasKey('id', $vars);
        $I->assertEquals($example['id'], $vars['id']);

        $I->assertArrayHasKey('tmpl', $vars);
        $I->assertEquals('component', $vars['tmpl']);
    }

    /**
     * Try to parse route segments for the routedownload and download tasks routes.
     *
     * Menu items tree:
     * - menu-category-1
     * - menu-category-3
     *     - menu-file-3
     * - menu-file-4
     *
     * @example {"task": "routedownload", "id": "1", "activeItemId": 101, "route": "routedownload/file-1"}
     * @example {"task": "routedownload", "id": "2", "activeItemId": 101, "route": "routedownload/category-2/file-2"}
     * @example {"task": "routedownload", "id": "3", "activeItemId": 103, "route": "routedownload"}
     * @example {"task": "routedownload", "id": "4", "activeItemId": 104, "route": "routedownload"}
     *
     * @example {"task": "download", "id": "1", "activeItemId": 101, "route": "download/file-1"}
     * @example {"task": "download", "id": "2", "activeItemId": 101, "route": "download/category-2/file-2"}
     * @example {"task": "download", "id": "3", "activeItemId": 103, "route": "download"}
     * @example {"task": "download", "id": "4", "activeItemId": 104, "route": "download"}
     */
    public function parseRouteSegmentsForRoutedownloadAndDownloadTasksWithMenu(UnitTester $I, Example $example)
    {
        // Menus
        global $menus;
        global $activeItemId;

        $activeItemId = $example['activeItemId'];

        $menus = [
            'category-1' => (object) [
                'id'        => '101',
                'alias'     => 'menu-category-1',
                'path'      => 'menu-category-1',
                'link'      => 'index.php?option=com_osdownloads&view=downloads&id=1',
                'parent_id' => '1',
                'published' => '1',
                'access'    => '1',
                'type'      => 'component',
                'client_id' => '0',
                'query'     => ['view' => 'downloads', 'id' => 1],
            ],

            'category-3' => (object) [
                'id'        => '102',
                'alias'     => 'menu-category-3',
                'path'      => 'menu-category-3',
                'link'      => 'index.php?option=com_osdownloads&view=downloads&id=3',
                'parent_id' => '1',
                'published' => '1',
                'access'    => '1',
                'type'      => 'component',
                'client_id' => '0',
                'query'     => ['view' => 'downloads', 'id' => 3],
            ],

            'file-3' => (object) [
                'id'        => '103',
                'alias'     => 'menu-file-3',
                'path'      => 'menu-category-3/menu-file-3',
                'link'      => 'index.php?option=com_osdownloads&view=item&id=3',
                'parent_id' => '102',
                'published' => '1',
                'access'    => '1',
                'type'      => 'component',
                'client_id' => '0',
                'query'     => ['view' => 'item', 'id' => 3],
            ],

            'file-4' => (object) [
                'id'        => '104',
                'alias'     => 'menu-file-4',
                'path'      => 'menu-file-4',
                'link'      => 'index.php?option=com_osdownloads&view=item&id=4',
                'parent_id' => '1',
                'published' => '1',
                'access'    => '1',
                'type'      => 'component',
                'client_id' => '0',
                'query'     => ['view' => 'item', 'id' => 4],
            ],
        ];

        $segments = explode('/', $example['route']);

        $vars = $this->router->parse($segments);

        $I->assertArrayHasKey('task', $vars);
        $I->assertEquals($example['task'], $vars['task']);

        $I->assertArrayHasKey('id', $vars);
        $I->assertEquals($example['id'], $vars['id']);

        $I->assertArrayHasKey('tmpl', $vars);
        $I->assertEquals('component', $vars['tmpl']);
    }

    /*=====  End of DOWNLOAD TASKS  ======*/


    /*==========================================
    =            CONFIRM EMAIL TASK            =
    ==========================================*/

    /**
     * Try to build route segments for the confirmemail task.
     *
     * @example {"task": "confirmemail", "data": "889ec873b0e085c1724ec0ca560d3cfe", "route": "confirmemail/889ec873b0e085c1724ec0ca560d3cfe"}
     * @example {"task": "confirmemail", "data": "4d43e82c9633e2c57df71042d9976135", "view": "any-view", "route": "confirmemail/4d43e82c9633e2c57df71042d9976135"}
     */
    public function buildRouteSegmentsForConfirmemailTask(UnitTester $I, Example $example)
    {
        $query = [
            'task' => $example['task'],
            'data' => $example['data'],
        ];

        if (isset($example['view'])) {
            $query['view'] = $example['view'];
        }

        $route = implode('/', $this->router->build($query));

        $I->assertEquals($example['route'], $route);
    }

    /**
     * Try to parse route segments for the confirmemail task.
     *
     * @example {"task": "confirmemail", "data": "889ec873b0e085c1724ec0ca560d3cfe", "route": "confirmemail/889ec873b0e085c1724ec0ca560d3cfe"}
     * @example {"task": "confirmemail", "data": "4d43e82c9633e2c57df71042d9976135", "route": "confirmemail/4d43e82c9633e2c57df71042d9976135"}
     */
    public function parseRouteSegmentsForConfirmEmailTask(UnitTester $I, Example $example)
    {
        $segments = explode('/', $example['route']);

        $vars = $this->router->parse($segments);

        $I->assertArrayHasKey('task', $vars);
        $I->assertEquals($example['task'], $vars['task']);

        $I->assertArrayHasKey('data', $vars);
        $I->assertEquals($example['data'], $vars['data']);

        $I->assertArrayHasKey('tmpl', $vars);
        $I->assertEquals('component', $vars['tmpl']);
    }

    /*=====  End of CONFIRM EMAIL TASK  ======*/



    /*=========================================
    =            SINGLE ITEM PAGES            =
    =========================================*/

    /**
     * Try to build route segments for a single file without menu items
     *
     * @example {"view": "item", "id": 1, "route": "category-1/file-1"}
     * @example {"view": "item", "id": 2, "route": "category-1/category-2/file-2"}
     * @example {"view": "item", "id": 3, "route": "category-1/category-2/category-3/file-3"}
     * @example {"view": "item", "id": 4, "route": "category-4/file-4"}
     */
    public function buildRouteSegmentsForASingleFileWithoutMenuItem(UnitTester $I, Example $example)
    {
        $query = [
            'view'   => $example['view'],
            'id'     => $example['id'],
        ];

        $route = implode('/', $this->router->build($query));

        $I->assertEquals($example['route'], $route);
    }

    /**
     * Try to build route segments for a single file with menu items
     *
     * Menu items tree:
     *   - menu-category-1
     *       - menu-category-3
     *           - menu-file-3
     *   - menu-file-4
     *
     *
     * @example {"view": "item", "id": 1, "activeItemId": 101, "route": "file-1"}
     * @example {"view": "item", "id": 2, "activeItemId": 101, "route": "category-2/file-2"}
     * @example {"view": "item", "id": 3, "activeItemId": 103, "route": ""}
     * @example {"view": "item", "id": 4, "activeItemId": 104, "route": ""}
     */
    public function buildRouteSegmentsForASingleFileWithMenuItem(UnitTester $I, Example $example)
    {
        // Menus
        global $menus;
        global $activeItemId;

        $activeItemId = $example['activeItemId'];

        $menus = [
            'category-1' => (object) [
                'id'        => '101',
                'alias'     => 'menu-category-1',
                'path'      => 'menu-category-1',
                'link'      => 'index.php?option=com_osdownloads&view=downloads&id=1',
                'parent_id' => '1',
                'published' => '1',
                'access'    => '1',
                'type'      => 'component',
                'client_id' => '0',
                'query'     => ['view' => 'downloads', 'id' => 1],
            ],

            'category-3' => (object) [
                'id'        => '103',
                'alias'     => 'menu-category-3',
                'path'      => 'menu-category-1/menu-category-3',
                'link'      => 'index.php?option=com_osdownloads&view=downloads&id=3',
                'parent_id' => '101',
                'published' => '1',
                'access'    => '1',
                'type'      => 'component',
                'client_id' => '0',
                'query'     => ['view' => 'downloads', 'id' => 3],
            ],

            'file-3' => (object) [
                'id'        => '104',
                'alias'     => 'menu-file-3',
                'path'      => 'menu-category-1/menu-category-3/menu-file-3',
                'link'      => 'index.php?option=com_osdownloads&view=item&id=3',
                'parent_id' => '103',
                'published' => '1',
                'access'    => '1',
                'type'      => 'component',
                'client_id' => '0',
                'query'     => ['view' => 'item', 'id' => 3],
            ],

            'file-4' => (object) [
                'id'        => '105',
                'alias'     => 'menu-file-4',
                'path'      => 'menu-file-4',
                'link'      => 'index.php?option=com_osdownloads&view=item&id=4',
                'parent_id' => '1',
                'published' => '1',
                'access'    => '1',
                'type'      => 'component',
                'client_id' => '0',
                'query'     => ['view' => 'item', 'id' => 4],
            ],
        ];

        $query = [
            'view'   => $example['view'],
            'id'     => $example['id'],
        ];

        $route = implode('/', $this->router->build($query));

        $I->assertEquals($example['route'], $route);
    }

    /**
     * Try to build route segments for a single file but only based on the item id.
     *
     * Menu items tree:
     *   - menu-category-1
     *       - menu-category-3
     *           - menu-file-3
     *   - menu-file-4
     *
     * @example {"Itemid": "104", "id": 3, "route": ""}
     * @example {"Itemid": "105", "id": 4, "route": ""}
     */
    public function buildRouteSegmentsForASingleFileBasedOnItemId(UnitTester $I, Example $example)
    {
        // Menus
        global $menus;

        $menus = [
            'category-1' => (object) [
                'id'        => '101',
                'alias'     => 'menu-category-1',
                'path'      => 'menu-category-1',
                'link'      => 'index.php?option=com_osdownloads&view=downloads&id=1',
                'parent_id' => '1',
                'published' => '1',
                'access'    => '1',
                'type'      => 'component',
                'component' => 'com_osdownloads',
                'client_id' => '0',
                'query'     => ['view' => 'downloads', 'id' => '1'],
            ],

            'category-3' => (object) [
                'id'        => '103',
                'alias'     => 'menu-category-3',
                'path'      => 'menu-category-1/menu-category-3',
                'link'      => 'index.php?option=com_osdownloads&view=downloads&id=3',
                'parent_id' => '101',
                'published' => '1',
                'access'    => '1',
                'type'      => 'component',
                'component' => 'com_osdownloads',
                'client_id' => '0',
                'query'     => ['view' => 'downloads', 'id' => '3'],
            ],

            'file-3' => (object) [
                'id'        => '104',
                'alias'     => 'menu-file-3',
                'path'      => 'menu-category-1/menu-category-3/menu-file-3',
                'link'      => 'index.php?option=com_osdownloads&view=item&id=3',
                'parent_id' => '103',
                'published' => '1',
                'access'    => '1',
                'type'      => 'component',
                'component' => 'com_osdownloads',
                'client_id' => '0',
                'query'     => ['view' => 'item', 'id' => '3'],
            ],

            'file-4' => (object) [
                'id'        => '105',
                'alias'     => 'menu-file-4',
                'path'      => 'menu-file-4',
                'link'      => 'index.php?option=com_osdownloads&view=item&id=4',
                'parent_id' => '1',
                'published' => '1',
                'access'    => '1',
                'type'      => 'component',
                'component' => 'com_osdownloads',
                'client_id' => '0',
                'query'     => ['view' => 'item', 'id' => '4'],
            ],
        ];

        $query = [
            'Itemid' => $example['Itemid'],
        ];

        $route = implode('/', $this->router->build($query));

        $I->assertEquals($example['route'], $route);
    }

    /**
     * Try to parse route segments for a single file.
     *
     * @example {"id": 1, "route": "category-1/file-1"}
     * @example {"id": 2, "route": "category-1/category-2/file-2"}
     * @example {"id": 3, "route": "category-1/category-2/category-3/file-3"}
     * @example {"id": 4, "route": "category-4/file-4"}
     */
    public function parseRouteSegmentsForASingleFileWithoutMenuItem(UnitTester $I, Example $example)
    {
        $segments = explode('/', $example['route']);

        $vars = $this->router->parse($segments);

        $I->assertArrayHasKey('view', $vars);
        $I->assertEquals('item', $vars['view']);

        $I->assertArrayHasKey('id', $vars);
        $I->assertEquals($example['id'], $vars['id']);

        $I->assertArrayNotHasKey('layout', $vars);
        $I->assertArrayNotHasKey('tmpl', $vars);
    }

    /**
     * Try to parse route segments for a single file.
     *
     * Menu items tree:
     *   - menu-category-1
     *       - menu-category-3
     *           - menu-file-3
     *   - menu-file-4
     *
     * @example {"id": 1, "activeItemId": 101, "route": "file-1"}
     * @example {"id": 2, "activeItemId": 101, "route": "file-2"}
     * @example {"id": 3, "activeItemId": 104, "route": ""}
     * @example {"id": 4, "activeItemId": 105, "route": ""}
     */
    public function parseRouteSegmentsForASingleFileWithMenuItem(UnitTester $I, Example $example)
    {
        // Menus
        global $menus;
        global $activeItemId;

        $activeItemId = $example['activeItemId'];

        $menus = [
            'category-1' => (object) [
                'id'        => '101',
                'alias'     => 'menu-category-1',
                'path'      => 'menu-category-1',
                'link'      => 'index.php?option=com_osdownloads&view=downloads&id=1',
                'parent_id' => '1',
                'published' => '1',
                'access'    => '1',
                'type'      => 'component',
                'component' => 'com_osdownloads',
                'client_id' => '0',
                'query'     => ['view' => 'downloads', 'id' => '1'],
            ],

            'category-3' => (object) [
                'id'        => '103',
                'alias'     => 'menu-category-3',
                'path'      => 'menu-category-1/menu-category-3',
                'link'      => 'index.php?option=com_osdownloads&view=downloads&id=3',
                'parent_id' => '101',
                'published' => '1',
                'access'    => '1',
                'type'      => 'component',
                'component' => 'com_osdownloads',
                'client_id' => '0',
                'query'     => ['view' => 'downloads', 'id' => '3'],
            ],

            'file-3' => (object) [
                'id'        => '104',
                'alias'     => 'menu-file-3',
                'path'      => 'menu-category-1/menu-category-3/menu-file-3',
                'link'      => 'index.php?option=com_osdownloads&view=item&id=3',
                'parent_id' => '103',
                'published' => '1',
                'access'    => '1',
                'type'      => 'component',
                'component' => 'com_osdownloads',
                'client_id' => '0',
                'query'     => ['view' => 'item', 'id' => '3'],
            ],

            'file-4' => (object) [
                'id'        => '105',
                'alias'     => 'menu-file-4',
                'path'      => 'menu-file-4',
                'link'      => 'index.php?option=com_osdownloads&view=item&id=4',
                'parent_id' => '1',
                'published' => '1',
                'access'    => '1',
                'type'      => 'component',
                'component' => 'com_osdownloads',
                'client_id' => '0',
                'query'     => ['view' => 'item', 'id' => '4'],
            ],
        ];

        $segments = explode('/', $example['route']);

        $vars = $this->router->parse($segments);

        $I->assertArrayHasKey('view', $vars);
        $I->assertEquals('item', $vars['view']);

        $I->assertArrayHasKey('id', $vars);
        $I->assertEquals($example['id'], $vars['id']);

        $I->assertArrayNotHasKey('layout', $vars);
        $I->assertArrayNotHasKey('tmpl', $vars);
    }

    /*=====  End of Section SINGLE ITEM PAGES  ======*/



    /*=====================================
    =            LIST OF FILES            =
    =====================================*/

    /**
     * Try to build route segments for a list of files.
     *
     * @example {"view": "downloads", "id": 1, "route": "category-1/files"}
     * @example {"view": "downloads", "id": 2, "route": "category-1/category-2/files"}
     * @example {"view": "downloads", "id": 3, "route": "category-1/category-2/category-3/files"}
     * @example {"view": "downloads", "id": 4, "route": "category-4/files"}
     */
    public function buildRouteSegmentsForAListOfFilesWithoutMenuItems(UnitTester $I, Example $example)
    {
        $query = [
            'view'   => $example['view'],
            'id'     => $example['id'],
        ];

        $route = implode('/', $this->router->build($query));

        $I->assertEquals($example['route'], $route);
    }

    /**
     * Try to build route segments for a list of files.
     *
     * Menu items tree:
     *   - menu-category-1
     *       - menu-category-3
     *           - menu-file-3
     *
     * @example {"view": "downloads", "id": 1, "route": "menu-category-1/files"}
     * @example {"view": "downloads", "id": 2, "route": "menu-category-1/category-2/files"}
     * @example {"view": "downloads", "id": 3, "route": "menu-category-1/menu-category-3/files"}
     * @example {"view": "downloads", "id": 4, "route": "category-4/files"}
     */
    public function buildRouteSegmentsForAListOfFilesWithMenuItems(UnitTester $I, Example $example)
    {
        // Menus
        global $menus;

        $menus = [
            'category-1' => (object) [
                'id'        => '101',
                'alias'     => 'menu-category-1',
                'path'      => 'menu-category-1',
                'link'      => 'index.php?option=com_osdownloads&view=downloads&id=1',
                'parent_id' => '1',
                'published' => '1',
                'access'    => '1',
                'type'      => 'component',
                'component' => 'com_osdownloads',
                'client_id' => '0',
                'query'     => ['view' => 'downloads', 'id' => '1'],
            ],

            'category-3' => (object) [
                'id'        => '103',
                'alias'     => 'menu-category-3',
                'path'      => 'menu-category-1/menu-category-3',
                'link'      => 'index.php?option=com_osdownloads&view=downloads&id=3',
                'parent_id' => '101',
                'published' => '1',
                'access'    => '1',
                'type'      => 'component',
                'component' => 'com_osdownloads',
                'client_id' => '0',
                'query'     => ['view' => 'downloads', 'id' => '3'],
            ],

            'file-3' => (object) [
                'id'        => '104',
                'alias'     => 'menu-file-3',
                'path'      => 'menu-category-1/menu-category-3/menu-file-3',
                'link'      => 'index.php?option=com_osdownloads&view=item&id=3',
                'parent_id' => '103',
                'published' => '1',
                'access'    => '1',
                'type'      => 'component',
                'component' => 'com_osdownloads',
                'client_id' => '0',
                'query'     => ['view' => 'item', 'id' => '3'],
            ],
        ];

        $query = [
            'view'   => $example['view'],
            'id'     => $example['id'],
        ];

        $route = implode('/', $this->router->build($query));

        $I->assertEquals($example['route'], $route);
    }

    /**
     * Try to build route segments for a list of files based on the item id.
     *
     * Menu items tree:
     *   - menu-category-1
     *       - menu-category-2
     *           - menu-category-3
     *   - menu-category-4
     *
     * @example {"Itemid": "101", "route": "menu-category-1/files"}
     * @example {"Itemid": "102", "route": "menu-category-1/menu-category-2/files"}
     * @example {"Itemid": "103", "route": "menu-category-1/menu-category-2/menu-category-3/files"}
     * @example {"Itemid": "104", "route": "menu-category-4/files"}
     */
    public function buildRouteSegmentsForAListOfFilesBasedOnItemId(UnitTester $I, Example $example)
    {
        // Menus
        global $menus;

        $menus = [
            'category-1' => (object) [
                'id'        => '101',
                'alias'     => 'menu-category-1',
                'path'      => 'menu-category-1',
                'link'      => 'index.php?option=com_osdownloads&view=downloads&id=1',
                'parent_id' => '1',
                'published' => '1',
                'access'    => '1',
                'type'      => 'component',
                'component' => 'com_osdownloads',
                'client_id' => '0',
                'query'     => ['view' => 'downloads', 'id' => '1'],
            ],

            'category-2' => (object) [
                'id'        => '102',
                'alias'     => 'menu-category-2',
                'path'      => 'menu-category-1/menu-category-2',
                'link'      => 'index.php?option=com_osdownloads&view=downloads&id=2',
                'parent_id' => '101',
                'published' => '1',
                'access'    => '1',
                'type'      => 'component',
                'component' => 'com_osdownloads',
                'client_id' => '0',
                'query'     => ['view' => 'downloads', 'id' => '2'],
            ],

            'category-3' => (object) [
                'id'        => '103',
                'alias'     => 'menu-category-3',
                'path'      => 'menu-category-1/menu-category-2/menu-category-3',
                'link'      => 'index.php?option=com_osdownloads&view=downloads&id=3',
                'parent_id' => '102',
                'published' => '1',
                'access'    => '1',
                'type'      => 'component',
                'component' => 'com_osdownloads',
                'client_id' => '0',
                'query'     => ['view' => 'downloads', 'id' => '3'],
            ],

            'category-4' => (object) [
                'id'        => '104',
                'alias'     => 'menu-category-4',
                'path'      => 'menu-category-4',
                'link'      => 'index.php?option=com_osdownloads&view=downloads&id=4',
                'parent_id' => '1',
                'published' => '1',
                'access'    => '1',
                'type'      => 'component',
                'component' => 'com_osdownloads',
                'client_id' => '0',
                'query'     => ['view' => 'downloads', 'id' => '4'],
            ],
        ];

        $query = [
            'Itemid' => $example['Itemid'],
        ];

        $route = implode('/', $this->router->build($query));

        $I->assertEquals($example['route'], $route);
    }

    /**
     * Try to build route segments for a list of files with custom segment.
     *
     * @example {"view": "downloads", "id": 1, "segment": "files_custom_segment", "route": "category-1/files_custom_segment"}
     * @example {"view": "downloads", "id": 2, "segment": "files_custom_segment2", "route": "category-1/category-2/files_custom_segment2"}
     * @example {"view": "downloads", "id": 3, "segment": "files_custom_segment3", "route": "category-1/category-2/category-3/files_custom_segment3"}
     */
    public function buildRouteSegmentsForAListOfFilesWithCustomSegmentWithoutMenuItems(UnitTester $I, Example $example)
    {
        $query = [
            'view'   => $example['view'],
            'id'     => $example['id'],
        ];

        // Force custom segments
        $mock = Stub::copy(
            $this->router,
            [
                'customSegments' => ['files' => $example['segment']],
            ]
        );

        $route = implode('/', $mock->build($query));

        $I->assertEquals($example['route'], $route);

        // Restore the default segment
        $this->router->setCustomSegments(['files' => 'files']);
    }

    /**
     * Try to build route segments for a list of files with custom segment.
     *
     * Menu items tree:
     *   - menu-category-1
     *       - menu-category-2
     *           - menu-category-3
     *   - menu-category-4
     *
     * @example {"view": "downloads", "id": 1, "segment": "files_custom_segment", "route": "menu-category-1/files_custom_segment"}
     * @example {"view": "downloads", "id": 2, "segment": "files_custom_segment2", "route": "menu-category-1/menu-category-2/files_custom_segment2"}
     * @example {"view": "downloads", "id": 3, "segment": "files_custom_segment3", "route": "menu-category-1/menu-category-2/menu-category-3/files_custom_segment3"}
     * @example {"view": "downloads", "id": 4, "segment": "files_custom_segment4", "route": "menu-category-4/files_custom_segment4"}
     */
    public function buildRouteSegmentsForAListOfFilesWithCustomSegmentWithMenuItems(UnitTester $I, Example $example)
    {
        // Menus
        global $menus;

        $menus = [
            'category-1' => (object) [
                'id'        => '101',
                'alias'     => 'menu-category-1',
                'path'      => 'menu-category-1',
                'link'      => 'index.php?option=com_osdownloads&view=downloads&id=1',
                'parent_id' => '1',
                'published' => '1',
                'access'    => '1',
                'type'      => 'component',
                'component' => 'com_osdownloads',
                'client_id' => '0',
                'query'     => ['view' => 'downloads', 'id' => '1'],
            ],

            'category-2' => (object) [
                'id'        => '102',
                'alias'     => 'menu-category-2',
                'path'      => 'menu-category-1/menu-category-2',
                'link'      => 'index.php?option=com_osdownloads&view=downloads&id=2',
                'parent_id' => '101',
                'published' => '1',
                'access'    => '1',
                'type'      => 'component',
                'component' => 'com_osdownloads',
                'client_id' => '0',
                'query'     => ['view' => 'downloads', 'id' => '2'],
            ],

            'category-3' => (object) [
                'id'        => '103',
                'alias'     => 'menu-category-3',
                'path'      => 'menu-category-1/menu-category-2/menu-category-3',
                'link'      => 'index.php?option=com_osdownloads&view=downloads&id=3',
                'parent_id' => '102',
                'published' => '1',
                'access'    => '1',
                'type'      => 'component',
                'component' => 'com_osdownloads',
                'client_id' => '0',
                'query'     => ['view' => 'downloads', 'id' => '3'],
            ],

            'category-4' => (object) [
                'id'        => '104',
                'alias'     => 'menu-category-4',
                'path'      => 'menu-category-4',
                'link'      => 'index.php?option=com_osdownloads&view=downloads&id=4',
                'parent_id' => '1',
                'published' => '1',
                'access'    => '1',
                'type'      => 'component',
                'component' => 'com_osdownloads',
                'client_id' => '0',
                'query'     => ['view' => 'downloads', 'id' => '4'],
            ],
        ];

        $query = [
            'view'   => $example['view'],
            'id'     => $example['id'],
        ];

        // Force custom segments
        $mock = Stub::copy(
            $this->router,
            [
                'customSegments' => ['files' => $example['segment']],
            ]
        );

        $route = implode('/', $mock->build($query));

        $I->assertEquals($example['route'], $route);

        // Restore the default segment
        $this->router->setCustomSegments(['files' => 'files']);
    }

    /**
     * Try to parse route segments for a list of files.
     *
     * @example {"id": 0, "route": "files"}
     * @example {"id": 1, "route": "category-1/files"}
     * @example {"id": 2, "route": "category-1/category-2/files"}
     * @example {"id": 3, "route": "category-1/category-2/category-3/files"}
     * @example {"id": 4, "route": "category-4/files"}
     */
    public function parseRouteSegmentsForAListOfFilesWithoutMenuItems(UnitTester $I, Example $example)
    {
        $segments = explode('/', $example['route']);

        $vars = $this->router->parse($segments);

        $I->assertArrayHasKey('view', $vars);
        $I->assertEquals('downloads', $vars['view']);

        $I->assertArrayHasKey('id', $vars);
        $I->assertEquals($example['id'], $vars['id']);

        $I->assertArrayNotHasKey('layout', $vars);
        $I->assertArrayNotHasKey('tmpl', $vars);
    }


    /**
     * Try to parse route segments for a list of files.
     *
     * Menu items tree:
     *   - menu-category-1
     *       - menu-category-2
     *   - menu-category-4
     *
     * @example {"id": 0, "route": "files"}
     * @example {"id": 1, "route": "menu-category-1/files"}
     * @example {"id": 2, "route": "menu-category-1/menu-category-2/files"}
     * @example {"id": 3, "route": "menu-category-1/menu-category-2/category-3/files"}
     * @example {"id": 4, "route": "menu-category-4/files"}
     */
    public function parseRouteSegmentsForAListOfFilesWithMenuItems(UnitTester $I, Example $example)
    {

        // Menus
        global $menus;

        $menus = [
            'category-1' => (object) [
                'id'        => '101',
                'alias'     => 'menu-category-1',
                'path'      => 'menu-category-1',
                'link'      => 'index.php?option=com_osdownloads&view=downloads&id=1',
                'parent_id' => '1',
                'published' => '1',
                'access'    => '1',
                'type'      => 'component',
                'component' => 'com_osdownloads',
                'client_id' => '0',
                'query'     => ['view' => 'downloads', 'id' => '1'],
            ],

            'category-2' => (object) [
                'id'        => '102',
                'alias'     => 'menu-category-2',
                'path'      => 'menu-category-1/menu-category-2',
                'link'      => 'index.php?option=com_osdownloads&view=downloads&id=2',
                'parent_id' => '101',
                'published' => '1',
                'access'    => '1',
                'type'      => 'component',
                'component' => 'com_osdownloads',
                'client_id' => '0',
                'query'     => ['view' => 'downloads', 'id' => '2'],
            ],

            'category-3' => (object) [
                'id'        => '103',
                'alias'     => 'menu-category-3',
                'path'      => 'menu-category-1/menu-category-2/menu-category-3',
                'link'      => 'index.php?option=com_osdownloads&view=downloads&id=3',
                'parent_id' => '102',
                'published' => '1',
                'access'    => '1',
                'type'      => 'component',
                'component' => 'com_osdownloads',
                'client_id' => '0',
                'query'     => ['view' => 'downloads', 'id' => '3'],
            ],

            'category-4' => (object) [
                'id'        => '104',
                'alias'     => 'menu-category-4',
                'path'      => 'menu-category-4',
                'link'      => 'index.php?option=com_osdownloads&view=downloads&id=4',
                'parent_id' => '1',
                'published' => '1',
                'access'    => '1',
                'type'      => 'component',
                'component' => 'com_osdownloads',
                'client_id' => '0',
                'query'     => ['view' => 'downloads', 'id' => '4'],
            ],
        ];

        $segments = explode('/', $example['route']);

        $vars = $this->router->parse($segments);

        $I->assertArrayHasKey('view', $vars);
        $I->assertEquals('downloads', $vars['view']);

        $I->assertArrayHasKey('id', $vars);
        $I->assertEquals($example['id'], $vars['id']);

        $I->assertArrayNotHasKey('layout', $vars);
        $I->assertArrayNotHasKey('tmpl', $vars);
    }

    /**
     * Try to parse route segments for a list of files with custom segment
     *
     * @example {"id": 0, "segment": "files_custom_segment", "route": "files_custom_segment"}
     * @example {"id": 1, "segment": "files_custom_segment2", "route": "category-1/files_custom_segment2"}
     * @example {"id": 2, "segment": "files_custom_segment2", "route": "category-1/category-2/files_custom_segment2"}
     * @example {"id": 3, "segment": "files_custom_segment3", "route": "category-1/category-2/category-3/files_custom_segment3"}
     */
    public function parseRouteSegmentsForAListOfFilesWithCustomSegment(UnitTester $I, Example $example)
    {
        $segments = explode('/', $example['route']);

        // Force custom segments
        $mock = Stub::copy(
            $this->router,
            [
                'customSegments' => ['files' => $example['segment']],
            ]
        );

        $vars = $mock->parse($segments);

        $I->assertArrayHasKey('view', $vars);
        $I->assertEquals('downloads', $vars['view']);

        $I->assertArrayHasKey('id', $vars);
        $I->assertEquals($example['id'], $vars['id']);

        $I->assertArrayNotHasKey('layout', $vars);
        $I->assertArrayNotHasKey('tmpl', $vars);
    }

    /*=====  End of LIST OF FILES  ======*/



    /*==========================================
    =            LIST OF CATEGORIES            =
    ==========================================*/

    /**
     * Try to build route segments for a list of categories. (Pro version)
     *
     * @example {"view": "categories", "id": 1, "route": "category-1"}
     * @example {"view": "categories", "id": 2, "route": "category-1/category-2"}
     * @example {"view": "categories", "id": 3, "route": "category-1/category-2/category-3"}
     */
    public function buildRouteSegmentsForAListOfCategoriesWithoutMenuItems(UnitTester $I, Example $example)
    {
        $query = [
            'view'   => $example['view'],
            'id'     => $example['id'],
        ];

        $route = implode('/', $this->router->build($query));

        $I->assertEquals($example['route'], $route);
    }

    /**
     * Try to build route segments for a list of categories. (Pro version)
     *
     * Menu items tree:
     *   - menu-category-1
     *       - menu-category-2
     *           - menu-category-3
     *   - menu-3rdparty-1
     *       - menu-category-4
     *
     * @example {"view": "categories", "id": 1, "route": "menu-category-1"}
     * @example {"view": "categories", "id": 2, "route": "menu-category-1/menu-category-2"}
     * @example {"view": "categories", "id": 3, "route": "menu-category-1/menu-category-2/menu-category-3"}
     * @example {"view": "categories", "id": 4, "route": "menu-3rdparty-1/menu-category-4"}
     */
    public function buildRouteSegmentsForAListOfCategoriesWithMenuItems(UnitTester $I, Example $example)
    {
        // Menus
        global $menus;

        $menus = [
            'category-1' => (object) [
                'id'        => '101',
                'alias'     => 'menu-category-1',
                'path'      => 'menu-category-1',
                'link'      => 'index.php?option=com_osdownloads&view=categories&id=1',
                'parent_id' => '1',
                'published' => '1',
                'access'    => '1',
                'type'      => 'component',
                'component' => 'com_osdownloads',
                'client_id' => '0',
                'query'     => ['view' => 'categories', 'id' => '1'],
            ],

            'category-2' => (object) [
                'id'        => '102',
                'alias'     => 'menu-category-2',
                'path'      => 'menu-category-1/menu-category-2',
                'link'      => 'index.php?option=com_osdownloads&view=categories&id=2',
                'parent_id' => '101',
                'published' => '1',
                'access'    => '1',
                'type'      => 'component',
                'component' => 'com_osdownloads',
                'client_id' => '0',
                'query'     => ['view' => 'categories', 'id' => '2'],
            ],

            'category-3' => (object) [
                'id'        => '103',
                'alias'     => 'menu-category-3',
                'path'      => 'menu-category-1/menu-category-2/menu-category-3',
                'link'      => 'index.php?option=com_osdownloads&view=categories&id=3',
                'parent_id' => '102',
                'published' => '1',
                'access'    => '1',
                'type'      => 'component',
                'component' => 'com_osdownloads',
                'client_id' => '0',
                'query'     => ['view' => 'categories', 'id' => '3'],
            ],

            '3rdparty-1' => (object) [
                'id'        => '104',
                'alias'     => 'menu-3rdparty-1',
                'path'      => 'menu-3rdparty-1',
                'link'      => 'index.php?option=com_otherextension&view=home&id=1',
                'parent_id' => '1',
                'published' => '1',
                'access'    => '1',
                'type'      => 'component',
                'component' => 'com_otherextension',
                'client_id' => '0',
                'query'     => ['view' => 'home', 'id' => '1'],
            ],

            'category-4' => (object) [
                'id'        => '105',
                'alias'     => 'menu-category-4',
                'path'      => 'menu-3rdparty-1/menu-category-4',
                'link'      => 'index.php?option=com_osdownloads&view=categories&id=4',
                'parent_id' => '1',
                'published' => '1',
                'access'    => '1',
                'type'      => 'component',
                'component' => 'com_osdownloads',
                'client_id' => '0',
                'query'     => ['view' => 'categories', 'id' => '4'],
            ],
        ];

        $query = [
            'view' => $example['view'],
            'id'   => $example['id'],
        ];

        $route = implode('/', $this->router->build($query));

        $I->assertEquals($example['route'], $route);
    }

    /**
     * Try to parse route segments for a list of categories. (Pro version)
     *
     * @example {"id": 0, "route": ""}
     * @example {"id": 1, "route": "category-1"}
     * @example {"id": 2, "route": "category-1/category-2"}
     * @example {"id": 3, "route": "category-1/category-2/category-3"}
     * @example {"id": 4, "route": "category-4"}
     */
    public function parseRouteSegmentsForAListOfCategoriesWithoutMenuItems(UnitTester $I, Example $example)
    {
        $segments = explode('/', $example['route']);

        $vars = $this->router->parse($segments);

        $I->assertArrayHasKey('view', $vars);
        $I->assertEquals('categories', $vars['view']);

        $I->assertArrayHasKey('id', $vars);
        $I->assertEquals($example['id'], $vars['id']);

        $I->assertArrayNotHasKey('layout', $vars);
        $I->assertArrayNotHasKey('tmpl', $vars);
    }

    /**
     * Try to parse route segments for a list of categories. (Pro version)
     *
     * Menu items tree:
     *   - menu-category-0
     *   - menu-category-1
     *       - menu-category-2
     *           - menu-category-3
     *   - menu-3rdparty-1
     *       - menu-category-4
     *
     * @example {"view": "categories", "id": 0, "route": "menu-category-0"}
     * @example {"view": "categories", "id": 1, "route": "menu-category-1"}
     * @example {"view": "categories", "id": 2, "route": "menu-category-1/menu-category-2"}
     * @example {"view": "categories", "id": 3, "route": "menu-category-1/menu-category-2/menu-category-3"}
     * @example {"view": "categories", "id": 4, "route": "menu-3rdparty-1/menu-category-4"}
     */
    public function parseRouteSegmentsForAListOfCategoriesWithMenuItems(UnitTester $I, Example $example)
    {
        // Menus
        global $menus;

        $menus = [
            'category-0' => (object) [
                'id'        => '100',
                'alias'     => 'menu-category-0',
                'path'      => 'menu-category-0',
                'link'      => 'index.php?option=com_osdownloads&view=categories&id=0',
                'parent_id' => '1',
                'published' => '1',
                'access'    => '1',
                'type'      => 'component',
                'component' => 'com_osdownloads',
                'client_id' => '0',
                'query'     => ['view' => 'categories', 'id' => '0'],
            ],

            'category-1' => (object) [
                'id'        => '101',
                'alias'     => 'menu-category-1',
                'path'      => 'menu-category-1',
                'link'      => 'index.php?option=com_osdownloads&view=categories&id=1',
                'parent_id' => '1',
                'published' => '1',
                'access'    => '1',
                'type'      => 'component',
                'component' => 'com_osdownloads',
                'client_id' => '0',
                'query'     => ['view' => 'categories', 'id' => '1'],
            ],

            'category-2' => (object) [
                'id'        => '102',
                'alias'     => 'menu-category-2',
                'path'      => 'menu-category-1/menu-category-2',
                'link'      => 'index.php?option=com_osdownloads&view=categories&id=2',
                'parent_id' => '101',
                'published' => '1',
                'access'    => '1',
                'type'      => 'component',
                'component' => 'com_osdownloads',
                'client_id' => '0',
                'query'     => ['view' => 'categories', 'id' => '2'],
            ],

            'category-3' => (object) [
                'id'        => '103',
                'alias'     => 'menu-category-3',
                'path'      => 'menu-category-1/menu-category-2/menu-category-3',
                'link'      => 'index.php?option=com_osdownloads&view=categories&id=3',
                'parent_id' => '102',
                'published' => '1',
                'access'    => '1',
                'type'      => 'component',
                'component' => 'com_osdownloads',
                'client_id' => '0',
                'query'     => ['view' => 'categories', 'id' => '3'],
            ],

            '3rdparty-1' => (object) [
                'id'        => '104',
                'alias'     => 'menu-3rdparty-1',
                'path'      => 'menu-3rdparty-1',
                'link'      => 'index.php?option=com_otherextension&view=home&id=1',
                'parent_id' => '1',
                'published' => '1',
                'access'    => '1',
                'type'      => 'component',
                'component' => 'com_otherextension',
                'client_id' => '0',
                'query'     => ['view' => 'home', 'id' => '1'],
            ],

            'category-4' => (object) [
                'id'        => '105',
                'alias'     => 'menu-category-4',
                'path'      => 'menu-3rdparty-1/menu-category-4',
                'link'      => 'index.php?option=com_osdownloads&view=categories&id=4',
                'parent_id' => '1',
                'published' => '1',
                'access'    => '1',
                'type'      => 'component',
                'component' => 'com_osdownloads',
                'client_id' => '0',
                'query'     => ['view' => 'categories', 'id' => '4'],
            ],
        ];

        $segments = explode('/', $example['route']);

        $vars = $this->router->parse($segments);

        $I->assertArrayHasKey('view', $vars);
        $I->assertEquals('categories', $vars['view']);

        $I->assertArrayHasKey('id', $vars);
        $I->assertEquals($example['id'], $vars['id']);

        $I->assertArrayNotHasKey('layout', $vars);
        $I->assertArrayNotHasKey('tmpl', $vars);
    }

    /*=====  End of CATEGORIES LIST  ======*/



    /*======================================
    =            Invalid Routes            =
    ======================================*/

    /**
     * Try to parse route segments of invalid URLs, expecting the error 404.
     *
     * List of categories: Invalid category alias
     * @example {"route": "invalid_category_alias"}
     * @example {"route": "23"}
     * @example {"route": "category-1/category-2_invalid"}
     * @example {"route": "category-1/category-2/invalid1"}
     */
    public function getError404ForListOfCategoriesWithInvalidCategory(UnitTester $I, Example $example)
    {
        $I->expectException(
            new Exception('COM_OSDOWNLOADS_ERROR_NOT_FOUND'),
            function () use ($example) {
                $segments = explode('/', $example['route']);

                $vars = $this->router->parse($segments);
            }
        );
    }

    /**
     * Try to parse route segments of invalid URLs, expecting the error 404.
     *
     * List of categories: Valid category alias, but invalid path
     * @example {"route": "category-2/category-1"}
     * @example {"route": "category-1_invalid/category-2"}
     * @example {"route": "category-1/category-2_invalid/category-3"}
     * @example {"route": "category-2/category-3"}
     */
    public function getError404ForListOfCategoriesWithValidCategoryButInvalidPath(UnitTester $I, Example $example)
    {
        $I->expectException(
            new Exception('COM_OSDOWNLOADS_ERROR_NOT_FOUND'),
            function () use ($example) {
                $segments = explode('/', $example['route']);

                $vars = $this->router->parse($segments);
            }
        );
    }

    /**
     * Try to parse route segments of invalid URLs, expecting the error 404.
     *
     * List of files: Invalid category alias
     * @example {"route": "3/items"}
     * @example {"route": "category_invalid/items"}
     * @example {"route": "category-1/category_invalid/items"}
     * @example {"route": "category-1/category-2/category_invalid/items"}
     */
    public function getError404ForListOfFilesWithInvalidCategoryAlias(UnitTester $I, Example $example)
    {
        $I->expectException(
            new Exception('COM_OSDOWNLOADS_ERROR_NOT_FOUND'),
            function () use ($example) {
                $segments = explode('/', $example['route']);

                $vars = $this->router->parse($segments);
            }
        );
    }

    /**
     * Try to parse route segments of invalid URLs, expecting the error 404.
     *
     * List of files: Valid category alias, but invalid path
     * @example {"route": "category_invalid/items"}
     * @example {"route": "category_invalid/category-1/items"}
     * @example {"route": "category_invalid/category-2/items"}
     * @example {"route": "category-1/category-2_invalid/category-3/items"}
     */
    public function getError404ForListOfFilesWithCategoryButInvalidPath(UnitTester $I, Example $example)
    {
        $I->expectException(
            new Exception('COM_OSDOWNLOADS_ERROR_NOT_FOUND'),
            function () use ($example) {
                $segments = explode('/', $example['route']);

                $vars = $this->router->parse($segments);
            }
        );
    }

    /**
     * Try to parse route segments of invalid URLs, expecting the error 404.
     *
     * Single file: Invalid file alias
     * @example {"route": "files/file_invalid"}
     * @example {"route": "category-1/files/file_invalid"}
     * @example {"route": "category-1/category-2/files/file_invalid"}
     * @example {"route": "category-1/category-2/category-3/files/file_invalid"}
     */
    public function getError404ForSingleFileWithInvalidAlias(UnitTester $I, Example $example)
    {
        $I->expectException(
            new Exception('COM_OSDOWNLOADS_ERROR_NOT_FOUND'),
            function () use ($example) {
                $segments = explode('/', $example['route']);

                $vars = $this->router->parse($segments);
            }
        );
    }

    /**
     * Try to parse route segments of invalid URLs, expecting the error 404.
     *
     * Single file: Valid file alias, but invalid path
     * @example {"route": "files/file-1"}
     * @example {"route": "category-1/files/file-1"}
     * @example {"route": "category-1/category-2/files/file-1"}
     * @example {"route": "category-1/category-2_invalid/files/file-1"}
     * @example {"route": "category-1/category-2/category-3_invalid/files/file-1"}
     */
    public function getError404ForSingleFileWithInvalidPath(UnitTester $I, Example $example)
    {
        $I->expectException(
            new Exception('COM_OSDOWNLOADS_ERROR_NOT_FOUND'),
            function () use ($example) {
                $segments = explode('/', $example['route']);

                $vars = $this->router->parse($segments);
            }
        );
    }

    /**
     * Try to parse route segments of invalid URLs, expecting the error 404.
     *
     * Download: Invalid file alias
     * @example {"route": "download/file_invalid"}
     * @example {"route": "download/category-1/file_invalid"}
     * @example {"route": "download/category-1/category-2/category-3/file_invalid"}
     */
    public function getError404ForDownloadingInvalidFile(UnitTester $I, Example $example)
    {
        $I->expectException(
            new Exception('COM_OSDOWNLOADS_ERROR_NOT_FOUND'),
            function () use ($example) {
                $segments = explode('/', $example['route']);

                $vars = $this->router->parse($segments);
            }
        );
    }

    /**
     * Try to parse route segments of invalid URLs, expecting the error 404.
     *
     * Download: Valid file alias, but invalid path
     * @example {"route": "download/file-1"}
     * @example {"route": "category-1/download/file-1"}
     * @example {"route": "category-3/download/file-1"}
     * @example {"route": "category-1/category-2/download/file-1"}
     * @example {"route": "category-1/category-2/category-3_invalid/download/file-1"}
     */
    public function getError404ForDownloadingWithValidFileButInvalidPath(UnitTester $I, Example $example)
    {
        $I->expectException(
            new Exception('COM_OSDOWNLOADS_ERROR_NOT_FOUND'),
            function () use ($example) {
                $segments = explode('/', $example['route']);

                $vars = $this->router->parse($segments);
            }
        );
    }

    /**
     * Try to parse route segments of invalid URLs, expecting the error 404.
     *
     * Route Download: Invalid file alias
     * @example {"route": "routedownload/file_invalid"}
     * @example {"route": "category-1/category-2/category-3/routedownload/file_invalid"}
     */
    public function getError404ForRoutingDownloadWithInvalidFile(UnitTester $I, Example $example)
    {
        $I->expectException(
            new Exception('COM_OSDOWNLOADS_ERROR_NOT_FOUND'),
            function () use ($example) {
                $segments = explode('/', $example['route']);

                $vars = $this->router->parse($segments);
            }
        );
    }

    /**
     * Try to parse route segments of invalid URLs, expecting the error 404.
     *
     * Route Download: Valid file alias, but invalid path
     * @example {"route": "routedownload/file-1"}
     * @example {"route": "category-1/routedownload/file-1"}
     * @example {"route": "category-3/routedownload/file-1"}
     * @example {"route": "category-1/category-2/routedownload/file-1"}
     * @example {"route": "category-1/category-2/category-3_invalid/routedownload/file-1"}
     */
    public function getError404ForRoutingDownloadWithValidFileButInvalidPath(UnitTester $I, Example $example)
    {
        $I->expectException(
            new Exception('COM_OSDOWNLOADS_ERROR_NOT_FOUND'),
            function () use ($example) {
                $segments = explode('/', $example['route']);

                $vars = $this->router->parse($segments);
            }
        );
    }

    /*=====  End of TESTS FOR THE PARSER  ======*/
}
