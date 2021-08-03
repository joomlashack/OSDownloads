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

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Layout\LayoutHelper;

$displayData = [
	'textPrefix' => 'COM_OSDOWNLOADS',
	'formURL'    => 'index.php?option=com_osdownloads&view=files',
	'helpURL'    => 'https://www.joomlashack.com/docs/osdownloads/start/',
	'icon'       => 'icon-copy article',
];

$user = Factory::getApplication()->getIdentity();

if ($user->authorise('core.create', 'com_osdownloads') || count($user->getAuthorisedCategories('com_osdownloads', 'core.create')) > 0)
{
	$displayData['createURL'] = 'index.php?option=com_osdownloads&task=file.add';
}

echo LayoutHelper::render('joomla.content.emptystate', $displayData);
