<?php
/**
 * @package   com_osdownloads
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2014 Alledia.com, All rights reserved
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
        $doc = Factory::getDocument();
        $doc->addStyleSheet(OSDOWNLOADS_MEDIA_URI . "/css/osdownloads.css");

        parent::init();
    }
}
