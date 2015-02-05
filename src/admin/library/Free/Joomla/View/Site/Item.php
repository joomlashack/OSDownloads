<?php
/**
 * @package   OSDownloads
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2014 Alledia.com, All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace Alledia\OSDownloads\Free\Joomla\View\Site;

defined('_JEXEC') or die();

use Alledia\Framework\Factory;
use Alledia\OSDownloads\Free\Joomla\Component\Site as FreeComponentSite;
use JTable;
use JError;
use JRequest;
use JRoute;
use JText;

jimport('legacy.view.legacy');


class Item extends \JViewLegacy
{
    public function display($tpl = null)
    {
        $app       = Factory::getApplication();
        $component = FreeComponentSite::getInstance();
        $model     = $component->getModel('Item');
        $params    = $app->getParams('com_osdownloads');
        $id        = (int) $params->get("document_id");
        $itemId    = (int) $app->input->get('Itemid');

        if (JRequest::getVar("id")) {
            $id = (int) JRequest::getVar("id");
        }

        $item = $model->getItem($id);

        if (empty($item)) {
            JError::raiseWarning(404, JText::_("COM_OSDOWNLOADS_THIS_DOWNLOAD_ISNT_AVAILABLE"));
            return;
        }

        $paths = null;
        $this->buildBreadcrumbs($paths, $item);

        // Load the extension
        $component->loadLibrary();
        $isPro = $component->isPro();

        // Add the agreementLink property
        if (!empty($item)) {
            $item->agreementLink = '';
            if ((bool)$item->require_email) {
                $item->agreementLink = JRoute::_('index.php?option=com_content&view=article&id=' . (int)  $item->agreement_article_id);
            }
        }

        $this->assignRef("item", $item);
        $this->assignRef("itemId", $itemId);
        $this->assignRef("paths", $paths);
        $this->assignRef("params", $params);
        $this->assignRef("isPro", $isPro);

        parent::display($tpl);
    }

    public function buildPath(&$paths, $categoryID)
    {
        if (empty($categoryID)) {
            return;
        }

        $db = Factory::getDBO();
        $db->setQuery("SELECT *
                       FROM `#__categories`
                       WHERE extension='com_osdownloads'
                           AND id = " . $db->q((int) $categoryID));
        $category = $db->loadObject();

        if ($category) {
            $paths[] = $category;
        }

        if ($category && $category->parent_id) {
            $this->buildPath($paths, $category->parent_id);
        }
    }

    protected function buildBreadcrumbs(&$paths, $item) {
        $this->buildPath($paths, $item->cate_id);

        $app     = Factory::getApplication();
        $pathway = $app->getPathway();
        $itemID  = JRequest::getVar("Itemid", null, 'default', 'int');

        $countPaths = count($paths) - 1;
        for ($i = $countPaths; $i >= 0; $i--) {
            $pathway->addItem(
                $paths[$i]->title,
                JRoute::_("index.php?option=com_osdownloads&view=downloads&id={$paths[$i]->id}"."&Itemid={$itemID}")
            );
        }
    }
}
