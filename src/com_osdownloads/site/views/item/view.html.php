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

class OSDownloadsViewItem extends JViewLegacy
{

    public function display($tpl = null)
    {
        JTable::addIncludePath(JPATH_COMPONENT.'/tables');

        $mainframe = JFactory::getApplication();
        $params    = clone($mainframe->getParams('com_osdownloads'));
        $id        = $params->get("document_id");

        $db = JFactory::getDBO();

        if (JRequest::getVar("id")) {
            $id = JRequest::getVar("id");
        }

        $query	= "SELECT documents.*, cate.access AS cat_access
                    FROM `#__osdownloads_documents` documents
                    LEFT JOIN `#__categories` cate ON (documents.cate_id = cate.id AND cate.extension='com_osdownloads')
                    WHERE cate.published = 1 AND documents.id = {$id}";

        $db->setQuery($query);
        $item   = $db->loadObject();
        $user   = JFactory::getUser();
        $groups = $user->getAuthorisedViewLevels();

        if (!$item) {
            JError::raiseWarning(404, JText::_("COM_OSDOWNLOADS_THIS_DOWNLOAD_ISN't available"));
            return;
        }

        $categoryAuthorized = in_array($item->cat_access, $groups);
        $itemAuthorized = in_array($item->access, $groups);

        if ((! $categoryAuthorized && ! $itemAuthorized) || ! $itemAuthorized) {
            JError::raiseWarning(403, JText::_("COM_OSDOWNLOADS_YOU_DON't have permission to download this"));
            return;
        }

        $this->buildPath($paths, $item->cate_id);
        $this->assignRef("item", $item);
        $this->assignRef("paths", $paths);
        $this->assignRef("params", $params);

        // Check if the file should come from an external URL
        if (!empty($item->file_url)) {

            // Triggers the onOSDownloadsGetExternalDownloadLink event
            JPluginHelper::importPlugin('osdownloads');
            $dispatcher = JEventDispatcher::getInstance();
            $dispatcher->trigger('onOSDownloadsGetExternalDownloadLink', array(&$item));

            $downloadUrl = $item->file_url;
        } else {
            $downloadUrl = "index.php?option=com_osdownloads&task=download&tmpl=component&id={$item->id}";
        }
        $downloadUrl = JRoute::_($downloadUrl);
        $this->assignRef('download_url', $downloadUrl);

        $this->_buildBreadcrumbs($paths, $item);

        parent::display($tpl);
    }

    public function buildPath(& $paths, $id)
    {
        $db = JFactory::getDBO();
        $db->setQuery("SELECT * FROM `#__categories` WHERE extension='com_osdownloads' AND id = " . $id);
        $cate = $db->loadObject();
        if ($cate) {
            $paths[] = $cate;
        }

        if ($cate && $cate->parent_id) {
            $this->buildPath($paths, $cate->parent_id);
        }
    }
    
    protected function _buildBreadcrumbs($paths, $item){
        $app    = JFactory::getApplication();
        $pathway = $app->getPathway();

        $countPaths =  count($paths) -1;
        for ($i = $countPaths; $i >= 0; $i--){
            $pathway->addItem($paths[$i]->title, JRoute::_("index.php?option=com_osdownloads&view=downloads&id={$paths[$i]->id}"."&Itemid=".JRequest::getVar("Itemid")));
        }

        $pathway->addItem($item->name, '');
    }
}
