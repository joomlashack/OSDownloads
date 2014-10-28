<?php
/**
 * @package   OSDownloads
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2014 Alledia.com, All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */
defined('_JEXEC') or die();

abstract class ModOSDownloadsFilesHelper
{
    public static function getList($params)
    {
        $db = JFactory::getDBO();

        $query = $db->getQuery(true);
        $query->select("*");
        $query->from("#__osdownloads_documents");
        $query->where("published = 1");
        $query->where("cate_id = " . $db->quote($params->get('category', 0)));
        $db->setQuery($query);

        $rows = $db->loadObjectList();

        return $rows;
    }
}
