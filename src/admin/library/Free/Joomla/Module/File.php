<?php

/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2005-2025 Joomlashack.com. All rights reserved
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
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
 * along with OSDownloads.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace Alledia\OSDownloads\Free\Joomla\Module;

use Alledia\Framework\Joomla\Extension\AbstractFlexibleModule;
use Alledia\OSDownloads\Factory;
use Alledia\OSDownloads\Free\Helper\Helper as FreeHelper;
use Alledia\OSDownloads\Free\Joomla\Component\Site as FreeComponentSite;
use Joomla\CMS\Form\Form;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die();

// phpcs:enable PSR1.Files.SideEffects

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
     * @var int
     */
    public $itemId = null;

    /**
     * @var object[]
     */
    protected $list = null;

    /**
     * @var object
     */
    public $item = null;

    /**
     * @var string
     */
    protected $popupAnimation = null;

    /**
     * @var bool
     */
    public $isPro = null;

    /**
     * @var Form
     */
    protected $form = null;

    /**
     * @var bool
     */
    protected $show_options = true;

    /**
     * @var string
     */
    public $buttonClasses = null;

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function init()
    {
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
     * Duplicates the method from DisplayData class
     *
     * @return  ?Form
     */
    /**
     * @return ?Form
     */
    public function getForm(): ?Form
    {
        return $this->form instanceof Form ? $this->form : null;
    }

    /**
     * @param Form $form
     *
     * @return void
     */
    public function setForm(Form $form)
    {
        $this->form = $form;
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
