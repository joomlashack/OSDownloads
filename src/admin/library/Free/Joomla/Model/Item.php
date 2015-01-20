<?php
/**
 * @package   OSDownloads
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2014 Alledia.com, All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace Alledia\OSDownloads\Free\Joomla\Model;

defined('_JEXEC') or die();

use Alledia\Framework\Factory;
use JRequest;

jimport('legacy.model.legacy');

class Item extends \JModelLegacy
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

        return $db->loadObject();
    }

    /**
     * Get the document's query
     *
     * @param  int $documentId
     * @return JDatabaseQuery
     */
    public function getItemQuery($documentId = null)
    {
        $db     = $this->getDBO();
        $user   = Factory::getUser();
        $groups = $user->getAuthorisedViewLevels();

        $query  = $db->getQuery(true)
            ->select('doc.*')
            ->select('cat.access', 'cat_access')
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

        return $query;
    }
}
