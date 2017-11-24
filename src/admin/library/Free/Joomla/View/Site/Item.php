<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2017 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace Alledia\OSDownloads\Free\Joomla\View\Site;

defined('_JEXEC') or die();

use Alledia\Framework\Factory;
use Alledia\OSDownloads\Free\Joomla\Component\Site as FreeComponentSite;
use Joomla\Registry\Registry;
use JRoute;
use JText;
use JPluginHelper;
use JEventDispatcher;
use stdClass;

if (!class_exists('JViewLegacy')) {
    jimport('legacy.view.legacy');
}


class Item extends Base
{
    /**
     * @var object
     */
    protected $item = null;

    /**
     * @var int
     */
    protected $itemId = null;

    /**
     * @var object[]
     */
    protected $paths = null;

    /**
     * @var Registry
     */
    protected $params = null;

    /**
     * @var bool
     */
    protected $isPro = null;

    /**
     * @var \OSDownloadsModelItem
     */
    protected $model = null;

    public function display($tpl = null)
    {
        $app       = Factory::getApplication();
        $component = FreeComponentSite::getInstance();
        $model     = $component->getModel('Item');
        $params    = $app->getParams('com_osdownloads');
        $id        = (int)$app->input->getInt('id');
        $itemId    = (int)$app->input->getInt('Itemid');

        if (empty($id)) {
            $id = (int)$params->get("document_id");
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

        // Process the content plugins
        JPluginHelper::importPlugin('content');

        $dispatcher = JEventDispatcher::getInstance();
        $offset = 0;
        $dispatcher->trigger('onContentPrepare', array('com_osdownloads.file', &$item, &$item->params, $offset));

        // Store the events for later
        $item->event = new stdClass;

        $results = $dispatcher->trigger('onContentAfterTitle', array('com_osdownloads.file', &$item, &$item->params, $offset));
        $item->event->afterDisplayTitle = trim(implode("\n", $results));

        $results = $dispatcher->trigger('onContentBeforeDisplay', array('com_osdownloads.file', &$item, &$item->params, $offset));
        $item->event->beforeDisplayContent = trim(implode("\n", $results));

        $results = $dispatcher->trigger('onContentAfterDisplay', array('com_osdownloads.file', &$item, &$item->params, $offset));
        $item->event->afterDisplayContent = trim(implode("\n", $results));

        $this->item   = $item;
        $this->itemId = $itemId;
        $this->paths  = $paths;
        $this->params = $params;
        $this->isPro  = $isPro;
        $this->model  = $model;

        parent::display($tpl);
    }

    public function buildPath(&$paths, $categoryID)
    {
        if (empty($categoryID)) {
            return;
        }

        $db = Factory::getDbo();
        $db->setQuery("SELECT *
                       FROM `#__categories`
                       WHERE extension='com_osdownloads'
                           AND id = " . $db->q((int)$categoryID));
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
                JRoute::_("index.php?option=com_osdownloads&view=downloads&id={$paths[$i]->id}&Itemid={$itemID}")
            );
        }
    }
}
