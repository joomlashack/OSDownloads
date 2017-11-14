<?php
require 'src/admin/library/Free/Helper/Route.php';

use Alledia\OSDownloads\Free\Helper\Route as HelperRoute;

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
     * Try to get the file download route providing a valid integer as the id.
     * It should return the URL with the given id.
     */
    public function tryToGetFileDownloadRouteWithValidIntId(UnitTester $I)
    {
        $route = $this->helper->getFileDownloadRoute(12);

        $I->assertEquals(
            'index.php?option=com_osdownloads&task=download&tmpl=component&id=12',
            $route
        );
    }

    /**
     * Try to get the file download route providing a valid string as the id.
     * It should return the URL with the given id.
     */
    public function tryToGetFileDownloadRouteWithValidStringId(UnitTester $I)
    {
        $route = $this->helper->getFileDownloadRoute('12');

        $I->assertEquals(
            'index.php?option=com_osdownloads&task=download&tmpl=component&id=12',
            $route
        );
    }

    /**
     * Try to get the file download route providing a negative integer.
     * It should return the URL with the given id, but without the minus sign.
     */
    public function tryToGetFileDownloadRouteWithNegativeId(UnitTester $I)
    {
        $route = $this->helper->getFileDownloadRoute('-12');

        $I->assertEquals(
            'index.php?option=com_osdownloads&task=download&tmpl=component&id=12',
            $route
        );
    }

    /**
     * Try to get the file download route providing an invalid string as the id.
     * It should return the URL with the id = 0.
     */
    public function tryToGetFileDownloadRouteWithMaliciousStrings(UnitTester $I)
    {
        $route = $this->helper->getFileDownloadRoute('1\'; SHOW DATABASES;');

        $I->assertEquals(
            'index.php?option=com_osdownloads&task=download&tmpl=component&id=1',
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
        $route = $this->helper->getFileRoute(12, 1);

        $I->assertEquals(
            'index.php?option=com_osdownloads&view=downloads&id=12&Itemid=1',
            $route
        );
    }

    /**
     * Try to get the file route providing a valid string as the id.
     * It should return the URL with the given id.
     */
    public function tryToGetFileRouteWithValidStringIdAndItemID(UnitTester $I)
    {
        $route = $this->helper->getFileRoute('12', '1');

        $I->assertEquals(
            'index.php?option=com_osdownloads&view=downloads&id=12&Itemid=1',
            $route
        );
    }

    /**
     * Try to get the file route providing a negative integer.
     * It should return the URL with the given id, but without the minus sign.
     */
    public function tryToGetFileRouteWithNegativeIdAndItemId(UnitTester $I)
    {
        $route = $this->helper->getFileRoute('-12', 2);

        $I->assertEquals(
            'index.php?option=com_osdownloads&view=downloads&id=12&Itemid=2',
            $route
        );
    }

    /**
     * Try to get the file route providing an invalid string as the id.
     * It should return the URL with the id = 0.
     */
    public function tryToGetFileRouteWithMaliciousStringsAndItemId(UnitTester $I)
    {
        $route = $this->helper->getFileRoute('1\'; SHOW DATABASES;', '2\'; SHOW DATABASES;');

        $I->assertEquals(
            'index.php?option=com_osdownloads&view=downloads&id=1&Itemid=2',
            $route
        );
    }

    /**
     * Try to get the file route providing a valid integer as the id.
     * It should return the URL with the given id.
     */
    public function tryToGetFileRouteWithValidIntIdWithoutItemId(UnitTester $I)
    {
        $route = $this->helper->getFileRoute(12);

        $I->assertEquals(
            'index.php?option=com_osdownloads&view=downloads&id=12',
            $route
        );
    }

    /**
     * Try to get the file route providing a valid string as the id.
     * It should return the URL with the given id.
     */
    public function tryToGetFileRouteWithValidStringIdWithoutItemID(UnitTester $I)
    {
        $route = $this->helper->getFileRoute('12');

        $I->assertEquals(
            'index.php?option=com_osdownloads&view=downloads&id=12',
            $route
        );
    }

    /**
     * Try to get the file route providing a negative integer.
     * It should return the URL with the given id, but without the minus sign.
     */
    public function tryToGetFileRouteWithNegativeIdWithoutItemId(UnitTester $I)
    {
        $route = $this->helper->getFileRoute('-12');

        $I->assertEquals(
            'index.php?option=com_osdownloads&view=downloads&id=12',
            $route
        );
    }

    /**
     * Try to get the file route providing an invalid string as the id.
     * It should return the URL with the id = 0.
     */
    public function tryToGetFileRouteWithMaliciousStringsWithoutItemId(UnitTester $I)
    {
        $route = $this->helper->getFileRoute('1\'; SHOW DATABASES;');

        $I->assertEquals(
            'index.php?option=com_osdownloads&view=downloads&id=1',
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
     * Try to get the view item route providing a valid integer as the id.
     * It should return the URL with the given id.
     */
    public function tryToGetViewItemRouteWithValidIntIdAndItemID(UnitTester $I)
    {
        $route = $this->helper->getViewItemRoute(12, 1);

        $I->assertEquals(
            'index.php?option=com_osdownloads&view=item&id=12&Itemid=1',
            $route
        );
    }

    /**
     * Try to get the view item route providing a valid string as the id.
     * It should return the URL with the given id.
     */
    public function tryToGetViewItemRouteWithValidStringIdAndItemID(UnitTester $I)
    {
        $route = $this->helper->getViewItemRoute('12', '1');

        $I->assertEquals(
            'index.php?option=com_osdownloads&view=item&id=12&Itemid=1',
            $route
        );
    }

    /**
     * Try to get the view item route providing a negative integer.
     * It should return the URL with the given id, but without the minus sign.
     */
    public function tryToGetViewItemRouteWithNegativeIdAndItemId(UnitTester $I)
    {
        $route = $this->helper->getViewItemRoute('-12', 2);

        $I->assertEquals(
            'index.php?option=com_osdownloads&view=item&id=12&Itemid=2',
            $route
        );
    }

    /**
     * Try to get the view item route providing an invalid string as the id.
     * It should return the URL with the id = 0.
     */
    public function tryToGetViewItemRouteWithMaliciousStringsAndItemId(UnitTester $I)
    {
        $route = $this->helper->getViewItemRoute('1\'; SHOW DATABASES;', '2\'; SHOW DATABASES;');

        $I->assertEquals(
            'index.php?option=com_osdownloads&view=item&id=1&Itemid=2',
            $route
        );
    }

    /**
     * Try to get the view item route providing a valid integer as the id.
     * It should return the URL with the given id.
     */
    public function tryToGetViewItemRouteWithValidIntIdWithoutItemId(UnitTester $I)
    {
        $route = $this->helper->getViewItemRoute(12);

        $I->assertEquals(
            'index.php?option=com_osdownloads&view=item&id=12',
            $route
        );
    }

    /**
     * Try to get the view item route providing a valid string as the id.
     * It should return the URL with the given id.
     */
    public function tryToGetViewItemRouteWithValidStringIdWithoutItemID(UnitTester $I)
    {
        $route = $this->helper->getViewItemRoute('12');

        $I->assertEquals(
            'index.php?option=com_osdownloads&view=item&id=12',
            $route
        );
    }

    /**
     * Try to get the view item route providing a negative integer.
     * It should return the URL with the given id, but without the minus sign.
     */
    public function tryToGetViewItemRouteWithNegativeIdWithoutItemId(UnitTester $I)
    {
        $route = $this->helper->getViewItemRoute('-12');

        $I->assertEquals(
            'index.php?option=com_osdownloads&view=item&id=12',
            $route
        );
    }

    /**
     * Try to get the view item route providing an invalid string as the id.
     * It should return the URL with the id = 0.
     */
    public function tryToGetViewItemRouteWithMaliciousStringsWithoutItemId(UnitTester $I)
    {
        $route = $this->helper->getViewItemRoute('1\'; SHOW DATABASES;');

        $I->assertEquals(
            'index.php?option=com_osdownloads&view=item&id=1',
            $route
        );
    }

    ########################################################
    #### File Download Content URL
    ########################################################

    /**
     * Try to get the file download content route providing a valid integer as the id.
     * It should return the URL with the given id.
     */
    public function tryToGetFileDownloadContentRouteWithValidIntIdAndItemID(UnitTester $I)
    {
        $route = $this->helper->getFileDownloadContentRoute(12, 1);

        $I->assertEquals(
            'index.php?option=com_osdownloads&task=routedownload&tmpl=component&id=12&Itemid=1',
            $route
        );
    }

    /**
     * Try to get the file download content route providing a valid string as the id.
     * It should return the URL with the given id.
     */
    public function tryToGetFileDownloadContentRouteWithValidStringIdAndItemID(UnitTester $I)
    {
        $route = $this->helper->getFileDownloadContentRoute('12', '1');

        $I->assertEquals(
            'index.php?option=com_osdownloads&task=routedownload&tmpl=component&id=12&Itemid=1',
            $route
        );
    }

    /**
     * Try to get the file download content route providing a negative integer.
     * It should return the URL with the given id, but without the minus sign.
     */
    public function tryToGetFileDownloadContentRouteWithNegativeIdAndItemId(UnitTester $I)
    {
        $route = $this->helper->getFileDownloadContentRoute('-12', 2);

        $I->assertEquals(
            'index.php?option=com_osdownloads&task=routedownload&tmpl=component&id=12&Itemid=2',
            $route
        );
    }

    /**
     * Try to get the file download content route providing an invalid string as the id.
     * It should return the URL with the id = 0.
     */
    public function tryToGetFileDownloadContentRouteWithMaliciousStringsAndItemId(UnitTester $I)
    {
        $route = $this->helper->getFileDownloadContentRoute('1\'; SHOW DATABASES;', '2\'; SHOW DATABASES;');

        $I->assertEquals(
            'index.php?option=com_osdownloads&task=routedownload&tmpl=component&id=1&Itemid=2',
            $route
        );
    }

    /**
     * Try to get the file download content route providing a valid integer as the id.
     * It should return the URL with the given id.
     */
    public function tryToGetFileDownloadContentRouteWithValidIntIdWithoutItemId(UnitTester $I)
    {
        $route = $this->helper->getFileDownloadContentRoute(12);

        $I->assertEquals(
            'index.php?option=com_osdownloads&task=routedownload&tmpl=component&id=12',
            $route
        );
    }

    /**
     * Try to get the file download content route providing a valid string as the id.
     * It should return the URL with the given id.
     */
    public function tryToGetFileDownloadContentRouteWithValidStringIdWithoutItemID(UnitTester $I)
    {
        $route = $this->helper->getFileDownloadContentRoute('12');

        $I->assertEquals(
            'index.php?option=com_osdownloads&task=routedownload&tmpl=component&id=12',
            $route
        );
    }

    /**
     * Try to get the file download content route providing a negative integer.
     * It should return the URL with the given id, but without the minus sign.
     */
    public function tryToGetFileDownloadContentRouteWithNegativeIdWithoutItemId(UnitTester $I)
    {
        $route = $this->helper->getFileDownloadContentRoute('-12');

        $I->assertEquals(
            'index.php?option=com_osdownloads&task=routedownload&tmpl=component&id=12',
            $route
        );
    }

    /**
     * Try to get the file download content route providing an invalid string as the id.
     * It should return the URL with the id = 0.
     */
    public function tryToGetFileDownloadContentRouteWithMaliciousStringsWithoutItemId(UnitTester $I)
    {
        $route = $this->helper->getFileDownloadContentRoute('1\'; SHOW DATABASES;');

        $I->assertEquals(
            'index.php?option=com_osdownloads&task=routedownload&tmpl=component&id=1',
            $route
        );
    }

    ########################################################
    #### Admin File Form
    ########################################################

    /**
     * Try to get the admin file form route providing a valid integer as the id.
     * It should return the URL with the given id.
     */
    public function tryToGetAdminFileFormRouteWithValidIntId(UnitTester $I)
    {
        $route = $this->helper->getAdminFileFormRoute(12);

        $I->assertEquals(
            'index.php?option=com_osdownloads&view=file&cid[]=12',
            $route
        );
    }

    /**
     * Try to get the admin file form route providing a valid string as the id.
     * It should return the URL with the given id.
     */
    public function tryToGetAdminFileFormRouteWithValidStringId(UnitTester $I)
    {
        $route = $this->helper->getAdminFileFormRoute('12');

        $I->assertEquals(
            'index.php?option=com_osdownloads&view=file&cid[]=12',
            $route
        );
    }

    /**
     * Try to get the admin file form route providing a negative integer.
     * It should return the URL with the given id, but without the minus sign.
     */
    public function tryToGetAdminFileFormRouteWithNegativeId(UnitTester $I)
    {
        $route = $this->helper->getAdminFileFormRoute('-12');

        $I->assertEquals(
            'index.php?option=com_osdownloads&view=file&cid[]=12',
            $route
        );
    }

    /**
     * Try to get the admin file form route providing an invalid string as the id.
     * It should return the URL with the id = 0.
     */
    public function tryToGetAdminFileFormRouteWithMaliciousStrings(UnitTester $I)
    {
        $route = $this->helper->getAdminFileFormRoute('1\'; SHOW DATABASES;');

        $I->assertEquals(
            'index.php?option=com_osdownloads&view=file&cid[]=1',
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
