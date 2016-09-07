<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

jimport('legacy.controller.legacy');

require_once 'include.php';

$controller = JControllerLegacy::getInstance('osdownloads');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
