<?php
/**
 * @package   OSDownloads
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2014 Alledia.com, All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace Alledia\OSDownloads\Free\Joomla\Model;

defined('_JEXEC') or die();

use Alledia\Framework\Joomla\Model\Base as BaseModel;
use Alledia\Framework\Factory;
use Alledia\OSDownloads\Free\Joomla\Component\Site as FreeComponentSite;
use JRequest;
use JRoute;
use JDispatcher;
use JEventDispatcher;
use JPluginHelper;


class Item extends BaseModel
{
    /**
     * Get document's data from db
     *
     * @param  int $documentId
     * @return stdClass
     */
    public function getItem($documentId)
    {
        $db    = $this->getDBO();
        $query = $this->getItemQuery($documentId);

        $db->setQuery($query);

        $item = $db->loadObject();

        if (!empty($item)) {
            // Check if the file should comes from an external URL
            if (!empty($item->file_url)) {
                // Triggers the onOSDownloadsGetExternalDownloadLink event
                JPluginHelper::importPlugin('osdownloads');

                if (version_compare(JVERSION, '3.0', '<')) {
                    $dispatcher = JDispatcher::getInstance();
                } else {
                    $dispatcher = JEventDispatcher::getInstance();
                }

                $dispatcher->trigger('onOSDownloadsGetExternalDownloadLink', array(&$item));

                $downloadUrl = $item->file_url;
            } else {
                // Uploaded file - internal link
                $downloadUrl = "index.php?option=com_osdownloads&task=download&tmpl=component&id={$item->id}";
            }

            $item->download_url = JRoute::_($downloadUrl);
        }

        return $item;
    }

    /**
     * Get the document's query
     *
     * @param  int $documentId
     * @return JDatabaseQuery
     */
    public function getItemQuery($documentId = null)
    {
        $db        = $this->getDBO();
        $user      = Factory::getUser();
        $groups    = $user->getAuthorisedViewLevels();
        $component = FreeComponentSite::getInstance();

        $query  = $db->getQuery(true)
            ->select('doc.*')
            ->select('cat.access AS cat_access')
            ->select('cat.title AS cat_title')
            ->from('#__osdownloads_documents AS doc')
            ->leftJoin(
                '#__categories AS cat'
                    . ' ON (doc.cate_id = cat.id AND cat.extension = ' . $db->quote('com_osdownloads') . ')'
            )
            ->where(
                array(
                    // It must be published
                    'cat.published = 1',
                    'doc.published = 1',
                    // The user needs to have access to it
                    'doc.access IN (' . implode(',', $groups) . ')',
                    'cat.access IN (' . implode(',', $groups) . ')'
                )
            );

        if (!empty($documentId)) {
            $query->where('doc.id = ' . $db->quote((int) $documentId));
        }

        if ($component->isFree()) {
            $query->select("doc.require_email as require_user_email");
        }

        return $query;
    }

    public function incrementDownloadCount($itemId)
    {
        $db = Factory::getDbo();

        $query = $db->getQuery(true)
            ->update('#__osdownloads_documents')
            ->set('downloaded = downloaded + 1')
            ->where('id = ' . $db->quote($itemId));
        $db->setQuery($query);
        $db->execute();
    }
}
