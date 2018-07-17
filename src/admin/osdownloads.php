<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2018 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

require_once 'include.php';

if (defined('OSDOWNLOADS_LOADED')) {
    $controller = JControllerLegacy::getInstance('osdownloads');
    $controller->execute(JFactory::getApplication()->input->get('task'));
    $controller->redirect();

} else {
    echo '<h3>Joomlashack Framework is not installed</h3>';
}

