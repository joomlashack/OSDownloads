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

use Codeception\Attribute\DataProvider;
use Codeception\Test\Unit;
use Tests\Support\Fixtures\ExposedManager;

class MailingListManagerTest extends Unit
{
    /**
     * @var ExposedManager
     */
    protected $manager = null;

    protected function setUp(): void
    {
        parent::setUp();

        // Plugin xml files map to classes in Tests\Support\Fixtures\MailingList
        $this->manager = new ExposedManager(rtrim(codecept_data_dir(), '/'), '\\Tests\\Support\\Fixtures');
    }

    protected function dataFiles(string ...$names): array
    {
        return array_map(
            function (string $name) {
                return codecept_data_dir('MailingList/' . $name . '.xml');
            },
            $names
        );
    }

    public static function classPathProvider(): array
    {
        return [
            'php file' => ['/Free/MailingList/MailChimp.php', '\\Alledia\\OSDownloads\\Free\\MailingList\\MailChimp'],
            'xml file' => ['/Free/MailingList/MailChimp.xml', '\\Alledia\\OSDownloads\\Free\\MailingList\\MailChimp'],
            'pro file' => ['/Pro/MailingList/Other.php', '\\Alledia\\OSDownloads\\Pro\\MailingList\\Other'],
        ];
    }

    #[DataProvider('classPathProvider')]
    public function testConvertPathToClass(string $relativePath, string $expected)
    {
        $basePath = '/var/www/administrator/components/com_osdownloads/library';
        $manager  = new ExposedManager($basePath);

        $this->assertSame($expected, $manager->convertPathToClass($basePath . $relativePath));
    }

    public function testGetFormSourcesKeysByGroup()
    {
        $sources = $this->manager->getFormSources($this->dataFiles('Active', 'Plain', 'NoGroup'), 'config');

        $this->assertSame(['active', 'plain'], array_keys($sources));
        $this->assertSame('active_config', (string)$sources['active']->field['name']);
    }

    public function testGetFormSourcesSkipsFilesWithoutMatchingFields()
    {
        $this->assertSame([], $this->manager->getFormSources($this->dataFiles('Active', 'Plain'), 'category'));
    }

    public function testGetFormSourcesAppliesOrdering()
    {
        $config = $this->manager->getFormSources($this->dataFiles('Active', 'Plain'), 'config');
        $file   = $this->manager->getFormSources($this->dataFiles('Plain'), 'file');

        $this->assertSame('20', (string)$config['active']['order'], 'Inherits order from the form');
        $this->assertSame('999', (string)$config['plain']['order'], 'Defaults when no order is set');
        $this->assertSame('5', (string)$file['plain']['order'], 'Keeps its own order');
    }

    public function testGetFormSourcesKeepsFirstFileForDuplicateGroups()
    {
        $sources = $this->manager->getFormSources($this->dataFiles('Active', 'Duplicate'), 'config');

        $this->assertSame('active_config', (string)$sources['active']->field['name']);
    }

    public function testGetFormSourcesSkipsPluginsWithMissingDependencies()
    {
        $sources = $this->manager->getFormSources($this->dataFiles('Active', 'Disabled', 'Inactive'), 'config');

        $this->assertSame(['active', 'inactive'], array_keys($sources));
    }

    public function testGetFormSourcesSkipsDisabledPluginsOutsideConfig()
    {
        $sources = $this->manager->getFormSources($this->dataFiles('Active', 'Disabled', 'Inactive'), 'file');

        $this->assertSame(['active'], array_keys($sources));
    }

    public function testShippedMailChimpFormProvidesConfigFields()
    {
        $manager = new ExposedManager(OSDOWNLOADS_LIBRARY);
        $sources = $manager->getFormSources([OSDOWNLOADS_LIBRARY . '/Free/MailingList/MailChimp.xml'], 'config');

        $this->assertSame(['mailchimp'], array_keys($sources));

        $fieldNames = array_map('strval', $sources['mailchimp']->xpath('field/@name'));
        $this->assertSame(['enable', 'api', 'list_id'], $fieldNames);
    }
}
