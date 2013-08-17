<?php
/**
 * @version 1.0.0
 * @author Open Source Training (www.ostraining.com)
 * @copyright (C) 2011- Open Source Training
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/



function OSDownloadsBuildRoute(&$query)
{
	$segments = array();
	$view = "";

	$app		= JFactory::getApplication();
	$menu		= $app->getMenu(); 

	if (empty($query['Itemid'])) {
		$menuItem = $menu->getActive();
		$menuItemGiven = false;
	}
	else {
		$menuItem = $menu->getItem($query['Itemid']);
		$menuItemGiven = true;
	} 


	if (isset($query['view'])) {
		$view = $query['view'];
		unset($query['view']);
	} 

	if ($view == "downloads")
	{
		if (!$menuItemGiven)
			$segments[] = "category";
		$categories = array();
		/*
		buildPath($categories, $query['id']);
		for ($i = count($categories) - 1; $i >= 0; $i--)
			$segments[] = $categories[$i];
		*/
	}

	if ($view == "item")
	{
		$id = (int) $query['id'];
		foreach ($menu->getMenu() as $item)
		{
			if (@$item->query["option"] == "com_osdownloads" && @$item->query["view"] == "item")
			{
				$params = $menu->getParams($item->id);
				if ($params->get("document_id") == $query['id'])
				{
					unset($query['id']);
					return array($item->alias);
				}
			}
		}
		
		if (!$menuItemGiven)
			$segments[] = "file";
		$db = JFactory::getDBO();
		$db->setQuery("SELECT alias FROM #__osdownloads_documents WHERE  id = " . $id);
		$segments[] = $db->loadResult();
	}

	return $segments;
}



function buildPath(& $segments, $id)
{
	if (!isset($id) || $id == 0)
		return;
	$db 	= JFactory::getDBO();
	$db->setQuery("SELECT * FROM #__categories WHERE extension='com_osdownloads' AND id = " . $id);
	$cate = $db->loadObject();
	if ($cate)
		$segments[] = $cate->alias;	
	if ($cate && $cate->parent_id)
		buildPath($segments, $cate->parent_id);

}





function OSDownloadsParseRoute($segments)
{
	$vars = array();
	if ($segments[0] == "category")
		$vars['view'] = 'downloads'; 		

	if ($segments[0] == "file")
		$vars['view'] = 'item'; 		

	return $vars;
} 