<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2017 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

use Alledia\Framework\Factory;

jimport('joomla.application.component.modeladmin');

abstract class OSDownloadsModelFileAbstract extends JModelAdmin
{
    /**
     * Method to get the row form.
     *
     * @param   array   $data     Data for the form.
     * @param   boolean $loadData True if the form is to load its own data (default case), false if not.
     *
     * @return  mixed    A JForm object on success, false on failure
     *
     * @since   1.6
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm('com_osdownloads.file', 'file', array('control' => 'jform', 'load_data' => $loadData));

        // Load the extension
        $extension = Factory::getExtension('OSDownloads', 'component');
        $extension->loadLibrary();

        if ($extension->isPro()) {
            $form->loadFile(JPATH_COMPONENT . '/models/forms/file_pro.xml', true);
        }

        if (empty($form)) {
            return false;
        }

        return $form;
    }

    /**
     * Returns a JTable object, always creating it.
     *
     * @param   string  $type    The table type to instantiate. [optional]
     * @param   string  $prefix  A prefix for the table class name. [optional]
     * @param   array   $config  Configuration array for model. [optional]
     *
     * @return  JTable  A database object
     *
     * @since   1.6
     */
    public function getTable($type = 'Document', $prefix = 'OSDownloadsTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }
}
