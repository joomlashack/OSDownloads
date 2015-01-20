<?php
use \UnitTester;
use \Alledia\OSDownloads\Free\File;

class FileCest
{
    public function _before(UnitTester $I)
    {
    }

    public function _after(UnitTester $I)
    {
    }

    // tests
    public function tryToTest(UnitTester $I)
    {
        $a = File::isExternalURL('//google.com');

        $I->assertTrue($a);
    }
}
