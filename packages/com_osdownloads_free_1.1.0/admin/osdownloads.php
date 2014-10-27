<?php
/**
 * @package   OSDownloads
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2014 Alledia.com, All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;
jimport('joomla.application.component.controller');
$controller = JControllerLegacy::getInstance('osdownloads');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
