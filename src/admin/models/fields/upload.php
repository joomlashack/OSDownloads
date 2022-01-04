<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2019-2022 Joomlashack.com. All rights reserved
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

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Language\Text;
use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die();

FormHelper::loadFieldClass('Hidden');
FormHelper::loadFieldClass('File');

class OsdownloadsFormFieldUpload extends FormField
{
    protected $baseAttribs = [
        'name'        => null,
        'label'       => null,
        'description' => null
    ];

    /**
     * @inheritDoc
     */
    public function setup(SimpleXMLElement $element, $value, $group = null)
    {
        $this->baseAttribs = [];
        foreach ($element->attributes() as $name => $attribute) {
            $this->baseAttribs[$name] = (string)$attribute;
        }

        $element['hiddenLabel'] = true;

        return parent::setup($element, $value, $group);
    }

    /**
     * @inheritDoc
     */
    protected function getInput()
    {
        $parts = explode('_', $this->value, 2);

        if ($fileName = array_pop($parts)) {
            $text = Text::sprintf('COM_OSDOWNLOADS_CURRENT_FILE', $fileName);
        } else {
            $text = Text::_('COM_OSDOWNLOADS_CURRENT_FILE_NONE');
        }

        return sprintf('<span class="btn alert-info">%s</span>', $text);
    }

    /**
     * @inheritDoc
     */
    public function renderField($options = [])
    {
        $hiddenField = $this->renderHiddenField();
        $uploadField = $this->renderUploader();

        return $hiddenField . $uploadField . parent::renderField();
    }

    /**
     * @return string
     */
    protected function renderHiddenField(): string
    {
        return $this->renderSubfield([
            'name' => $this->baseAttribs['name'],
            'type' => 'hidden'
        ]);
    }

    /**
     * @return string
     */
    protected function renderUploader(): string
    {
        $attribs                = $this->baseAttribs;
        $attribs['name']        .= '_upload';
        $attribs['type']        = 'file';
        $attribs['label']       = 'COM_OSDOWNLOADS_UPLOAD_FILE';
        $attribs['description'] = 'COM_OSDOWNLOADS_UPLOAD_FILE_DESC';

        return $this->renderSubfield($attribs);
    }

    /**
     * @param string[] $attribs
     *
     * @return string
     */
    protected function renderSubfield(array $attribs): string
    {
        $name = $attribs['name'] ?? null;

        if ($name) {
            try {
                $fieldXml = sprintf('<field %s/>', ArrayHelper::toString($attribs));
                $field    = new SimpleXMLElement($fieldXml);

                $this->form->setField($field, $this->group);

                $renderedField = $this->form->renderField($name, $this->group);
            } catch (Throwable $error) {
                // fail silently
            }
        }

        return $renderedField ?? '';
    }
}
