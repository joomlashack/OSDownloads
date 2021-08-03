<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2005-2021 Joomlashack.com. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 *
 * This file is part of OSDownloads.
 *
 * OSDownloads is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * OSDownloads is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OSDownloads.  If not, see <http://www.gnu.org/licenses/>.
 */

defined('_JEXEC') or die();

require_once JPATH_SITE . '/components/com_osdownloads/models/item.php';

use Alledia\OSDownloads\Free\Joomla\View;
use Alledia\OSDownloads\Free\Factory;
use Alledia\OSDownloads\Free\Helper\View as HelperView;
use Joomla\CMS\Application\SiteApplication;

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
    public $params = null;

    /**
     * @var bool
     */
    public $isPro = null;

    public function display($tpl = null)
    {
        /** @var SiteApplication $app */
        $app                 = JFactory::getApplication();
        $db                  = JFactory::getDbo();
        $params              = $app->getParams();
        $includeChildFiles   = (bool)$params->get('include_child_files', 0);
        $showChildCategories = (bool)$params->get('show_child_categories', 1);

        $menu = $app->getMenu()->getActive();
        if ($menu) {
            $params->def('page_heading', $params->get('page_title', $menu->title));
        }

        // Load the extension
        $extension = Alledia\Framework\Factory::getExtension('OSDownloads', 'component');
        $extension->loadLibrary();

        $id = $app->input->getInt('id', 1);

        // If empty, we assume id = 1 to hit the Root category
        if (empty($id)) {
            $id = 1;
        }

        $model = JModelLegacy::getInstance('OSDownloadsModelItem');

        $app->setUserState('com_osdownloads.files.filter_order', $params->get('ordering', 'doc.ordering'));
        $app->setUserState('com_osdownloads.files.filter_order_Dir', $params->get('ordering_dir', 'asc'));

        /** @var JDatabaseQuery $query */
        $query = $model->getItemQuery();

        $query->select('cat.access as cat_access');

        $where = array(
            sprintf('cate_id = %s', $db->quote($id))
        );

        if ($includeChildFiles) {
            $where[] = sprintf('cat.parent_id = %s', $db->quote($id));
        }


        $query->where(sprintf('(%s)', join(' OR ', $where)));

        /*----------  Pagination  ----------*/

        $db->setQuery($query)->execute();

        $total = $db->getNumRows();

        $defaultLimit = $params->get('list_limit') ?: $app->get('list_limit');
        $limit        = $app->getUserStateFromRequest('osdownloads.request.list.limit', 'limit', $defaultLimit, 'int');
        $limitstart   = $app->getUserStateFromRequest('osdownloads.request.limitstart', 'limitstart', 0, 'int');

        $pagination = new JPagination($total, $limitstart, $limit);

        /*----------  Files  ----------*/
        $db->setQuery($query, $pagination->limitstart, $pagination->limit);

        JLoader::register('ContentHelperRoute', JPATH_SITE . '/components/com_content/helpers/route.php');
        $items = $db->loadObjectList();
        foreach ($items as &$item) {
            $item->agreementLink = '';
            if ((bool)$item->require_agree) {
                $item->agreementLink = JRoute::_(ContentHelperRoute::getArticleRoute($item->agreement_article_id));
            }

            $item->brief         = JHtml::_('content.prepare', $item->brief);
            $item->description_1 = JHtml::_('content.prepare', $item->description_1);
            $item->description_2 = JHtml::_('content.prepare', $item->description_2);
            $item->description_3 = JHtml::_('content.prepare', $item->description_3);
        }

        $user   = JFactory::getUser();
        $groups = $user->getAuthorisedViewLevels();

        if (!isset($items) || (count($items) && !in_array($items[0]->cat_access, $groups))) {
            throw new Exception(JText::_('COM_OSDOWNLOADS_THIS_CATEGORY_ISNT_AVAILABLE'), 404);
        }

        /*----------  Child Categories  ----------*/
        $categories = array();

        if ($showChildCategories) {
            $query = $db->getQuery(true)
                ->select('*')
                ->from('#__categories AS c')
                ->where(
                    array(
                        'extension = ' . $db->quote('com_osdownloads'),
                        'published = 1',
                        'parent_id = ' . $db->quote($id),
                        sprintf('access IN (%s)', join(',', $groups))
                    )
                )
                ->order('c.lft ASC');

            $db->setQuery($query);
            $categories = $db->loadObjectList();
        }

        // Category filter
        $showCategoryFilter = $params->get('show_category_filter', false);

        $container = Factory::getPimpleContainer();
        $container->helperView->buildCategoryBreadcrumbs($id);

        $this->params             = $params;
        $this->categories         = $categories;
        $this->showCategoryFilter = $showCategoryFilter;
        $this->items              = $items;
        $this->pagination         = $pagination;
        $this->isPro              = $extension->isPro();

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
