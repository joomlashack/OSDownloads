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

namespace Alledia\OSDownloads\Free\Joomla\View\Site;

use Alledia\Framework\Joomla\View\Site\AbstractBase;
use Alledia\OSDownloads\Factory;
use Exception;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die();
// phpcs:enable PSR1.Files.SideEffects

class Base extends AbstractBase
{
    /**
     * @inheritDoc
     */
    public function display($tpl = null)
    {
        $this->prepareDocument();

        parent::display($tpl);
    }

    /**
     * Method to prepares the document
     *
     * @return  void
     * @throws Exception
     */
    protected function prepareDocument()
    {
        $app    = Factory::getApplication();
        $menus  = $app->getMenu();
        $doc    = Factory::getDocument();
        $params = $app->getParams();

        // Because the application sets a default page title, we need to get it from the menu item itself
        $menu = $menus->getActive();

        if ($menu) {
            $params->def('page_heading', $params->get('page_title', $menu->title));
        } else {
            $params->def('page_heading', '');
        }

        $title = $params->get('page_title', '');

        if (empty($title)) {
            $title = $app->get('sitename');
        } elseif ($app->get('sitename_pagetitles', 0) == 1) {
            $title = Text::sprintf('JPAGETITLE', $app->get('sitename'), $title);
        } elseif ($app->get('sitename_pagetitles', 0) == 2) {
            $title = Text::sprintf('JPAGETITLE', $title, $app->get('sitename'));
        }

        $doc->setTitle($title);

        if ($params->get('menu-meta_description')) {
            $doc->setDescription($params->get('menu-meta_description'));
        }

        if ($params->get('menu-meta_keywords')) {
            $doc->setMetaData('keywords', $params->get('menu-meta_keywords'));
        }

        if ($params->get('robots')) {
            $doc->setMetaData('robots', $params->get('robots'));
        }
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
}
