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
use Joomla\Registry\Registry;
use JText;

if (!class_exists('JViewLegacy')) {
    jimport('legacy.view.legacy');
}


class Item extends Base
{
    /**
     * @var object
     */
    public $item = null;

    /**
     * @var int
     */
    public $itemId = null;

    /**
     * @var object[]
     */
    protected $paths = null;

    /**
     * @var Registry
     */
    public $params = null;

    /**
     * @var bool
     */
    public $isPro = null;

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
