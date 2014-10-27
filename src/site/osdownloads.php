<?php
/**
 * @package   OSDownloads
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2014 Alledia.com, All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

require_once JPATH_COMPONENT.'/controller.php';

$doc = JFactory::getDocument();
$doc->addStyleSheet(JURI::Root(). "components/com_osdownloads/assets/osdownloads.css");

$controller	= new OSDownloadsController();
$controller->execute(JRequest::getCmd('task', 'display'));
$controller->redirect();
