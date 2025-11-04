<?php

/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2005-2025 Joomlashack.com. All rights reserved
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
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
 * along with OSDownloads.  If not, see <https://www.gnu.org/licenses/>.
 */

use Alledia\OSDownloads\Factory;
use Alledia\OSDownloads\Free\Helper\Helper;
use Alledia\OSDownloads\Free\Joomla\View\Site\Base as SiteViewBase;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Pagination\Pagination;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die();
// phpcs:enable PSR1.Files.SideEffects
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace

class OSDownloadsViewDownloads extends SiteViewBase
{
    /**
     * @var bool
     */
    public $isPro = null;

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
     * @var Pagination
     */
    protected $pagination = null;

    /**
     * @var string
     */
    public $buttonClasses = null;

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function __get(string $name)
    {
        if (property_exists($this, $name)) {
            return $this->{$name};
        }

        trigger_error(
            Text::sprintf(
                'COM_OSDOWNLOADS_ERROR_INVALID_ARGUMENT',
                get_class($this),
                $name
            )
        );

        return null;
    }

    /**
     * @inheritDoc
     */
    protected function setup()
    {
        parent::setup();

        $model = BaseDatabaseModel::getInstance('Item', 'OsdownloadsModel');
        $this->setModel($model, true);
    }

    /**
     * @inheritDoc
     */
    public function display($tpl = null)
    {
        $app = $this->app;
        $db  = Factory::getDbo();

        $params              = $this->params;
        $includeChildFiles   = (bool)$params->get('include_child_files', 0);
        $showChildCategories = (bool)$params->get('show_child_categories', 1);

        $menu = $app->getMenu()->getActive();
        if ($menu) {
            $params->def('page_heading', $params->get('page_title', $menu->title));
        }

        // Load the extension
        $extension = Alledia\Framework\Factory::getExtension('OSDownloads', 'component');
        $extension->loadLibrary();

        // Default is 1 to start from root category
        $id = $app->input->getInt('id') ?: 1;

        $app->setUserState('com_osdownloads.files.filter_order', $params->get('ordering', 'doc.ordering'));
        $app->setUserState('com_osdownloads.files.filter_order_Dir', $params->get('ordering_dir', 'asc'));

        $query = $this->model->getItemQuery();

        $query->select('cat.access as cat_access');

        $where = [
            sprintf('cate_id = %s', $db->quote($id))
        ];

        if ($includeChildFiles) {
            $where[] = sprintf('cat.parent_id = %s', $db->quote($id));
        }


        $query->where(sprintf('(%s)', join(' OR ', $where)));

        /*----------  Pagination  ----------*/

        $db->setQuery($query)->execute();

        $total = $db->getNumRows();

        $defaultLimit = $params->get('list_limit') ?: $app->get('list_limit');
        $limit        = $app->getUserStateFromRequest(
            'osdownloads.request.list.limit',
            'limit',
            $defaultLimit,
            'int'
        );
        $limitstart   = $app->getUserStateFromRequest(
            'osdownloads.request.limitstart',
            'limitstart',
            0,
            'int'
        );

        $pagination = new Pagination($total, $limitstart, $limit);

        /*----------  Files  ----------*/
        $db->setQuery($query, $pagination->limitstart, $pagination->limit);

        $items = $db->loadObjectList();
        foreach ($items as $item) {
            $item->agreementLink = $item->require_agree ? Helper::getArticleLink($item->agreement_article_id) : '';

            $item->brief         = HTMLHelper::_('content.prepare', $item->brief);
            $item->description_1 = HTMLHelper::_('content.prepare', $item->description_1);
            $item->description_2 = HTMLHelper::_('content.prepare', $item->description_2);
            $item->description_3 = HTMLHelper::_('content.prepare', $item->description_3);
        }

        $user   = Factory::getUser();
        $groups = $user->getAuthorisedViewLevels();

        if (!isset($items) || (count($items) && !in_array($items[0]->cat_access, $groups))) {
            throw new Exception(Text::_('COM_OSDOWNLOADS_THIS_CATEGORY_ISNT_AVAILABLE'), 404);
        }

        /*----------  Child Categories  ----------*/
        $categories = [];

        if ($showChildCategories) {
            $query = $db->getQuery(true)
                ->select('*')
                ->from('#__categories AS c')
                ->where([
                    'extension = ' . $db->quote('com_osdownloads'),
                    'published = 1',
                    'parent_id = ' . $db->quote($id),
                    sprintf('access IN (%s)', join(',', $groups))
                ])
                ->order('c.lft ASC');

            $db->setQuery($query);
            $categories = $db->loadObjectList();
        }

        // Category filter
        $showCategoryFilter = $params->get('show_category_filter', false);

        Factory::getPimpleContainer()->helperView->buildCategoryBreadcrumbs($id);

        $this->categories         = $categories;
        $this->showCategoryFilter = $showCategoryFilter;
        $this->items              = $items;
        $this->pagination         = $pagination;
        $this->isPro              = $extension->isPro();

        parent::display($tpl);
    }
}
