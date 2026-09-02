<?php

/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2005-2026 Joomlashack.com. All rights reserved
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

namespace Alledia\OSDownloads\Free\Joomla\Controller;

use Alledia\Framework\Joomla\Controller\AbstractBase;
use Alledia\OSDownloads\Factory;
use Alledia\OSDownloads\Free\Helper\Helper;
use Alledia\OSDownloads\Free\Joomla\Component\Site as FreeComponentSite;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Language\Text;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die();

// phpcs:enable PSR1.Files.SideEffects

class Site extends AbstractBase
{
    /**
     * Length of time, in seconds, that an approved download remains available.
     */
    protected const DOWNLOAD_AUTHORIZATION_TTL = 300;

    /**
     * Authorize the current session to download a document.
     *
     * @param int $documentId
     *
     * @return void
     * @throws \Exception
     */
    protected function authorizeDownload(int $documentId): void
    {
        $session = Factory::getApplication()->getSession();

        $session->set(
            'com_osdownloads.download.' . $documentId,
            time() + static::DOWNLOAD_AUTHORIZATION_TTL
        );
    }

    /**
     * Check whether the current session may download a document.
     *
     * @param object $item
     *
     * @return bool
     * @throws \Exception
     */
    public static function isDownloadAuthorized(object $item): bool
    {
        if (empty($item->require_user_email) && empty($item->require_agree)) {
            return true;
        }

        $session = Factory::getApplication()->getSession();
        $key     = 'com_osdownloads.download.' . (int)$item->id;
        $expires = (int)$session->get($key, 0);

        if ($expires >= time()) {
            return true;
        }

        $session->clear($key);

        return false;
    }

    /**
     * @inheritDoc
     */
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
    protected function processEmailRequirement(object $item): bool
    {
        /** @var SiteApplication $app */
        $app       = Factory::getApplication();
        $component = FreeComponentSite::getInstance();

        $email = trim($app->input->getString('require_email'));

        // Must verify the e-mail before download?
        if ($item->require_user_email == 1 || ($item->require_user_email == 2 && $email != '')) {
            if (!Helper::validateEmail($email)) {
                $app->input->set('layout', 'error_invalid_email');

                return false;
            }

            /** @var \OsdownloadsModelEmail $modelEmail */
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
    protected function processRequirements(object $item): bool
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
    public function routedownload(): void
    {
        $app       = Factory::getApplication();
        $component = FreeComponentSite::getInstance();
        $id        = $app->input->getInt('id');

        /** @var \OsdownloadsModelItem $model */
        $model = $component->getModel('Item');
        $item  = $model->getItem($id);

        if (empty($item)) {
            throw new \Exception(Text::_('COM_OSDOWNLOADS_ERROR_DOWNLOAD_NOT_AVAILABLE'), 404);
        }

        $requirementsPassed = $this->processRequirements($item);
        $emailPassed        = false;

        if ($requirementsPassed) {
            $emailPassed = $this->processEmailRequirement($item);
        }

        if ($requirementsPassed && $emailPassed) {
            $this->authorizeDownload((int)$item->id);
        }

        $app->input->set('view', 'item');

        $this->display();
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function download(): void
    {
        $app = Factory::getApplication();

        $app->input->set('view', 'download');
        $this->display();
    }
}
