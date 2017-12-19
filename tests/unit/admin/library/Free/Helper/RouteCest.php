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
    public function getFileDownloadRoute(UnitTester $I, Example $example)
    {
        $route = $this->helper->getFileDownloadRoute($example['id']);

        $I->assertEquals(
            'index.php?option=com_osdownloads&task=download&tmpl=component&id=12',
            $route
        );
    }

    ########################################################
    #### File List URL
    ########################################################

    /**
     * Try to get the file route providing a valid integer as the id.
     * It should return the URL with the given id.
     *
     * @example {"id": 12, "itemid": 13}
     * @example {"id": "12", "itemid": "13"}
     * @example {"id": "12\"; SHOW DATABASES;", "itemid": "13\"; DROP TABLE #_tests;"}
     */
    public function getFileListRouteWithItemId(UnitTester $I, Example $example)
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
    public function getFileListRouteWithoutItemId(UnitTester $I, Example $example)
    {
        $route = $this->helper->getFileListRoute($example['id']);

        $I->assertEquals(
            'index.php?option=com_osdownloads&view=downloads&id=12',
            $route
        );
    }

    /**
     * Try to get the file list route.
     */
    public function getFileListRoute(UnitTester $I)
    {
        $route = $this->helper->getFileListRoute();

        $I->assertEquals(
            'index.php?option=com_osdownloads&view=downloads',
            $route
        );
    }

    ########################################################
    #### Category List URL
    ########################################################

    /**
     * Try to get the category route providing a valid integer as the id.
     * It should return the URL with the given id.
     *
     * @example {"id": 12, "itemid": 13}
     * @example {"id": "12", "itemid": "13"}
     * @example {"id": "12\"; SHOW DATABASES;", "itemid": "13\"; DROP TABLE #_tests;"}
     */
    public function getCategoryListRouteWithItemId(UnitTester $I, Example $example)
    {
        $route = $this->helper->getCategoryListRoute($example['id'], $example['itemid']);

        $I->assertEquals(
            'index.php?option=com_osdownloads&view=categories&id=12&Itemid=13',
            $route
        );
    }

    /**
     * Try to get the category route providing a valid integer as the id.
     * It should return the URL with the given id.
     *
     * @example {"id": 12}
     * @example {"id": "12"}
     * @example {"id": "12\"; SHOW DATABASES;"}
     */
    public function getCategoryListRouteWithoutItemId(UnitTester $I, Example $example)
    {
        $route = $this->helper->getCategoryListRoute($example['id']);

        $I->assertEquals(
            'index.php?option=com_osdownloads&view=categories&id=12',
            $route
        );
    }

    /**
     * Try to get the file list route.
     */
    public function getCategoryListRoute(UnitTester $I)
    {
        $route = $this->helper->getCategoryListRoute();

        $I->assertEquals(
            'index.php?option=com_osdownloads&view=categories',
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
    public function getViewItemRouteWithItemId(UnitTester $I, Example $example)
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
    public function getViewItemRouteWithoutItemId(UnitTester $I, Example $example)
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
    public function getFileDownloadContentRouteWithItemID(UnitTester $I, Example $example)
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
    public function getFileDownloadContentRouteWithoutItemID(UnitTester $I, Example $example)
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
    public function getAdminFileFormRoute(UnitTester $I, Example $example)
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
    public function getAdminFileListRoute(UnitTester $I)
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
    public function getAdminCategoryListRoute(UnitTester $I)
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
    public function getAdminEmailListRoute(UnitTester $I)
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
    public function getAdminMainViewRoute(UnitTester $I)
    {
        $route = $this->helper->getAdminMainViewRoute();

        $I->assertEquals(
            'index.php?option=com_osdownloads',
            $route
        );
    }

    ########################################################
    #### Admin Main Save Ordering Route
    ########################################################

    /**
     * Try to get the main view route.
     */
    public function getAdminSaveOrderingRoute(UnitTester $I)
    {
        $route = $this->helper->getAdminSaveOrderingRoute();

        $I->assertEquals(
            'index.php?option=com_osdownloads&task=files.saveOrderAjax&tmpl=component',
            $route
        );
    }
}
