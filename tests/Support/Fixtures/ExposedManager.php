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

namespace Tests\Support\Fixtures;

use Alledia\OSDownloads\MailingLists\Manager;

/**
 * Presets the base path so the framework extension is never loaded
 */
class ExposedManager extends Manager
{
    /**
     * @var string
     */
    protected static $basePath = null;

    /**
     * @var string
     */
    protected static $baseClass = null;

    public function __construct(string $basePath, string $baseClass = '\\Alledia\\OSDownloads')
    {
        static::$basePath  = $basePath;
        static::$baseClass = $baseClass;
    }

    public function convertPathToClass(string $filePath): string
    {
        return parent::convertPathToClass($filePath);
    }

    public function getFormSources(array $files, string $name): array
    {
        return parent::getFormSources($files, $name);
    }
}
