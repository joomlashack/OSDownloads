<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2018 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace Alledia\OSDownloads\Free\Joomla\Controller;

defined('_JEXEC') or die();

use Alledia\Framework\Factory;
use Alledia\Framework\Joomla\Controller\Base as BaseController;
use Alledia\OSDownloads\Free\Joomla\Component\Site as FreeComponentSite;
use Alledia\OSDownloads\Free\Helper\Helper;
use Alledia\OSDownloads\Free\Joomla\Model\Email;


class Site extends BaseController
{
    public function display($cachable = false, $urlparams = false)
    {
        $app = \JFactory::getApplication();

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
            $app = \JFactory::getApplication();

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
        $app = \JFactory::getApplication();

        $app->input->set('view', 'download');
        $this->display();
    }
}
