<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2018 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace Alledia\OSDownloads\Free\Joomla\View\Site;

defined('_JEXEC') or die();

use Alledia\Framework\Factory;
use Alledia\OSDownloads\Free\Joomla\Component\Site as FreeComponentSite;
use Alledia\OSDownloads\Free\Factory as OSDFactory;
use Exception;
use JHtml;
use Joomla\CMS\Application\SiteApplication;
use Joomla\Registry\Registry;
use JText;
use OSDownloadsModelItem;

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
     * @var OSDownloadsModelItem
     */
    protected $model = null;

    /**
     * @var object
     */
    protected $category = null;

    /**
     * @param string $tpl
     *
     * @return void
     * @throws Exception
     */
    public function display($tpl = null)
    {
        /** @var SiteApplication $app */
        $app       = Factory::getApplication();
        $component = FreeComponentSite::getInstance();
        $container = OSDFactory::getContainer();

        $this->model  = $component->getModel('Item');
        $this->params = $app->getParams();
        $this->itemId = (int)$app->input->getInt('Itemid');

        $menu = $app->getMenu()->getActive();
        if ($menu) {
            $this->params->def('page_heading', $this->params->get('page_title', $menu->title));
        }

        $id = (int)$app->input->getInt('id') ?: (int)$this->params->get('document_id');

        $this->item = $this->model->getItem($id);

        if (empty($this->item)) {
            throw new Exception(JText::_('COM_OSDOWNLOADS_ERROR_DOWNLOAD_NOT_AVAILABLE'), 404);
        }

        // Breadcrumbs
        $container->helperView->buildFileBreadcrumbs($this->item);

        // Load the extension
        $component->loadLibrary();

        $this->isPro    = $component->isPro();
        $this->category = $container->helperSEF->getCategory($this->item->cate_id);

        // Process content plugins
        $this->item->brief         = JHtml::_('content.prepare', $this->item->brief);
        $this->item->description_1 = JHtml::_('content.prepare', $this->item->description_1);
        $this->item->description_2 = JHtml::_('content.prepare', $this->item->description_2);
        $this->item->description_3 = JHtml::_('content.prepare', $this->item->description_3);

        /**
         * Temporary backward compatibility for user's template overrides.
         *
         * @var array
         * @deprecated  1.9.9  Use JPathway and the breadcrumb module instead to display the breadcrumbs
         */
        $this->paths = array();

        parent::display($tpl);
    }
}
