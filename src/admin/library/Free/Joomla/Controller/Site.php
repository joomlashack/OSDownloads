<?php
/**
 * @package   OSDownloads
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2014 Alledia.com, All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace Alledia\OSDownloads\Free\Joomla\Controller;

defined('_JEXEC') or die();

use Alledia\Framework\Factory;
use Alledia\Framework\Joomla\Controller\Base as BaseController;
use Alledia\OSDownloads\Free\Joomla\Component\Site as FreeComponentSite;
use Alledia\OSDownloads\Free\File;
use JRequest;
use JModelLegacy;


class Site extends BaseController
{
    public function display($cachable = false, $urlparams = false)
    {
        $view = JRequest::getCmd("view", "category");
        JRequest::setVar("view", $view);
        parent::display();
    }

    protected function processEmailRequirement(&$item)
    {
        $app              = Factory::getApplication();
        $component        = FreeComponentSite::getInstance();
        $params           = $app->getParams('com_osdownloads');
        $mailchimpConnect = $params->get("connect_mailchimp", 0);
        $mailchimpAPIKey  = $params->get("mailchimp_api", 0);
        $mailchimpListId  = $params->get("list_id", 0);

        $email = JRequest::getVar("email");

        // Must verify the e-mail before download?
        if ($item->require_user_email == 1 || ($item->require_user_email == 2 && !empty($email))) {

            $email = filter_var($email, FILTER_VALIDATE_EMAIL);
            if (empty($email)) {
                JRequest::setVar("layout", "error_invalid_email");

                return false;
            }

            // Store the e-mail
            $modelEmail = $component->getModel('Email');
            $emailRow = $modelEmail->insert($email, $item->id);

            // Send to Mail Chimp without validation
            if ($mailchimpConnect) {
                $this->addEmailToMailchimpList($item, $email, $mailchimpAPIKey, $mailchimpListId);
            }

            if (!$emailRow) {
                JRequest::setVar("layout", "error_invalid_email");

                return false;
            }
        }

        JRequest::setVar("layout", "thankyou");

        return true;
    }

    protected function processRequirements(&$item)
    {
        if ($item->require_agree == 1) {
            $agree = (int) JRequest::getVar('agree');

            if ($agree != 1) {
                JRequest::setVar("layout", "error_invalid_data");

                return false;
            }
        }

        return true;
    }

    /**
     * Task to route the download workflow
     *
     * @return void
     */
    public function routedownload()
    {
        $app                  = Factory::getApplication();
        $component            = FreeComponentSite::getInstance();
        $params               = $app->getParams('com_osdownloads');
        $downloadEmailContent = $params->get("download_email_content", false);
        $id                   = (int) JRequest::getVar("id");

        $model = $component->getModel('Item');
        $item  = $model->getItem($id);

        if (empty($item)) {
            JError::raiseWarning(404, JText::_("COM_OSDOWNLOADS_THIS_DOWNLOAD_ISNT_AVAILABLE"));
            return;
        }

        if ($this->processRequirements($item)) {
            $this->processEmailRequirement($item);
        }

        JRequest::setVar("view", "item");

        $this->display();
    }

    public function download()
    {
        $id = (int) JRequest::getVar('id');

        $component = FreeComponentSite::getInstance();
        $model     = $component->getModel('Item');

        $model->incrementDownloadCount($id);

        JRequest::setVar("view", "download");
        $this->display();
    }
}
