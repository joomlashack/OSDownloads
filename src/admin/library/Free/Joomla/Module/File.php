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

use Alledia\Framework\Factory;
use Alledia\Framework\Joomla\Extension\AbstractFlexibleModule;
use Alledia\OSDownloads\Free\Helper\Helper as FreeHelper;
use Alledia\OSDownloads\Free\Joomla\Component\Site as FreeComponentSite;
use Joomla\CMS\Form\Form;

class File extends AbstractFlexibleModule
{
    /**
     * @var string[]
     */
    public $hiddenFieldsets = [
        'advanced',
        'basic',
        'detail',
        'file',
        'file-vertical',
        'general',
        'info',
        'item_associations',
        'jmetadata',
        'mailchimp',
        'options',
        'requirements',
    ];

    /**
     * @var object[]
     */
    protected $list = null;

    /**
     * @var string
     */
    protected $popupAnimation = null;

    /**
     * @var bool
     */
    protected $isPro = null;

    /**
     * @inheritDoc
     * @throws \Exception
     */
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

    /**
     * @return object[]
     * @throws \Exception
     */
    public function getList(): array
    {
        $db  = Factory::getDbo();
        $app = Factory::getApplication();

        $app->setUserState('com_osdownloads.files.filter_order', $this->params->get('ordering', 'ordering'));
        $app->setUserState('com_osdownloads.files.filter_order_Dir', $this->params->get('ordering_dir', 'asc'));

        $osdownloads = FreeComponentSite::getInstance();
        $model       = $osdownloads->getModel('Item');
        $query       = $model->getItemQuery();
        $query->where('cate_id = ' . $db->quote($this->params->get('category', 0)));
        $db->setQuery($query);

        if ($rows = $db->loadObjectList()) {
            foreach ($rows as $row) {
                FreeHelper::prepareItem($row);
            }
        }

        return $rows;
    }

    /**
     * Method to get the row form.
     *
     * @param object|array $data
     *
     * @return  ?Form
     * @throws \Exception
     */
    public function getForm($data = []): ?Form
    {
        if ($form = new Form('com_osdownloads.download')) {
            $data = (object)$data;

            Factory::getApplication()->triggerEvent(
                'onContentPrepareForm',
                [$form, ['catid' => empty($data->cate_id) ? null : $data->cate_id]]
            );

            return $form;
        }

        return null;
    }

    /**
     * @param string $property
     *
     * @return mixed
     */
    public function get(string $property)
    {
        if (empty($this->{$property})) {
            return null;
        }

        return $this->{$property};
    }
}
