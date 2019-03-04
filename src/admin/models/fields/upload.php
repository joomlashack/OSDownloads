<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2019 Joomlashack.com. All rights reserved
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

defined('_JEXEC') or die();

JFormHelper::loadFieldClass('Hidden');
JFormHelper::loadFieldClass('File');

class OsdownloadsFormFieldUpload extends JFormField
{
    protected $baseAttribs = array(
        'name'        => null,
        'label'       => null,
        'description' => null
    );

    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        $this->baseAttribs = array();
        foreach ($element->attributes() as $name => $attribute) {
            $this->baseAttribs[$name] = (string)$attribute;
        }

        $element['hiddenLabel'] = true;

        return parent::setup($element, $value, $group);
    }

    /**
     * This will display the currently uploaded file as just text
     *
     * @return string
     */
    protected function getInput()
    {
        $parts = explode('_', $this->value, 2);

        if ($fileName = array_pop($parts)) {
            $text = JText::sprintf('COM_OSDOWNLOADS_CURRENT_FILE', $fileName);
        } else {
            $text = JText::_('COM_OSDOWNLOADS_CURRENT_FILE_NONE');
        }

        return sprintf('<span class="btn alert-info">%s</span>', $text);
    }

    public function renderField($options = array())
    {
        $hiddenField = $this->renderHiddenField();
        $uploadField = $this->renderUploader();

        return $hiddenField . $uploadField . parent::renderField();
    }

    protected function renderHiddenField()
    {
        $hiddenField = $this->renderSubfield(
            array(
                'name' => $this->baseAttribs['name'],
                'type' => 'hidden'
            )
        );

        return $hiddenField;
    }

    protected function renderUploader()
    {
        $attribs                = $this->baseAttribs;
        $attribs['name']        .= '_upload';
        $attribs['type']        = 'file';
        $attribs['label']       = 'COM_OSDOWNLOADS_UPLOAD_FILE';
        $attribs['description'] = 'COM_OSDOWNLOADS_UPLOAD_FILE_DESC';

        $uploadField = $this->renderSubfield($attribs);

        return $uploadField;
    }

    protected function renderSubfield($attribs)
    {
        $name = empty($attribs['name']) ? null : $attribs['name'];

        if ($name) {
            $fieldXml = sprintf('<field %s/>', \Joomla\Utilities\ArrayHelper::toString($attribs));
            $field    = new SimpleXMLElement($fieldXml);

            $this->form->setField($field, $this->group);

            $renderedField = $this->form->renderField($name, $this->group);
        }

        return empty($renderedField) ? null : $renderedField;
    }
}
