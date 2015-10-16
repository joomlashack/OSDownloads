<?php
/**
 * @package   com_osdownloads
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2015 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace Alledia\OSDownloads\Free\Joomla\Module;

defined('_JEXEC') or die();

use Alledia\Framework\Joomla\Extension\AbstractFlexibleModule;
use Alledia\Framework\Factory;
use Alledia\OSDownloads\Free\Joomla\Component\Site as FreeComponentSite;
use JModuleHelper;
use JRoute;

class File extends AbstractFlexibleModule
{
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
        $db  = Factory::getDBO();
        $app = Factory::getApplication();

        $app->setUserState("com_osdownloads.files.filter_order", $this->params->get('ordering', 'ordering'));
        $app->setUserState("com_osdownloads.files.filter_order_Dir", $this->params->get('ordering_dir', 'asc'));

        $osdownloads = FreeComponentSite::getInstance();
        $model       = $osdownloads->getModel('Item');
        $query = $model->getItemQuery();
        $query->where("cate_id = " . $db->quote($this->params->get('category', 0)));
        $db->setQuery($query);

        $rows = $db->loadObjectList();

        if (!empty($rows)) {
            foreach ($rows as &$row) {
                $row->agreementLink = JRoute::_('index.php?option=com_content&view=article&id=' . (int)  $row->agreement_article_id);
            }
        }

        return $rows;
    }
}
