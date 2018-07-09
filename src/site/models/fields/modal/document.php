<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2018 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
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
