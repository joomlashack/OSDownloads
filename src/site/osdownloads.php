<?php
/**
 * @package   OSDownloads
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2016 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

use Alledia\OSDownloads\Free\Joomla\Component\Site as SiteComponent;

require_once JPATH_SITE . '/administrator/components/com_osdownloads/include.php';

$component = SiteComponent::getInstance();
$component->init();
