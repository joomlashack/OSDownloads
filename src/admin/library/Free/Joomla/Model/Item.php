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

namespace Alledia\OSDownloads\Free\Joomla\Model;

defined('_JEXEC') or die();

use Alledia\Framework\Joomla\Model\Base as BaseModel;
use Alledia\OSDownloads\Factory;
use Alledia\OSDownloads\Free\Helper\Helper as FreeHelper;
use Alledia\OSDownloads\Free\Joomla\Component\Site as FreeSite;
use JDatabaseQuery;
use Joomla\Database\QueryInterface;

class Item extends BaseModel
{
    /**
     * Get document's data from db
     *
     * @param int $documentId
     *
     * @return object
     * @throws \Exception
     */
    public function getItem($documentId)
    {
        $db    = $this->getDbo();
        $query = $this->getItemQuery($documentId);

        $db->setQuery($query);

        if ($item = $db->loadObject()) {
            FreeHelper::prepareItem($item);
        }

        return $item;
    }

    /**
     * Get the document's query
     *
     * @param ?int $documentId
     *
     * @return JDatabaseQuery|QueryInterface
     * @throws \Exception
     */
    public function getItemQuery(?int $documentId = null)
    {
        $app       = Factory::getApplication();
        $db        = $this->getDbo();
        $user      = Factory::getUser();
        $groups    = $user->getAuthorisedViewLevels();
        $component = FreeSite::getInstance();

        $filterOrder    = $app->getUserStateFromRequest(
            'com_osdownloads.files.filter_order',
            'filter_order',
            'doc.ordering',
            ''
        );
        $filterOrderDir = $app->getUserStateFromRequest(
            'com_osdownloads.files.filter_order_Dir',
            'filter_order_Dir',
            'asc',
            'word'
        );

        $query = $db->getQuery(true)
            ->select('doc.*')
            ->select('cat.access AS cat_access')
            ->select('cat.title AS cat_title')
            ->from('#__osdownloads_documents AS doc')
            ->leftJoin(
                '#__categories AS cat'
                . ' ON (doc.cate_id = cat.id AND cat.extension = ' . $db->quote('com_osdownloads') . ')'
            )
            ->where([
                'cat.published = 1',
                'doc.published = 1',
                'doc.access IN (' . implode(',', $groups) . ')',
                'cat.access IN (' . implode(',', $groups) . ')'
            ])
            ->order($db->quoteName($filterOrder) . ' ' . $filterOrderDir);

        if (!empty($documentId)) {
            $query->where('doc.id = ' . $db->quote((int)$documentId));
        }

        if ($component->isFree()) {
            $query->select('doc.require_email as require_user_email');
        }

        return $query;
    }

    /**
     * @param int $id
     *
     * @return void
     */
    public function incrementDownloadCount(int $id)
    {
        $db = Factory::getDbo();

        $query = $db->getQuery(true)
            ->update('#__osdownloads_documents')
            ->set('downloaded = downloaded + 1')
            ->where('id = ' . $id);
        $db->setQuery($query);
        $db->execute();
    }
}
