<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2018 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

use Alledia\OSDownloads\Free\Joomla\Component\Site as SiteComponent;

include_once JPATH_ADMINISTRATOR . '/components/com_osdownloads/include.php';

if (defined('OSDOWNLOADS_LOADED')) {
    $component = SiteComponent::getInstance();
    $component->init();
}
