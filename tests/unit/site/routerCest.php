<?php
require 'src/site/router.php';

use Codeception\Example;
use Codeception\Util\Stub;

class RouterCest
{
    protected $router;

    public function _before(UnitTester $I)
    {
        $this->router  = Stub::make(
            'OsdownloadsRouter',
            [
                'getCategoryIdFromFile' => function($id)
                {
                    switch ($id) {
                        case 1:
                            return 12;
                            break;
                    }

                    return 0;
                },

                'getCategory' => function($id)
                {
                    $categories = [
                        10 => (object) [
                            'parent_id' => null,
                            'alias'     => 'category10'
                        ],
                        11 => (object) [
                            'parent_id' => 10,
                            'alias'     => 'category11'
                        ],
                        12 => (object) [
                            'parent_id' => 11,
                            'alias'     => 'category12'
                        ]
                    ];

                    return $categories[$id];
                },

                'getFileAlias' => function($id)
                {
                    switch ($id) {
                        case 1:
                            return 'file1';
                            break;
                    }

                    return false;
                }
            ]
        );
    }

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
     * Try to build route segments for the 'thank you' page in the routedownload and download tasks.
     *
     * task: 'routedownload', layout: 'thankyou', 'id': 1 ==> 0: 'thankyou', 1..n: [category_aliases], n+1: [file_alias]
     * task: 'download', layout: 'thankyou', 'id': 1 ==> 0: 'thankyou', 1..n: [category_aliases], n+1: [file_alias]
     *
     * @example {"task": "routedownload", "layout": "thankyou", "id": "1"}
     * @example {"task": "download", "layout": "thankyou", "id": "1"}
     */
    public function tryToBuildRouteSegmentsForThankyouPageInRoutedownloadAndDownloadTasks(UnitTester $I, Example $example)
    {
        $query = [
            'task'   => $example['task'],
            'layout' => $example['layout'],
            'id'     => $example['id'],
        ];

        $segments = $this->router->build($query);

        $I->assertEquals('thankyou', $segments[0]);
        $I->assertEquals('category10', $segments[1]);
        $I->assertEquals('category11', $segments[2]);
        $I->assertEquals('category12', $segments[3]);
        $I->assertEquals('file1', $segments[4]);
    }

    /**
     * Try to build route segments for the routedownload and download tasks.
     *
     * task: 'routedownload', layout: 'any', id: 1 ==> 0: 'routedownload', 1..n: [category_aliases], n+1: [file_alias]
     * task: 'routedownload', layout: null, id: 1 ==> 0: 'routedownload', 1..n: [category_aliases], n+1: [file_alias]
     * task: 'download', layout: 'any', id: 1 ==> 0: 'download', 1..n: [category_aliases], n+1: [file_alias]
     * task: 'download', layout: null, id: 1 ==> 0: 'download', 1..n: [category_aliases], n+1: [file_alias]
     *
     * @example {"task": "routedownload", "layout": "any-layout", "id": "1"}
     * @example {"task": "routedownload", "id": "1"}
     * @example {"task": "download", "layout": "any-layout", "id": "1"}
     * @example {"task": "download", "id": "1"}
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

        $segments = $this->router->build($query);

        $I->assertEquals($example['task'], $segments[0]);
        $I->assertEquals('category10', $segments[1]);
        $I->assertEquals('category11', $segments[2]);
        $I->assertEquals('category12', $segments[3]);
        $I->assertEquals('file1', $segments[4]);
    }

    /**
     * Try to build route segments for the confirmemail task.
     *
     * task: 'confirmemail' ==> 0: 'confirmemail', 1: [query_data]
     *
     * @example {"task": "confirmemail", "data": "889ec873b0e085c1724ec0ca560d3cfe"}
     * @example {"task": "confirmemail", "data": "4d43e82c9633e2c57df71042d9976135", "view": "any-view"}
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

        $segments = $this->router->build($query);

        $I->assertEquals('confirmemail', $segments[0]);
        $I->assertEquals($example['data'], $segments[1]);
        $I->assertEquals(2, count($segments), 'The route should contain only 2 segments: task and data');
    }

    /**
     * Try to build route segments for the thank you page in the item view.
     * 
     *  view: 'item', layout: 'thankyou', id: 1 ==> 0: 'thankyou', 1..n: [category_aliases], n+1: [file_alias]
     *
     * @example {"view": "item", "layout": "thankyou", "id": 1}
     */
    public function tryToBuildRouteSegmentsForViewItemThankYouPage(UnitTester $I, Example $example)
    {
        $query = [
            'view'   => $example['view'],
            'layout' => $example['layout'],
            'id'     => $example['id'],
        ];

        $segments = $this->router->build($query);

        $I->assertEquals('thankyou', $segments[0]);
        $I->assertEquals('category10', $segments[1]);
        $I->assertEquals('category11', $segments[2]);
        $I->assertEquals('category12', $segments[3]);
        $I->assertEquals('file1', $segments[4]);
    }

    /**
     * Try to build route segments for the item view
     * 
     * view: 'item', id: 1 ==> 0: 'file', 1..n: [category_aliases], n+1: [file_alias]
     *
     * @example {"view": "item", "id": 1}
     */
    public function tryToBuildRouteSegmentsForViewItem(UnitTester $I, Example $example)
    {
        $query = [
            'view'   => $example['view'],
            'id'     => $example['id'],
        ];

        $segments = $this->router->build($query);

        $I->assertEquals('file', $segments[0]);
        $I->assertEquals('category10', $segments[1]);
        $I->assertEquals('category11', $segments[2]);
        $I->assertEquals('category12', $segments[3]);
        $I->assertEquals('file1', $segments[4]);
    }

    /**
     * Try to parse segments for sub
     * 
     * view: 'item', id: 1 ==> 0: 'file', 1..n: [category_aliases], n+1: [file_alias]
     *
     * @example {"view": "item", "id": 1}
     */
    public function tryToBuildRouteSegmentsForViewItem(UnitTester $I, Example $example)
    {
        $query = [
            'view'   => $example['view'],
            'id'     => $example['id'],
        ];

        $segments = $this->router->build($query);

        $I->assertEquals('file', $segments[0]);
        $I->assertEquals('category10', $segments[1]);
        $I->assertEquals('category11', $segments[2]);
        $I->assertEquals('category12', $segments[3]);
        $I->assertEquals('file1', $segments[4]);
    }

    // PARSE
    // thankyou/category10/category11/category12/file1

    // category
    // category/category10

    // file/category10/category11/category12/file1

    // download/category10/category11/category12/file1

    // routedownload/category10/category11/category12/file1

    // confirmemail/889ec873b0e085c1724ec0ca560d3cfe
}
