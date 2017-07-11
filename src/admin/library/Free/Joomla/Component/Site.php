<?php
/**
 * @package   com_osdownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace Alledia\OSDownloads\Free\Joomla\Component;

defined('_JEXEC') or die();

use Alledia\Framework\Joomla\Extension\AbstractComponent;
use Alledia\Framework\Factory;

class Site extends AbstractComponent
{
    protected static $instance;

    public static function getInstance($namespace = null)
    {
        return parent::getInstance('OSDownloads');
    }

    public function init()
    {
        \JHtml::stylesheet('com_osdownloads/frontend.css', null, true);

        parent::init();
    }
}
