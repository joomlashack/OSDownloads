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

namespace Alledia\OSDownloads\Free\Joomla\Controller;

defined('_JEXEC') or die();

use Alledia\Framework\Joomla\Controller\Base as BaseController;
use Alledia\OSDownloads\Factory;
use Alledia\OSDownloads\Free\Joomla\Component\Site as FreeComponentSite;
use Alledia\OSDownloads\Free\Helper\Helper;
use Alledia\OSDownloads\Free\Joomla\Model\Email;


class Site extends BaseController
{
    public function display($cachable = false, $urlparams = false)
    {
        $app = Factory::getApplication();

        $view = $app->input->getCmd('view', 'category');
        $app->input->set('view', $view);
        parent::display();
    }

    /**
     * @param object $item
     *
     * @return bool
     * @throws \Exception
     */
    protected function processEmailRequirement($item)
    {
        /** @var \JApplicationSite $app */
        $app       = Factory::getApplication();
        $component = FreeComponentSite::getInstance();

        $email = trim($app->input->getString('require_email'));

        // Must verify the e-mail before download?
        if ($item->require_user_email == 1 || ($item->require_user_email == 2 && $email != '')) {
            if (!Helper::validateEmail($email)) {
                $app->input->set('layout', 'error_invalid_email');

                return false;
            }

            /** @var \OSDownloadsModelEmail $modelEmail */
            $modelEmail = $component->getModel('Email');
            if (!$modelEmail->insert($email, $item->id)) {
                $app->input->set('layout', 'error_invalid_email');

                return false;
            }
        }

        $app->input->set('layout', 'thankyou');

        return true;
    }

    /**
     * @param object $item
     *
     * @return bool
     * @throws \Exception
     */
    protected function processRequirements($item)
    {
        if ($item->require_agree == 1) {
            $app = Factory::getApplication();

            $agree = $app->input->getInt('require_agree');

            if ($agree != 1) {
                $app->input->set('layout', 'error_invalid_data');

                return false;
            }
        }

        return true;
    }

    /**
     * Task to route the download workflow
     *
     * @return void
     * @throws \Exception
     */
    public function routedownload()
    {
        $app       = Factory::getApplication();
        $component = FreeComponentSite::getInstance();
        $id        = $app->input->getInt('id');

        /** @var \OSDownloadsModelItem $model */
        $model = $component->getModel('Item');
        $item  = $model->getItem($id);

        if (empty($item)) {
            throw new \Exception(\JText::_('COM_OSDOWNLOADS_ERROR_DOWNLOAD_NOT_AVAILABLE'), 404);
        }

        if ($this->processRequirements($item)) {
            $this->processEmailRequirement($item);
        }

        $app->input->set('view', 'item');

        $this->display();
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function download()
    {
        $app = Factory::getApplication();

        $app->input->set('view', 'download');
        $this->display();
    }
}
