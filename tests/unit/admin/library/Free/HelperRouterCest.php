<?php
require 'src/admin/library/Free/HelperRoute.php';

use Alledia\OSDownloads\Free\HelperRoute;

class HelperRouterCest
{
    ########################################################
    #### File Download URL
    ########################################################

    /**
     * Try to get the file download route providing a valid integer as the id.
     * It should return the URL with the given id.
     */
    public function tryToGetFileDownloadRouteWithValidIntId(UnitTester $I)
    {
        $route = HelperRoute::getFileDownloadRoute(12);

        $I->assertEquals(
            "index.php?option=com_osdownloads&task=download&tmpl=component&id=12",
            $route
        );
    }

    /**
     * Try to get the file download route providing a valid string as the id.
     * It should return the URL with the given id.
     */
    public function tryToGetFileDownloadRouteWithValidStringId(UnitTester $I)
    {
        $route = HelperRoute::getFileDownloadRoute('12');

        $I->assertEquals(
            "index.php?option=com_osdownloads&task=download&tmpl=component&id=12",
            $route
        );
    }

    /**
     * Try to get the file download route providing a negative integer.
     * It should return the URL with the given id, but without the minus sign.
     */
    public function tryToGetFileDownloadRouteWithNegativeId(UnitTester $I)
    {
        $route = HelperRoute::getFileDownloadRoute('-12');

        $I->assertEquals(
            "index.php?option=com_osdownloads&task=download&tmpl=component&id=12",
            $route
        );
    }

    /**
     * Try to get the file download route providing an invalid string as the id.
     * It should return the URL with the id = 0.
     */
    public function tryToGetFileDownloadRouteWithMaliciousStrings(UnitTester $I)
    {
        $route = HelperRoute::getFileDownloadRoute('1"; SHOW DATABASES;');

        $I->assertEquals(
            "index.php?option=com_osdownloads&task=download&tmpl=component&id=1",
            $route
        );
    }

    ########################################################
    #### File URL
    ########################################################

    /**
     * Try to get the file route providing a valid integer as the id.
     * It should return the URL with the given id.
     */
    public function tryToGetFileRouteWithValidIntIdAndItemID(UnitTester $I)
    {
        $route = HelperRoute::getFileRoute(12, 1);

        $I->assertEquals(
            "index.php?option=com_osdownloads&view=downloads&id=12&Itemid=1",
            $route
        );
    }

    /**
     * Try to get the file route providing a valid string as the id.
     * It should return the URL with the given id.
     */
    public function tryToGetFileRouteWithValidStringIdAndItemID(UnitTester $I)
    {
        $route = HelperRoute::getFileRoute('12', '1');

        $I->assertEquals(
            "index.php?option=com_osdownloads&view=downloads&id=12&Itemid=1",
            $route
        );
    }

    /**
     * Try to get the file route providing a negative integer.
     * It should return the URL with the given id, but without the minus sign.
     */
    public function tryToGetFileRouteWithNegativeIdAndItemId(UnitTester $I)
    {
        $route = HelperRoute::getFileRoute('-12', 2);

        $I->assertEquals(
            "index.php?option=com_osdownloads&view=downloads&id=12&Itemid=2",
            $route
        );
    }

    /**
     * Try to get the file route providing an invalid string as the id.
     * It should return the URL with the id = 0.
     */
    public function tryToGetFileRouteWithMaliciousStringsAndItemId(UnitTester $I)
    {
        $route = HelperRoute::getFileRoute('1"; SHOW DATABASES;', '2"; SHOW DATABASES;');

        $I->assertEquals(
            "index.php?option=com_osdownloads&view=downloads&id=1&Itemid=2",
            $route
        );
    }

    /**
     * Try to get the file route providing a valid integer as the id.
     * It should return the URL with the given id.
     */
    public function tryToGetFileRouteWithValidIntIdWithoutItemId(UnitTester $I)
    {
        $route = HelperRoute::getFileRoute(12);

        $I->assertEquals(
            "index.php?option=com_osdownloads&view=downloads&id=12",
            $route
        );
    }

    /**
     * Try to get the file route providing a valid string as the id.
     * It should return the URL with the given id.
     */
    public function tryToGetFileRouteWithValidStringIdWithoutItemID(UnitTester $I)
    {
        $route = HelperRoute::getFileRoute('12');

        $I->assertEquals(
            "index.php?option=com_osdownloads&view=downloads&id=12",
            $route
        );
    }

    /**
     * Try to get the file route providing a negative integer.
     * It should return the URL with the given id, but without the minus sign.
     */
    public function tryToGetFileRouteWithNegativeIdWithoutItemId(UnitTester $I)
    {
        $route = HelperRoute::getFileRoute('-12');

        $I->assertEquals(
            "index.php?option=com_osdownloads&view=downloads&id=12",
            $route
        );
    }

    /**
     * Try to get the file route providing an invalid string as the id.
     * It should return the URL with the id = 0.
     */
    public function tryToGetFileRouteWithMaliciousStringsWithoutItemId(UnitTester $I)
    {
        $route = HelperRoute::getFileRoute('1"; SHOW DATABASES;');

        $I->assertEquals(
            "index.php?option=com_osdownloads&view=downloads&id=1",
            $route
        );
    }

    ########################################################
    #### File List URL
    ########################################################
    
    /**
     * Try to get the file list route.
     */
    public function tryToGetFileListRoute(UnitTester $I)
    {
        $route = HelperRoute::getFileListRoute();

        $I->assertEquals(
            "index.php?option=com_osdownloads&view=downloads",
            $route
        );
    }
}
