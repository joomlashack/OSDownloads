<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2005-2022 Joomlashack.com. All rights reserved
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

namespace Alledia\OSDownloads\Free\Joomla\View\Site;

defined('_JEXEC') or die();

use Alledia\OSDownloads\Factory;
use Alledia\OSDownloads\Free\Joomla\Component\Site as FreeComponentSite;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Form\Form;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;

class Item extends Base
{
    /**
     * @var object
     */
    public $item = null;

    /**
     * @var int
     */
    public $itemId = null;

    /**
     * @var Registry
     */
    public $params = null;

    /**
     * @var bool
     */
    public $isPro = null;

    /**
     * @var object
     */
    protected $category = null;

    /**
     * @param string $tpl
     *
     * @return void
     * @throws \Exception
     */
    public function display($tpl = null)
    {
        /** @var SiteApplication $app */
        $app       = Factory::getApplication();
        $component = FreeComponentSite::getInstance();
        $container = Factory::getPimpleContainer();

        $this->model  = $component->getModel('Item');
        $this->params = $app->getParams();
        $this->itemId = $app->input->getInt('Itemid');

        if ($activeMenu = $app->getMenu()->getActive()) {
            $this->params->def('page_heading', $this->params->get('page_title', $activeMenu->title));
        }

        $id = $app->input->getInt('id') ?: (int)$this->params->get('document_id');

        $this->item = $this->model->getItem($id);

        if (empty($this->item)) {
            throw new \Exception(Text::_('COM_OSDOWNLOADS_ERROR_DOWNLOAD_NOT_AVAILABLE'), 404);
        }

        // Breadcrumbs
        $container->helperView->buildFileBreadcrumbs($this->item);

        // Load the extension
        $component->loadLibrary();

        $this->isPro    = $component->isPro();
        $this->category = $container->helperSEF->getCategory($this->item->cate_id);

        // Process content plugins
        $this->item->brief         = HTMLHelper::_('content.prepare', $this->item->brief);
        $this->item->description_1 = HTMLHelper::_('content.prepare', $this->item->description_1);
        $this->item->description_2 = HTMLHelper::_('content.prepare', $this->item->description_2);
        $this->item->description_3 = HTMLHelper::_('content.prepare', $this->item->description_3);

        parent::display($tpl);
    }
}
