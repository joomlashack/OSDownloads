<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die();

if (!defined('OSDOWNLOADS_LOADED')) {
    require_once JPATH_ADMINISTRATOR . '/components/com_osdownloads/include.php';
}

/**
 * Routing class from com_osdownloads
 */
class OsdownloadsRouter extends JComponentRouterBase
{
    /**
     * Build the route for the com_content component
     *
     * @param   array  &$query  An array of URL arguments
     *
     * @return  array  The URL arguments to use to assemble the subsequent URL.
     */
    public function build(&$query)
    {
        $segments = array();

        $view = "";

        if (isset($query['view'])) {
            $view = $query['view'];
            unset($query['view']);

            switch ($view) {
                case 'downloads':
                    $segments[] = "category";

                    if (isset($query['id'])) {
                        $this->appendCategoriesToSegments($segments, $query['id']);

                        unset($query['id']);
                    }
                    break;

                case 'item':
                    if (isset($query['id'])) {
                        $segments[] = "file";

                        $catId = $this->getCategoryIdFromFile($query['id']);

                        $this->appendCategoriesToSegments($segments, $catId);

                        // Append the file alias
                        $segments[] = $this->getFileAlias($query['id']);

                        unset($query['id']);
                    }
                    break;
            }
        }

        if (isset($query['task'])) {
            if ($query['task'] === 'routedownload') {
                $segments[] = 'download';

                // Append the categories before the alias of the file
                $catId = $this->getCategoryIdFromFile($query['id']);

                $this->appendCategoriesToSegments($segments, $catId);

                $segments[] = $this->getFileAlias($query['id']);

                unset($query['task'], $query['tmpl'], $query['id']);
            }
        }

        return $segments;
    }

    /**
     * @param string[] $segments
     *
     * @return mixed[]
     */
    public function parse(&$segments)
    {
        $lastSegmentIndex = count($segments) - 1;
        $vars             = array();

        if ($segments[0] === 'category') {
            $vars['view'] = 'downloads';

            if (isset($segments[1])) {
                // Get category Id from category alias
                $category = $this->getCategoryFromAlias($segments[$lastSegmentIndex]);

                $vars['id'] = $category->id;
            }
        }

        if ($segments[0] === 'file') {
            $vars['view'] = 'item';
            $vars['id']   = $this->getFileIdFromAlias($segments[$lastSegmentIndex]);
        }

        if ($segments[0] === 'download') {
            $vars['task'] = 'routedownload';
            $vars['tmpl'] = 'component';
            $vars['id']   = $this->getFileIdFromAlias($segments[$lastSegmentIndex]);
        }

        return $vars;
    }

    /**
     * Build the path to a category, considering the parent categories.
     *
     * @param array $segments
     * @param int   $catId
     */
    protected function buildCategoriesPath(&$segments, $catId)
    {
        if (empty($catId)) {
            return;
        }

        $category = $this->getCategory($catId);

        if ($category) {
            $segments[] = $category->alias;
        }

        if ($category && $category->parent_id) {
            $this->buildCategoriesPath($segments, $category->parent_id);
        }
    }

    /**
     * Append the category path to the segments.
     *
     * @param array $segments
     * @param int   $catId
     */
    protected function appendCategoriesToSegments(&$segments, $catId)
    {
        // Append the categories before the alias of the file
        $categories = array();

        $this->buildCategoriesPath($categories, $catId);

        for ($i = count($categories) - 1; $i >= 0; $i--) {
            $segments[] = $categories[$i];
        }
    }

    /**
     * Returns the alias of a file based on the file id.
     *
     * @param int $id
     *
     * @return string
     */
    protected function getFileAlias($id)
    {
        $db = JFactory::getDbo();

        $query = $db->getQuery(true)
            ->select('alias')
            ->from('#__osdownloads_documents')
            ->where('id = ' . $db->quote((int)$id));

        $alias = $db->setQuery($query)->loadResult();

        if (empty($alias)) {
            throw new Exception(JText::_('COM_OSDOWNLOADS_ERROR_FILE_NOT_FOUND'));
        }

        return $alias;
    }

    /**
     * Returns the id of a file based on the file's alias.
     *
     * @param string $alias
     *
     * @return string
     */
    protected function getFileIdFromAlias($alias)
    {
        $db = JFactory::getDbo();

        $query = $db->getQuery(true)
            ->select('id')
            ->from('#__osdownloads_documents')
            ->where('alias = ' . $db->quote($alias));

        $id = $db->setQuery($query)->loadResult();

        if (empty($id)) {
            throw new Exception(JText::_('COM_OSDOWNLOADS_ERROR_FILE_NOT_FOUND'));
        }

        return $id;
    }

    /**
     * Returns the category id based on the file id.
     *
     * @param int $fileId
     *
     * @return int
     */
    protected function getCategoryIdFromFile($fileId)
    {
        $db = JFactory::getDbo();

        $query = $db->getQuery(true)
            ->select('cate_id')
            ->from('#__osdownloads_documents')
            ->where('id = ' . (int)$fileId);

        $catId = $db->setQuery($query)->loadResult();

        if (empty($catId)) {
            throw new Exception(JText::_('COM_OSDOWNLOADS_ERROR_FILE_NOT_FOUND'));
        }

        return $catId;
    }

    /**
     * Returns the category as object based on the id.
     *
     * @param int $id
     *
     * @return stdClass
     */
    protected function getCategory($id)
    {
        $db = JFactory::getDBO();

        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__categories')
            ->where(
                array(
                    'extension IN ("com_osdownloads", "system")',
                    'id = ' . (int)$id
                )
            );

        $category = $db->setQuery($query)->loadObject();

        if (!is_object($category)) {
            throw new Exception(JText::_('COM_OSDOWNLOADS_ERROR_CATEGORY_NOT_FOUND'));
        }

        return $category;
    }

    /**
     * Returns the category as object based on the alias.
     *
     * @param string $alias
     *
     * @return stdClass
     */
    protected function getCategoryFromAlias($alias)
    {
        $db = JFactory::getDBO();

        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__categories')
            ->where(
                array(
                    'extension IN ("com_osdownloads", "system")',
                    'alias = ' . $db->quote($alias)
                )
            );

        $category = $db->setQuery($query)->loadObject();

        if (!is_object($category)) {
            throw new Exception(JText::_('COM_OSDOWNLOADS_ERROR_CATEGORY_NOT_FOUND'));
        }

        return $category;
    }
}
