<?php

/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2026 Joomlashack.com. All rights reserved
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
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
 * along with OSDownloads.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace Tests\Unit;

use Alledia\OSDownloads\Free\Helper\Helper;
use Codeception\Attribute\DataProvider;
use Codeception\Test\Unit;

class HelperTest extends Unit
{
    public static function validEmailProvider(): array
    {
        return [
            'simple'          => ['user@example.com'],
            'dotted name'     => ['first.last@example.co.uk'],
            'underscore/dash' => ['user_name-1@sub.example.org'],
            'uppercase'       => ['User@Example.COM'],
        ];
    }

    #[DataProvider('validEmailProvider')]
    public function testValidateEmailAcceptsValidAddresses(string $email)
    {
        $this->assertTrue(Helper::validateEmail($email));
        $this->assertTrue(Helper::validateEmail($email, false));
    }

    public function testValidateEmailAcceptsPlusSign()
    {
        $this->assertTrue(Helper::validateEmail('user+tag@example.com'));
    }

    public static function invalidEmailProvider(): array
    {
        return [
            'no at sign'      => ['plainaddress'],
            'no domain dot'   => ['user@localhost'],
            'no local part'   => ['@example.com'],
            'space'           => ['user name@example.com'],
            'empty'           => [''],
        ];
    }

    #[DataProvider('invalidEmailProvider')]
    public function testValidateEmailRejectsInvalidAddresses(string $email)
    {
        $this->assertFalse(Helper::validateEmail($email));
        $this->assertFalse(Helper::validateEmail($email, false));
    }

    public function testValidateEmailWithPlusSignRequiresTwoLetterTld()
    {
        $this->assertFalse(Helper::validateEmail('user@example.c'));
    }

    public static function localPathProvider(): array
    {
        return [
            'relative'      => ['images/local.txt'],
            'leading slash' => ['/images/local.txt'],
        ];
    }

    #[DataProvider('localPathProvider')]
    public function testIsLocalPathFindsExistingFile(string $path)
    {
        $this->assertTrue(Helper::isLocalPath($path));
    }

    public static function notLocalPathProvider(): array
    {
        return [
            'http url'          => ['http://example.com/images/local.txt'],
            'https url'         => ['https://example.com/images/local.txt'],
            'protocol relative' => ['//cdn.example.com/images/local.txt'],
            'other scheme'      => ['ftp://example.com/images/local.txt'],
            'missing file'      => ['images/missing.txt'],
            'directory'         => ['images'],
            'empty'             => [''],
            'null'              => [null],
        ];
    }

    #[DataProvider('notLocalPathProvider')]
    public function testIsLocalPathRejectsRemoteOrMissingPaths(?string $path)
    {
        $this->assertFalse(Helper::isLocalPath($path));
    }

    public function testGetFullPathResolvesUploads()
    {
        $expected = realpath(JPATH_SITE . '/media/com_osdownloads/files/upload.txt');

        $this->assertSame($expected, Helper::getFullPath('upload.txt'));
        $this->assertSame($expected, Helper::getFullPath('/upload.txt'));
    }

    public function testGetFullPathResolvesSitePaths()
    {
        $this->assertSame(
            realpath(JPATH_SITE . '/images/local.txt'),
            Helper::getFullPath('/images/local.txt', false)
        );
    }

    public function testGetFullPathReturnsEmptyStringForMissingFile()
    {
        $this->assertSame('', Helper::getFullPath('missing.txt'));
    }

    public function testGetContextsIsEmptyInFree()
    {
        $this->assertSame([], Helper::getContexts());
    }
}
