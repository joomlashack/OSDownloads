<?php
/**
 * @package   OSDownloads-Pro
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2005-2021 Joomlashack.com. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 *
 * This file is part of OSDownloads-Pro.
 *
 * OSDownloads-Pro is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * OSDownloads-Pro is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OSDownloads-Pro.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Alledia\OSDownloads\Free;

use Alledia\OSDownloads\Factory;
use JForm;
use Joomla\CMS\Form\Form;
use Joomla\Registry\Registry;

defined('_JEXEC') or die();

class DisplayData
{
    public $item;

    public $hiddenFieldsets = array('dummy');

    public $isPro;

    public $params;

    public $itemId;

    public function __construct($params = null)
    {
        $extension   = Factory::getExtension('OSDownloads', 'component');
        $this->isPro = $extension->isPro();

        if (empty($params)) {
            $params = new Registry();
        }
        $this->params = $params;
    }

    /**
     * Method to get the row form.
     *
     * @param array   $data     Data for the form.
     * @param boolean $loadData True if the form is to load its own data (default case), false if not.
     *
     * @return  ?Form
     *
     * @since   1.6
     */
    public function getForm($data = [], $loadData = true)
    {
        // Get the form.
        $form = new JForm('com_osdownloads.download');

        Factory::getApplication()->triggerEvent(
            'onContentPrepareForm',
            [
                $form,
                [
                    'catid' => $data->cate_id ?? null
                ]
            ]
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
