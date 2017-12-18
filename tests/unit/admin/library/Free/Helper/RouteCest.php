<?php
require 'src/admin/library/Free/Helper/Route.php';

use Alledia\OSDownloads\Free\Helper\Route as HelperRoute;
use Codeception\Example;

class RouteCest
{
    protected $helper;

    public function _before(UnitTester $I)
    {
        $this->helper = new HelperRoute;
    }

    ########################################################
    #### File Download URL
    ########################################################

    /**
     * Try to get the file download route. It should return the URL with the
     * given id, but sanitized.
     *
     * @example {"id": 12}
     * @example {"id": "12"}
     * @example {"id": "12\"; SHOW DATABASES;"}
     *
     */
    public function tryToGetFileDownloadRoute(UnitTester $I, Example $example)
    {
        $route = $this->helper->getFileDownloadRoute($example['id']);

        $I->assertEquals(
            'index.php?option=com_osdownloads&task=download&tmpl=component&id=12',
            $route
        );
    }

    ########################################################
    #### File URL
    ########################################################

    /**
     * Try to get the file route providing a valid integer as the id.
     * It should return the URL with the given id.
     *
     * @example {"id": 12, "itemid": 13}
     * @example {"id": "12", "itemid": "13"}
     * @example {"id": "12\"; SHOW DATABASES;", "itemid": "13\"; DROP TABLE #_tests;"}
     */
    public function tryTogetFileListRouteWithItemId(UnitTester $I, Example $example)
    {
        $route = $this->helper->getFileListRoute($example['id'], $example['itemid']);

        $I->assertEquals(
            'index.php?option=com_osdownloads&view=downloads&id=12&Itemid=13',
            $route
        );
    }

    /**
     * Try to get the file route providing a valid integer as the id.
     * It should return the URL with the given id.
     *
     * @example {"id": 12}
     * @example {"id": "12"}
     * @example {"id": "12\"; SHOW DATABASES;"}
     */
    public function tryTogetFileListRouteWithoutItemId(UnitTester $I, Example $example)
    {
        $route = $this->helper->getFileListRoute($example['id']);

        $I->assertEquals(
            'index.php?option=com_osdownloads&view=downloads&id=12',
            $route
        );
    }

    ########################################################
    #### File List URL
    ########################################################

    /**
     * Try to get the file list route.
     */
    public function tryTogetFileListRoute(UnitTester $I)
    {
        $route = $this->helper->getFileListRoute();

        $I->assertEquals(
            'index.php?option=com_osdownloads&view=downloads',
            $route
        );
    }

    ########################################################
    #### View Item URL
    ########################################################

    /**
     * Try to get the view item route with item id.
     *
     * @example {"id": 12, "itemid": 13}
     * @example {"id": "12", "itemid": "13"}
     * @example {"id": "12\"; SHOW DATABASES;", "itemid": "13\"; DROP TABLE #_tests;"}
     */
    public function tryToGetViewItemRouteWithItemId(UnitTester $I, Example $example)
    {
        $route = $this->helper->getViewItemRoute($example['id'], $example['itemid']);

        $I->assertEquals(
            'index.php?option=com_osdownloads&view=item&id=12&Itemid=13',
            $route
        );
    }

    /**
     * Try to get the view item route without item id.
     *
     * @example {"id": 12}
     * @example {"id": "12"}
     * @example {"id": "12\"; SHOW DATABASES;"}
     */
    public function tryToGetViewItemRouteWithoutItemId(UnitTester $I, Example $example)
    {
        $route = $this->helper->getViewItemRoute($example['id']);

        $I->assertEquals(
            'index.php?option=com_osdownloads&view=item&id=12',
            $route
        );
    }

    ########################################################
    #### File Download Content URL
    ########################################################

    /**
     * Try to get the file download content route with item id.
     *
     * @example {"id": 12, "itemid": 13}
     * @example {"id": "12", "itemid": "13"}
     * @example {"id": "12\"; SHOW DATABASES;", "itemid": "13\"; DROP TABLE #_tests;"}
     */
    public function tryToGetFileDownloadContentRouteWithItemID(UnitTester $I, Example $example)
    {
        $route = $this->helper->getFileDownloadContentRoute($example['id'], $example['itemid']);

        $I->assertEquals(
            'index.php?option=com_osdownloads&task=routedownload&tmpl=component&id=12&Itemid=13',
            $route
        );
    }

    /**
     * Try to get the file download content route without item id.
     *
     * @example {"id": 12}
     * @example {"id": "12"}
     * @example {"id": "12\"; SHOW DATABASES;"}
     */
    public function tryToGetFileDownloadContentRouteWithoutItemID(UnitTester $I, Example $example)
    {
        $route = $this->helper->getFileDownloadContentRoute($example['id']);

        $I->assertEquals(
            'index.php?option=com_osdownloads&task=routedownload&tmpl=component&id=12',
            $route
        );
    }

    ########################################################
    #### Admin File Form
    ########################################################

    /**
     * Try to get the admin file form route.
     *
     * @example {"id": 12}
     * @example {"id": "12"}
     * @example {"id": "12\"; SHOW DATABASES;"}
     */
    public function tryToGetAdminFileFormRoute(UnitTester $I, Example $example)
    {
        $route = $this->helper->getAdminFileFormRoute($example['id']);

        $I->assertEquals(
            'index.php?option=com_osdownloads&view=file&cid[]=12',
            $route
        );
    }

    ########################################################
    #### Admin File List URL
    ########################################################

    /**
     * Try to get the file list route.
     */
    public function tryToGetAdminFileListRoute(UnitTester $I)
    {
        $route = $this->helper->getAdminFileListRoute();

        $I->assertEquals(
            'index.php?option=com_osdownloads&view=files',
            $route
        );
    }

    ########################################################
    #### Admin Category List URL
    ########################################################

    /**
     * Try to get the category list route.
     */
    public function tryToGetAdminCategoryListRoute(UnitTester $I)
    {
        $route = $this->helper->getAdminCategoryListRoute();

        $I->assertEquals(
            'index.php?option=com_categories&extension=com_osdownloads',
            $route
        );
    }

    ########################################################
    #### Admin Email List URL
    ########################################################

    /**
     * Try to get the email list route.
     */
    public function tryToGetAdminEmailListRoute(UnitTester $I)
    {
        $route = $this->helper->getAdminEmailListRoute();

        $I->assertEquals(
            'index.php?option=com_osdownloads&view=emails',
            $route
        );
    }

    ########################################################
    #### Admin Main View URL
    ########################################################

    /**
     * Try to get the main view route.
     */
    public function tryToGetAdminMainViewRoute(UnitTester $I)
    {
        $route = $this->helper->getAdminMainViewRoute();

        $I->assertEquals(
            'index.php?option=com_osdownloads',
            $route
        );
    }
}
