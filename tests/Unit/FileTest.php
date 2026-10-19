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

use Alledia\OSDownloads\Free\File;
use Codeception\Attribute\DataProvider;
use Codeception\Test\Unit;

class FileTest extends Unit
{
    public static function knownExtensionProvider(): array
    {
        return [
            'file name'      => ['manual.pdf', 'application/pdf'],
            'path'           => ['/downloads/archive/package.zip', 'application/zip'],
            'url'            => ['https://example.com/files/song.mp3', 'audio/mpeg3'],
            'multiple dots'  => ['backup.2024.tar', 'application/x-tar'],
            'shared mapping' => ['report.docx', 'application/msword'],
        ];
    }

    #[DataProvider('knownExtensionProvider')]
    public function testGetMimeTypeFromExtension(string $fileName, string $expected)
    {
        $this->assertSame($expected, File::getMimeType($fileName));
    }

    public static function unknownExtensionProvider(): array
    {
        return [
            'unknown extension' => ['data.xyz'],
            'no extension'      => ['no-extension-file'],
            'empty'             => [''],
        ];
    }

    #[DataProvider('unknownExtensionProvider')]
    public function testGetMimeTypeReturnsNullForUnknownFiles(string $fileName)
    {
        $this->assertNull(File::getMimeType($fileName));
    }

    public function testGetMimeTypeInspectsExistingLocalFileContents()
    {
        $this->assertSame('text/plain', File::getMimeType(JPATH_SITE . '/images/not-really.pdf'));
    }

    public function testGetContentTypeFromFileNameUsesExtension()
    {
        $this->assertSame('application/pdf', File::getContentTypeFromFileName('manual.pdf'));
    }

    public function testGetContentTypeFromFileNameFallsBackToOctetStream()
    {
        $this->assertSame('application/octet-stream', File::getContentTypeFromFileName('data.xyz'));
    }

    public static function noHeadersProvider(): array
    {
        return [
            'local name'   => ['data.xyz'],
            'non-http url' => ['ftp://example.com/data.xyz'],
        ];
    }

    #[DataProvider('noHeadersProvider')]
    public function testGetHeadersSkipsNonHttpLocations(string $location)
    {
        $this->assertSame([], File::getHeaders($location));
    }
}
