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
use Alledia\OSDownloads\Free\Factory as OSDFactory;
use Alledia\OSDownloads\Free\Helper\View as HelperView;
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
        $container = OSDFactory::getContainer();
        $model     = $component->getModel('Item');
        $params    = $app->getParams();
        $id        = (int) $app->input->getInt('id');
        $itemId    = (int) $app->input->getInt('Itemid');

        if (empty($id)) {
            $id = (int) $params->get("document_id");
        }

        $item = $model->getItem($id);

        if (empty($item)) {
            throw new \Exception(JText::_('COM_OSDOWNLOADS_THIS_DOWNLOAD_ISNT_AVAILABLE'), 404);
        }

        // Breadcrumbs
        $container->helperView->buildFileBreadcrumbs($item);

        // Load the extension
        $component->loadLibrary();


        // Process the content plugins
        JPluginHelper::importPlugin('content');

        // Make compatible with content plugins
        $item->text = null;

        $dispatcher = JEventDispatcher::getInstance();
        $offset     = 0;
        $dispatcher->trigger('onContentPrepare', array('com_osdownloads.file', &$item, &$item->params, $offset));

        $afterDisplayTitle    = $dispatcher->trigger(
            'onContentAfterTitle',
            array('com_osdownloads.file', &$item, &$item->params, $offset)
        );
        $beforeDisplayContent = $dispatcher->trigger(
            'onContentBeforeDisplay',
            array('com_osdownloads.file', &$item, &$item->params, $offset)
        );
        $afterDisplayContent  = $dispatcher->trigger(
            'onContentAfterDisplay',
            array('com_osdownloads.file', &$item, &$item->params, $offset)
        );

        $item->event = (object)array(
            'afterDisplayTitle'    => trim(implode("\n", $afterDisplayTitle)),
            'beforeDisplayContent' => trim(implode("\n", $beforeDisplayContent)),
            'afterDisplayContent'  => trim(implode("\n", $afterDisplayContent))
        );

        $this->item      = $item;
        $this->itemId    = $itemId;
        $this->params    = $params;
        $this->isPro     = $component->isPro();
        $this->model     = $model;
        $this->category  = $container->helperSEF->getCategory($item->cate_id);

        /**
         * Temporary backward compatibility for user's template overrides.
         *
         * @var array
         * @deprecated  1.9.9  Use JPathway and the breadcrumb module instead to display the breadcrumbs
         */
        $this->paths  = array();

        parent::display($tpl);
    }
}
