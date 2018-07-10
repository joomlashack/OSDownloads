<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2018 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
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
