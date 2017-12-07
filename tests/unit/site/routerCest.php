<?php
require 'src/site/router.php';

use Codeception\Example;
use Codeception\Util\Stub;

class RouterCest
{
    protected $router;

    public function _before(UnitTester $I)
    {
        global $files;
        global $categories;

        /**
         *
         * Dummy categories
         *
         */
        $categories = [
            1 => (object) [
                'id'        => 1,
                'alias'     => 'category_1',
                'path'      => 'category_1',
                'parent_id' => null,
            ],
            2 => (object) [
                'id'        => 2,
                'alias'     => 'category_2',
                'path'      => 'category_1/category_2',
                'parent_id' => 1,
            ],
            3 => (object) [
                'id'        => 3,
                'alias'     => 'category_3',
                'path'      => 'category_1/category_2/category_3',
                'parent_id' => 2,
            ],
        ];
        // Add index by alias
        $categories['category_1'] = &$categories[1];
        $categories['category_2'] = &$categories[2];
        $categories['category_3'] = &$categories[3];

        /**
         *
         * Dummy files
         *
         */
        $files = [
            1 => (object) [
                'id'      => 1,
                'alias'   => 'file_1',
                'cate_id' => 3,
            ],
            2 => (object) [
                'id'      => 2,
                'alias'   => 'file_2',
                'cate_id' => 1,
            ],
        ];
        $files['file_1'] = &$files[1];
        $files['file_2'] = &$files[2];

        /**
         *
         * Dummy router
         *
         */
        $container = new class {
            public $helperSEF;
        };

        $container->helperSEF = new class {
            public function appendCategoriesToSegments(&$segments, $catid)
            {
                global $categories;

                $category = $categories[$catid];

                $segments = array_merge($segments, explode('/', $category->path));
            }

            public function getCategoryFromAlias($alias)
            {
                global $categories;

                if (isset($categories[$alias])) {
                    return $categories[$alias];
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
        };

        $this->router  = Stub::make(
            'OsdownloadsRouter',
            [
                'container'      => $container,
                'customSegments' => ['files' => 'files'],
            ]
        );
    }

    /*===========================================
    =            CUSTOM SEGMENTS            =
    ===========================================*/

    /**
     * Try to get current custom segments
     *
     * @example {"custom_segment": "files"}
     * @example {"custom_segment": "customA"}
     * @example {"custom_segment": "custom_B"}
     */
    public function tryToGetCurrentCustomSegments(UnitTester $I, Example $example)
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
    public function tryToCustomizeTheFilesSegment(UnitTester $I, Example $example)
    {
        $this->router->setCustomSegments($example['segments']);

        $customSegments = $this->router->getCustomSegments();

        $I->assertArrayHasKey('files', $customSegments);
        $I->assertEquals($example['expected'], $customSegments['files']);
    }

    /*=====  End of CUSTOM SEGMENTS  ======*/

    /*=============================================
    =            TESTS FOR THE BUILDER            =
    =============================================*/

    /**
     * Try to build route segments and check if the used query elements were
     * removed from the query.
     */
    public function tryToBuildRouteSegmentsAndCheckIfQueryArgumentsWereRemoved(UnitTester $I)
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

    /**
     * Try to build route segments for the routedownload and download tasks.
     *
     * @example {"task": "routedownload", "layout": "any-layout", "id": "1", "route": "category_1/category_2/category_3/routedownload/file_1"}
     * @example {"task": "routedownload", "id": "1", "route": "category_1/category_2/category_3/routedownload/file_1"}
     * @example {"task": "download", "layout": "any-layout", "id": "1", "route": "category_1/category_2/category_3/download/file_1"}
     * @example {"task": "download", "id": "1", "route": "category_1/category_2/category_3/download/file_1"}
     */
    public function tryToBuildRouteSegmentsForRoutedownloadAndDownloadTasks(UnitTester $I, Example $example)
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
     * Try to build route segments for the confirmemail task.
     *
     * @example {"task": "confirmemail", "data": "889ec873b0e085c1724ec0ca560d3cfe", "route": "confirmemail/889ec873b0e085c1724ec0ca560d3cfe"}
     * @example {"task": "confirmemail", "data": "4d43e82c9633e2c57df71042d9976135", "view": "any-view", "route": "confirmemail/4d43e82c9633e2c57df71042d9976135"}
     */
    public function tryToBuildRouteSegmentsForConfirmEmailTask(UnitTester $I, Example $example)
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
     * Try to build route segments for the thank you page in the item view.
     *
     * @example {"view": "item", "layout": "thankyou", "id": 1, "route": "category_1/category_2/category_3/files/file_1/thankyou"}
     * @example {"view": "item", "layout": "thankyou", "id": 2, "route": "category_1/files/file_2/thankyou"}
     */
    public function tryToBuildRouteSegmentsForViewItemThankYouPage(UnitTester $I, Example $example)
    {
        $query = [
            'view'   => $example['view'],
            'layout' => $example['layout'],
            'id'     => $example['id'],
        ];

        $route = implode('/', $this->router->build($query));

        $I->assertEquals($example['route'], $route);
    }

    /**
     * Try to build route segments for a single file
     *
     * @example {"view": "item", "id": 1, "route": "category_1/category_2/category_3/files/file_1"}
     * @example {"view": "item", "id": 2, "route": "category_1/files/file_2"}
     */
    public function tryToBuildRouteSegmentsForASingleFile(UnitTester $I, Example $example)
    {
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
     * @example {"Itemid": "101", "id": 1, "route": "category_1/category_2/category_3/files/file_1"}
     * @example {"Itemid": "102", "id": 2, "route": "category_1/files/file_2"}
     */
    public function tryToBuildRouteSegmentsForASingleFileBasedOnItemId(UnitTester $I, Example $example)
    {
        $query = [
            'Itemid' => $example['Itemid'],
        ];

        $route = implode('/', $this->router->build($query));

        $I->assertEquals($example['route'], $route);
    }

    /**
     * Try to build route segments for a list of files.
     *
     * @example {"view": "downloads", "id": 1, "route": "category_1/files"}
     * @example {"view": "downloads", "id": 2, "route": "category_1/category_2/files"}
     * @example {"view": "downloads", "id": 3, "route": "category_1/category_2/category_3/files"}
     */
    public function tryToBuildRouteSegmentsForAListOfFiles(UnitTester $I, Example $example)
    {
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
     * @example {"Itemid": "201", "route": "category_1/files"}
     * @example {"Itemid": "202", "route": "category_1/category_2/files"}
     * @example {"Itemid": "203", "route": "category_1/category_2/category_3/files"}
     */
    public function tryToBuildRouteSegmentsForAListOfFilesBasedOnItemId(UnitTester $I, Example $example)
    {
        $query = [
            'Itemid' => $example['Itemid'],
        ];

        $route = implode('/', $this->router->build($query));

        $I->assertEquals($example['route'], $route);
    }

     /**
     * Try to build route segments for a list of files with custom segment.
     *
     * @example {"view": "downloads", "id": 1, "segment": "files_custom_segment", "route": "category_1/files_custom_segment"}
     * @example {"view": "downloads", "id": 2, "segment": "files_custom_segment2", "route": "category_1/category_2/files_custom_segment2"}
     * @example {"view": "downloads", "id": 3, "segment": "files_custom_segment3", "route": "category_1/category_2/category_3/files_custom_segment3"}
     */
    public function tryToBuildRouteSegmentsForAListOfFilesWithCustomSegment(UnitTester $I, Example $example)
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
     * Try to build route segments for a list of categories. (Pro version)
     *
     * @example {"view": "categories", "id": 1, "route": "category_1"}
     * @example {"view": "categories", "id": 2, "route": "category_1/category_2"}
     * @example {"view": "categories", "id": 3, "route": "category_1/category_2/category_3"}
     */
    public function tryToBuildRouteSegmentsForAListOfCategories(UnitTester $I, Example $example)
    {
        $query = [
            'view'   => $example['view'],
            'id'     => $example['id'],
        ];

        $route = implode('/', $this->router->build($query));

        $I->assertEquals($example['route'], $route);
    }

    /*=====  End of TESTS FOR THE BUILDER  ======*/


    /*============================================
    =            TESTS FOR THE PARSER            =
    ============================================*/

    /**
     * Try to parse route segments for the routedownload and download tasks routes.
     *
     * @example {"task": "routedownload", "id": "1", "route": "category_1/category_2/category_3/routedownload/file_1"}
     * * @example {"task": "routedownload", "id": "2", "route": "category_1/routedownload/file_2"}
     * @example {"task": "download", "id": "1", "route": "category_1/category_2/category_3/download/file_1"}
     * @example {"task": "download", "id": "2", "route": "category_1/download/file_2"}
     */
    public function tryToParseRouteSegmentsForRoutedownloadAndDownloadTasks(UnitTester $I, Example $example)
    {
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
     * Try to parse route segments for the confirmemail task.
     *
     * @example {"task": "confirmemail", "data": "889ec873b0e085c1724ec0ca560d3cfe", "route": "confirmemail/889ec873b0e085c1724ec0ca560d3cfe"}
     * @example {"task": "confirmemail", "data": "4d43e82c9633e2c57df71042d9976135", "route": "confirmemail/4d43e82c9633e2c57df71042d9976135"}
     */
    public function tryToParseRouteSegmentsForConfirmEmailTask(UnitTester $I, Example $example)
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

    /**
     * Try to parse route segments for the thank you page in the item view.
     *
     * @example {"id": 1, "route": "category_1/category_2/category_3/files/file_1/thankyou"}
     * @example {"id": 2, "route": "category_1/files/file_2/thankyou"}
     */
    public function tryToParseRouteSegmentsForViewItemThankYouPage(UnitTester $I, Example $example)
    {
        $segments = explode('/', $example['route']);

        $vars = $this->router->parse($segments);

        $I->assertArrayHasKey('view', $vars);
        $I->assertEquals('item', $vars['view']);

        $I->assertArrayHasKey('layout', $vars);
        $I->assertEquals('thankyou', $vars['layout']);

        $I->assertArrayHasKey('tmpl', $vars);
        $I->assertEquals('component', $vars['tmpl']);

        $I->assertArrayHasKey('id', $vars);
        $I->assertEquals($example['id'], $vars['id']);
    }

    /**
     * Try to parse route segments for a single file.
     *
     * @example {"id": 1, "route": "category_1/category_2/category_3/files/file_1"}
     * @example {"id": 2, "route": "category_1/files/file_2"}
     */
    public function tryToParseRouteSegmentsForASingleFile(UnitTester $I, Example $example)
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
     * Try to parse route segments for a list of files.
     *
     * @example {"id": 0, "route": "files"}
     * @example {"id": 1, "route": "category_1/files"}
     * @example {"id": 2, "route": "category_1/category_2/files"}
     * @example {"id": 3, "route": "category_1/category_2/category_3/files"}
     */
    public function tryToParseRouteSegmentsForAListOfFiles(UnitTester $I, Example $example)
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
     * Try to parse route segments for a list of files with custom segment
     *
     * @example {"id": 0, "segment": "files_custom_segment", "route": "files_custom_segment"}
     * @example {"id": 1, "segment": "files_custom_segment2", "route": "category_1/files_custom_segment2"}
     * @example {"id": 2, "segment": "files_custom_segment2", "route": "category_1/category_2/files_custom_segment2"}
     * @example {"id": 3, "segment": "files_custom_segment3", "route": "category_1/category_2/category_3/files_custom_segment3"}
     */
    public function tryToParseRouteSegmentsForAListOfFilesWithCustomSegment(UnitTester $I, Example $example)
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

    /**
     * Try to parse route segments for a list of categories. (Pro version)
     *
     * @example {"id": 0, "route": ""}
     * @example {"id": 1, "route": "category_1"}
     * @example {"id": 2, "route": "category_1/category_2"}
     * @example {"id": 3, "route": "category_1/category_2/category_3"}
     */
    public function tryToParseRouteSegmentsForAListOfCategories(UnitTester $I, Example $example)
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

    /*======================================
    =            Invalid Routes            =
    ======================================*/

    /**
     * Try to parse route segments of invalid URLs, expecting the error 404.
     *
     * List of categories: Invalid category alias
     * @example {"route": "invalid_category_alias"}
     * @example {"route": "23"}
     * @example {"route": "category_1/category_2_invalid"}
     * @example {"route": "category_1/category_2/invalid1"}
     */
    public function tryToGetError404ForListOfCategoriesWithInvalidCategory(UnitTester $I, Example $example)
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
     * @example {"route": "category_2/category_1"}
     * @example {"route": "category_1_invalid/category_2"}
     * @example {"route": "category_1/category_2_invalid/category_3"}
     * @example {"route": "category_2/category_3"}
     */
    public function tryToGetError404ForListOfCategoriesWithValidCategoryButInvalidPath(UnitTester $I, Example $example)
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
     * @example {"route": "category_1/category_invalid/items"}
     * @example {"route": "category_1/category_2/category_invalid/items"}
     */
    public function tryToGetError404ForListOfFilesWithInvalidCategoryAlias(UnitTester $I, Example $example)
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
     * @example {"route": "category_invalid/category_1/items"}
     * @example {"route": "category_invalid/category_2/items"}
     * @example {"route": "category_1/category_2_invalid/category_3/items"}
     */
    public function tryToGetError404ForListOfFilesWithCategoryButInvalidPath(UnitTester $I, Example $example)
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
     * @example {"route": "category_1/files/file_invalid"}
     * @example {"route": "category_1/category_2/files/file_invalid"}
     * @example {"route": "category_1/category_2/category_3/files/file_invalid"}
     */
    public function tryToGetError404ForSingleFileWithInvalidAlias(UnitTester $I, Example $example)
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
     * @example {"route": "files/file_1"}
     * @example {"route": "category_1/files/file_1"}
     * @example {"route": "category_1/category_2/files/file_1"}
     * @example {"route": "category_1/category_2_invalid/files/file_1"}
     * @example {"route": "category_1/category_2/category_3_invalid/files/file_1"}
     */
    public function tryToGetError404ForSingleFileWithInvalidPath(UnitTester $I, Example $example)
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
     * @example {"route": "download/category_1/file_invalid"}
     * @example {"route": "download/category_1/category_2/category_3/file_invalid"}
     */
    public function tryToGetError404ForDownloadingInvalidFile(UnitTester $I, Example $example)
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
     * @example {"route": "download/file_1"}
     * @example {"route": "category_1/download/file_1"}
     * @example {"route": "category_3/download/file_1"}
     * @example {"route": "category_1/category_2/download/file_1"}
     * @example {"route": "category_1/category_2/category_3_invalid/download/file_1"}
     */
    public function tryToGetError404ForDownloadingWithValidFileButInvalidPath(UnitTester $I, Example $example)
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
     * @example {"route": "category_1/category_2/category_3/routedownload/file_invalid"}
     */
    public function tryToGetError404ForRoutingDownloadWithInvalidFile(UnitTester $I, Example $example)
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
     * @example {"route": "routedownload/file_1"}
     * @example {"route": "category_1/routedownload/file_1"}
     * @example {"route": "category_3/routedownload/file_1"}
     * @example {"route": "category_1/category_2/routedownload/file_1"}
     * @example {"route": "category_1/category_2/category_3_invalid/routedownload/file_1"}
     */
    public function tryToGetError404ForRoutingDownloadWithValidFileButInvalidPath(UnitTester $I, Example $example)
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
