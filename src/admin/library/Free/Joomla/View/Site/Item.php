<?php
/**
 * @package   OSDownloads
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2016 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace Alledia\OSDownloads\Free\Joomla\View\Site;

defined('_JEXEC') or die();

use Alledia\Framework\Factory;
use Alledia\OSDownloads\Free\Joomla\Component\Site as FreeComponentSite;
use JRoute;
use JText;

if (!class_exists('JViewLegacy')) {
    jimport('legacy.view.legacy');
}


class Item extends Base
{
    public function display($tpl = null)
    {
        $app       = Factory::getApplication();
        $component = FreeComponentSite::getInstance();
        $model     = $component->getModel('Item');
        $params    = $app->getParams('com_osdownloads');
        $id        = (int) $app->input->getInt('id');
        $itemId    = (int) $app->input->get('Itemid');

        if (empty($id)) {
            $id = (int) $params->get("document_id");
        }

        $item = $model->getItem($id);

        if (empty($item)) {
            throw new \Exception(JText::_('COM_OSDOWNLOADS_THIS_DOWNLOAD_ISNT_AVAILABLE'), 404);
        }

        $paths = null;
        $this->buildBreadcrumbs($paths, $item);

        // Load the extension
        $component->loadLibrary();
        $isPro = $component->isPro();

        // Add the agreementLink property
        if (!empty($item)) {
            $item->agreementLink = '';
            if ((bool)$item->require_agree) {
                $item->agreementLink = JRoute::_('index.php?option=com_content&view=article&id=' . (int)  $item->agreement_article_id);
            }
        }

        $this->assignRef("item", $item);
        $this->assignRef("itemId", $itemId);
        $this->assignRef("paths", $paths);
        $this->assignRef("params", $params);
        $this->assignRef("isPro", $isPro);
        $this->assignRef('model', $model);

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

    protected function buildBreadcrumbs(&$paths, $item)
    {
        $this->buildPath($paths, $item->cate_id);

        $app     = Factory::getApplication();
        $pathway = $app->getPathway();
        $itemID  = $app->input->getInt('Itemid');

        $countPaths = count($paths) - 1;
        for ($i = $countPaths; $i >= 0; $i--) {
            $pathway->addItem(
                $paths[$i]->title,
                JRoute::_("index.php?option=com_osdownloads&view=downloads&id={$paths[$i]->id}"."&Itemid={$itemID}")
            );
        }
    }
}
