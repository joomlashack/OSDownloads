<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2005-2023 Joomlashack.com. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
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
 * along with OSDownloads.  If not, see <http://www.gnu.org/licenses/>.
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Version;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die();

FormHelper::loadFieldClass('list');

if (Version::MAJOR_VERSION < 4) {
    class_alias(JFormFieldList::class, ListField::class);
}

// phpcs:enable PSR1.Files.SideEffects
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
// phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

class OsdownloadsFormFieldFile extends ListField
{
    protected $type = 'osdownloads.file';

    /**
     * @inheritDoc
     */
    protected function getOptions()
    {
        // Initialize variables.
        $options = [];

        $db = Factory::getDbo();

        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__osdownloads_documents')
            ->where('published = 1');

        $db->setQuery($query);
        $rows = $db->loadObjectList();

        foreach ($rows as $item) {
            $options[] = HTMLHelper::_(
                'select.option',
                (string)$item->id,
                Text::alt(
                    trim((string)$item->name),
                    preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname)
                ),
                'value',
                'text'
            );
        }

        return $options;
    }
}
