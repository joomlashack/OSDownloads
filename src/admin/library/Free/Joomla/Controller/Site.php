<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace Alledia\OSDownloads\Free\Joomla\Controller;

defined('_JEXEC') or die();

use Alledia\Framework\Factory;
use Alledia\Framework\Joomla\Controller\Base as BaseController;
use Alledia\OSDownloads\Free\Joomla\Component\Site as FreeComponentSite;
use Alledia\OSDownloads\Free\Helper;

class Site extends BaseController
{
    public function display($cachable = false, $urlparams = false)
    {
        $app = \JFactory::getApplication();

        $view = $app->input->getCmd('view', 'category');
        $app->input->set('view', $view);
        parent::display();
    }

    protected function processEmailRequirement(&$item)
    {
        $app              = Factory::getApplication();
        $component        = FreeComponentSite::getInstance();
        $params           = $app->getParams('com_osdownloads');
        $mailchimpConnect = $params->get("connect_mailchimp", 0);

        $email = trim($app->input->getString('email'));

        // Must verify the e-mail before download?
        if ($item->require_user_email == 1 || ($item->require_user_email == 2 && $email != '')) {
            if (!Helper::validateEmail($email)) {
                $app->input->set('layout', 'error_invalid_email');

                return false;
            }

            // Store the e-mail
            $modelEmail = $component->getModel('Email');
            $emailRow   = $modelEmail->insert($email, $item->id);

            // Send to Mail Chimp without validation
            if ($mailchimpConnect) {
                $emailRow->addToMailchimpList();
            }

            if (!$emailRow) {
                $app->input->set('layout', 'error_invalid_email');

                return false;
            }
        }

        $app->input->set('layout', 'thankyou');

        return true;
    }

    protected function processRequirements(&$item)
    {
        if ($item->require_agree == 1) {
            $app = \JFactory::getApplication();

            $agree = $app->input->getInt('agree');

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

        $model = $component->getModel('Item');
        $item  = $model->getItem($id);

        if (empty($item)) {
            throw new \Exception(\JText::_('COM_OSDOWNLOADS_THIS_DOWNLOAD_ISNT_AVAILABLE'), 404);
        }

        if ($this->processRequirements($item)) {
            $this->processEmailRequirement($item);
        }

        $app->input->set('view', 'item');

        $this->display();
    }

    public function download()
    {
        $app = \JFactory::getApplication();

        $app->input->set('view', 'download');
        $this->display();
    }
}
