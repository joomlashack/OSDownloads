<?php
/**
 * @package   com_osdownloads
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2014 Alledia.com, All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace Alledia\OSDownloads\Free\Joomla\Module;

defined('_JEXEC') or die();

use Alledia\Framework\Joomla\Extension\AbstractModule;
use Alledia\Framework\Factory;
use Alledia\OSDownloads\Free\Joomla\Component\Site as FreeComponentSite;
use JModuleHelper;

class File extends AbstractModule
{
    protected static $instance;

    public static function getInstance($namespace = null, $module = null)
    {
        return parent::getInstance('OSDownloadsFiles', $module);
    }

    public function init()
    {
        // Load the OSDownloads extension
        Factory::getLanguage()->load('com_osdownloads');
        $osdownloads = FreeComponentSite::getInstance();
        $osdownloads->loadLibrary();

        $this->list           = $this->getList();
        $this->loadJQuery     = (bool) $osdownloads->params->get('load_jquery', false);
        $this->popupAnimation = $osdownloads->params->get('popup_animation', 'fade');
        $this->isPro          = $osdownloads->isPro();

        parent::init();
    }

    public function getList()
    {
        $db = Factory::getDBO();

        $osdownloads = FreeComponentSite::getInstance();
        $model       = $osdownloads->getModel('Item');

        $query = $model->getItemQuery();
        $query->where("cate_id = " . $db->quote($this->params->get('category', 0)));
        $db->setQuery($query);

        $rows = $db->loadObjectList();

        return $rows;
    }
}
