<?php
/**
 * @package   OSDownloads
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2014 Alledia.com, All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.view');

class OSDownloadsViewDownloads extends JViewLegacy
{

    public function display($tpl = null)
    {
        $app                    = JFactory::getApplication();
        $db                     = JFactory::getDBO();
        $params                 = clone($app->getParams('com_osdownloads'));
        $categoryIDs            = (array) $params->get("category_id");
        $includeChildCategories = (bool) $params->get('include_child_category', 0);

        $limit      = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');
        $limitstart = $app->getUserStateFromRequest('osdownloads.request.limitstart', 'limitstart', 0, 'int');

        // Paths
        $paths = array();
        $id = JRequest::getVar("id", null, 'default', 'int');
        if (!empty($id)) {
            $this->buildPath($paths, $id);

            $categoryIDs = (array) $id;
        }

        $categoryIDsStr = implode(',', $categoryIDs);

        $extraWhere = '';
        if ($includeChildCategories) {
            $extraWhere = " OR c.parent_id IN ({$categoryIDsStr}) ";
        }

        $query = "SELECT d.*,
                      c.published as cat_published,
                      c.access AS cat_access
                  FROM `#__osdownloads_documents` AS d
                  LEFT JOIN `#__categories` AS c ON (d.cate_id = c.id AND c.extension='com_osdownloads')
                  WHERE (d.cate_id IN ({$categoryIDsStr})
                      {$extraWhere} )
                      AND d.published = 1
                      AND c.published = 1
                  ORDER BY d.ordering";

        // Pagination
        $db->setQuery($query);
        $db->query();

        $total = $db->getNumRows();

        jimport('joomla.html.pagination');
        $pagination = new JPagination($total, $limitstart, $limit);

        // Items
        $db->setQuery($query, $pagination->limitstart, $pagination->limit);
        $items = $db->loadObjectList();

        $user = JFactory::getUser();
        $groups = $user->getAuthorisedViewLevels();

        if (!isset($items) || (count($items) && !in_array($items[0]->cat_access, $groups))) {
            JError::raiseWarning(404, JText::_("COM_OSDOWNLOADS_THIS_CATEGORY_ISNT_AVAILABLE"));

            return;
        }

        // Categories
        $groupsStr = implode(',', $groups);
        $query = "SELECT *,
                  (
                    SELECT COUNT(*)
                    FROM `#__osdownloads_documents` AS d
                    WHERE d.cate_id = c.id
                        AND d.access IN ({$groupsStr})
                  ) AS total_doc
                  FROM `#__categories` AS c
                  WHERE extension='com_osdownloads'
                    AND published = 1
                    AND (id IN ({$categoryIDsStr})
                        {$extraWhere}
                    )";
        $db->setQuery($query);
        $categories = $db->loadObjectList();

        // Category filter
        $showCategoryFilter = $params->get('show_category_filter', false);

        $this->assignRef("categories", $categories);
        $this->assignRef("showCategoryFilter", $showCategoryFilter);
        $this->assignRef("items", $items);
        $this->assignRef("paths", $paths);
        $this->assignRef("pagination", $pagination);
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
                           AND id = " . $db->q((int) $categoryID));
        $category = $db->loadObject();

        if ($category) {
            $paths[] = $category;
        }

        if ($category && $category->parent_id) {
            $this->buildPath($paths, $category->parent_id);
        }
    }
}
