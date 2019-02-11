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

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

use Alledia\Framework\Factory;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

jimport('joomla.application.component.modeladmin');

abstract class OSDownloadsModelFileAbstract extends JModelAdmin
{
    /**
     * @param array $data
     * @param bool  $loadData
     *
     * @return JForm
     * @throws Exception
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm('com_osdownloads.file', 'file', array('control' => 'jform', 'load_data' => $loadData));

        // Load the extension
        $extension = Factory::getExtension('OSDownloads', 'component');
        $extension->loadLibrary();

        if (empty($form)) {
            return null;
        }

        if ($loadData) {
            $data = $this->loadFormData();

            // Load the data into the form after the plugins have operated.
            $form->bind($data);
        }

        return $form;
    }

    public function getTable($type = 'Document', $prefix = 'OSDownloadsTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * @return array|bool|JObject|object
     * @throws Exception
     */
    protected function loadFormData()
    {
        $data = $this->getItem();

        $this->preprocessData('com_osdownloads.file', $data);

        return $data;
    }

    /**
     * @param int $pk
     *
     * @return JObject
     * @throws Exception
     */
    public function getItem($pk = null)
    {
        $pk    = $pk ?: (int)$this->getState()->get($this->getName() . '.id');
        $table = $this->getTable();

        if ($pk > 0) {
            // Attempt to load the row.
            $return = $table->load($pk);

            // Check for a table object error.
            if ($return === false && $table->getError()) {
                $this->setError($table->getError());

                return null;
            }
        }

        /** @var JObject $item */
        $properties = $table->getProperties(1);
        $item       = ArrayHelper::toObject($properties, '\JObject');

        if (property_exists($item, 'params')) {
            $registry     = new Registry($item->params);
            $item->params = $registry->toArray();
        }

        return $item;
    }
}
