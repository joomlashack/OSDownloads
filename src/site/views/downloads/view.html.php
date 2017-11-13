<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2017 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

require_once JPATH_SITE . '/components/com_osdownloads/models/item.php';

use Alledia\OSDownloads\Free\Joomla\View;
use Alledia\OSDownloads\Free\HelperRoute;

class OSDownloadsViewDownloads extends View\Site\Base
{
    /**
     * @var object[]
     */
    protected $categories = null;

    /**
     * @var bool
     */
    protected $showCategoryFilter = null;

    /**
     * @var object[]
     */
    protected $items = null;

    /**
     * @var object[]
     */
    protected $paths = null;

    /**
     * @var JPagination
     */
    protected $pagination = null;

    /**
     * @var \Joomla\Registry\Registry
     */
    protected $params = null;

    /**
     * @var bool
     */
    protected $isPro = null;

    public function display($tpl = null)
    {
        $app                 = JFactory::getApplication();
        $db                  = JFactory::getDbo();
        $this->params        = clone($app->getParams('com_osdownloads'));
        $categoryIDs         = (array)$this->params->get("category_id");
        $includeChildFiles   = (bool)$this->params->get('include_child_files', 0);
        $showChildCategories = (bool)$this->params->get('show_child_categories', 1);

        // Load the extension
        $extension = Alledia\Framework\Factory::getExtension('OSDownloads', 'component');
        $extension->loadLibrary();

        $limit      = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');
        $limitstart = $app->getUserStateFromRequest('osdownloads.request.limitstart', 'limitstart', 0, 'int');

        $app->setUserState("com_osdownloads.files.filter_order", $this->params->get('ordering', 'doc.ordering'));
        $app->setUserState("com_osdownloads.files.filter_order_Dir", $this->params->get('ordering_dir', 'asc'));

        // Paths
        $paths = array();

        $id = $app->input->getInt('id');

        if (empty($id)) {
            if (count($categoryIDs) == 1) {
                $id = $categoryIDs[0];
            }
        }

        if (!empty($id)) {
            $this->buildPath($paths, $id);

            $categoryIDs = (array)$id;
        }

        $model = JModelLegacy::getInstance('OSDownloadsModelItem');

        $query = $model->getItemQuery();

        $query->select('cat.access as cat_access');

        if (!empty($categoryIDs)) {
            $ors = array(sprintf('cate_id IN (%s)', join(',', $categoryIDs)));
            if ($includeChildFiles) {
                $ors[] = sprintf('cat.parent_id IN (%s)', join(',', $categoryIDs));
            }
            $query->where(sprintf('(%s)', join(' OR ', $ors)));
        }

        $db->setQuery($query);


        // Pagination
        $db->setQuery($query)->execute();

        $total = $db->getNumRows();

        jimport('joomla.html.pagination');
        $pagination = new JPagination($total, $limitstart, $limit);

        // Items
        $db->setQuery($query, $pagination->limitstart, $pagination->limit);
        $items = $db->loadObjectList();
        foreach ($items as &$item) {
            $item->agreementLink = '';
            if ((bool)$item->require_agree) {
                JLoader::register('ContentHelperRoute', JPATH_SITE . '/components/com_content/helpers/route.php');
                $item->agreementLink = JRoute::_(ContentHelperRoute::getArticleRoute($item->agreement_article_id));
            }
        }


        $user   = JFactory::getUser();
        $groups = $user->getAuthorisedViewLevels();

        if (!isset($items) || (count($items) && !in_array($items[0]->cat_access, $groups))) {
            throw new Exception(JText::_("COM_OSDOWNLOADS_THIS_CATEGORY_ISNT_AVAILABLE"), 404);
        }

        // Categories
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__categories AS c')
            ->where(
                array(
                    'extension = ' . $db->quote('com_osdownloads'),
                    'published = 1',
                    sprintf('access IN (%s)', join(',', $groups))
                )
            )
            ->order('c.lft ASC');

        if ($categoryIDs) {
            $ors = array(sprintf('id IN (%s)', join(',', $categoryIDs)));
            if ($showChildCategories) {
                $ors[] = sprintf('parent_id IN (%s)', join(',', $categoryIDs));
            }
            $query->where(sprintf('(%s)', join(' OR ', $ors)));
        }

        $db->setQuery($query);
        $categories = $db->loadObjectList();

        // Category filter
        $showCategoryFilter = $this->params->get('show_category_filter', false);

        $this->buildBreadcrumbs($paths);

        $this->categories         = $categories;
        $this->showCategoryFilter = $showCategoryFilter;
        $this->items              = $items;
        $this->paths              = $paths;
        $this->pagination         = $pagination;
        $this->isPro              = $extension->isPro();

        parent::display($tpl);
    }

    public function buildPath(&$paths, $categoryID)
    {
        if (empty($categoryID)) {
            return;
        }

        $db = JFactory::getDBO();
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

    protected function buildBreadcrumbs($paths)
    {
        $app     = JFactory::getApplication();
        $pathway = $app->getPathway();
        $itemID  = $app->input->getInt('Itemid');

        $countPaths = count($paths) - 1;
        for ($i = $countPaths; $i >= 0; $i--) {
            $pathway->addItem(
                $paths[$i]->title,
                JRoute::_(HelperRoute::getFileRoute($paths[$i]->id, $itemID))
            );
        }
    }
}
