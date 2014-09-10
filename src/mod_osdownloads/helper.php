<?php
/**
 * @version 1.0.0
 * @author Open Source Training (www.ostraining.com)
 * @copyright (C) 2014 Open Source Training
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/
defined('_JEXEC') or die();

abstract class ModOSDownloadsHelper
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
