<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2005-2021 Joomlashack.com. All rights reserved
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

namespace Alledia\OSDownloads\Free\Joomla\Module;

defined('_JEXEC') or die();

use Alledia\Framework\Joomla\Extension\AbstractFlexibleModule;
use Alledia\Framework\Factory;
use Alledia\OSDownloads\Free\Joomla\Component\Site as FreeComponentSite;
use JRoute;
use JFactory;
use JForm;
use JLoader;
use ContentHelperRoute;
use SplPriorityQueue;
use Exception;
use JEventDispatcher;
use Joomla\Utilities\ArrayHelper;

\JLoader::register('OSDownloadsHelper', JPATH_ADMINISTRATOR . '/components/com_osdownloads/helpers/osdownloads.php');

class File extends AbstractFlexibleModule
{
    public $hiddenFieldsets = array(
        'general',
        'info',
        'detail',
        'jmetadata',
        'item_associations',
        'file',
        'file-vertical',
        'requirements',
        'options',
        'advanced',
        'mailchimp',
        'basic'
    );


    public function init()
    {
        // Load the OSDownloads extension
        $baseLang = Factory::getApplication()->isClient('site') ? JPATH_SITE : JPATH_ADMINISTRATOR;
        Factory::getLanguage()->load('com_osdownloads', $baseLang . '/components/com_osdownloads');
        $osdownloads = FreeComponentSite::getInstance();
        $osdownloads->loadLibrary();

        $this->list           = $this->getList();
        $this->popupAnimation = $osdownloads->params->get('popup_animation', 'fade');
        $this->isPro          = $osdownloads->isPro();

        parent::init();
    }

    public function getList()
    {
        $db  = Factory::getDbo();
        $app = Factory::getApplication();

        $app->setUserState("com_osdownloads.files.filter_order", $this->params->get('ordering', 'ordering'));
        $app->setUserState("com_osdownloads.files.filter_order_Dir", $this->params->get('ordering_dir', 'asc'));

        $osdownloads = FreeComponentSite::getInstance();
        $model       = $osdownloads->getModel('Item');
        $query       = $model->getItemQuery();
        $query->where("cate_id = " . $db->quote($this->params->get('category', 0)));
        $db->setQuery($query);

        $rows = $db->loadObjectList();

        if (!empty($rows)) {
            JLoader::register('ContentHelperRoute', JPATH_SITE . '/components/com_content/helpers/route.php');

            foreach ($rows as $row) {
                \OSDownloadsHelper::prepareItem($row);
            }
        }

        return $rows;
    }

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
        $form = new JForm('com_osdownloads.download');

        Factory::getApplication()->triggerEvent(
            'onContentPrepareForm',
            array(
                $form,
                array(
                    'catid' => @$data->cate_id,
                )
            )
        );

        return $form;
    }

    public function get($attribute)
    {
        if (isset($this->$attribute)) {
            return $this->$attribute;
        }

        return null;
    }
}
