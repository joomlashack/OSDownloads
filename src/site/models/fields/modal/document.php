<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2005-2019 Joomlashack.com. All rights reserved
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

defined('JPATH_BASE') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

class JFormFieldModal_Document extends JFormFieldList
{
    protected $type = 'Modal_Document';

    protected function getOptions()
    {
        // Initialize variables.
        $options = array();

        $db = JFactory::getDBO();

        $query = $db->getQuery(true);
        $query->select("*");
        $query->from("#__osdownloads_documents");
        $query->where("published = 1");
        $db->setQuery($query);
        $rows = $db->loadObjectList();

        foreach ($rows as $item) {

            // Create a new option object based on the <option /> element.
            $tmp = JHtml::_(
                'select.option',
                (string) $item->id,
                JText::alt(
                    trim((string) $item->name),
                    preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname)
                ),
                'value',
                'text'
            );

            // Add the option object to the result set.
            $options[] = $tmp;
        }

        reset($options);

        return $options;
    }
}
