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
        $this->baseAttribs = array_merge(
            $this->baseAttribs,
            array(
                'name'        => (string)$element['name'],
                'label'       => (string)$element['label'],
                'description' => (string)$element['description']
            )
        );

        $element['name']        .= '_current';
        $element['label']       = 'COM_OSDOWNLOADS_CURRENT_FILE';
        $element['description'] = '';

        return parent::setup($element, $value, $group);
    }

    /**
     * This will display the currently uploaded file as just text
     *
     * @return string
     */
    protected function getInput()
    {
        $parts    = explode('_', $this->value, 2);
        $fileName = array_pop($parts) ?: 'N/A';

        return $fileName;
    }

    public function renderField($options = array())
    {
        $hiddenField = $this->renderHiddenField();
        $uploadField = $this->renderUploader();

        return parent::renderField($options) . $hiddenField . $uploadField;
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
        $attribs         = $this->baseAttribs;
        $attribs['name'] .= '_upload';
        $attribs['type'] = 'file';

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

/*
 * $index    = strpos($this->item->file_path, "_");
$realname = substr($this->item->file_path, $index + 1);

                <?php if ($this->item->file_path): ?>
                    <div class="control-group">
                        <div class="control-label">
                            <?php echo JText::_('COM_OSDOWNLOADS_CURRENT_FILE'); ?>
                        </div>
                        <div class="controls">
                            <?php echo $realname; ?>
                            <input type="hidden" name="old_file" value="<?php echo($this->item->file_path); ?>"/>
                        </div>
                    </div>
                <?php endif; ?>

*/
