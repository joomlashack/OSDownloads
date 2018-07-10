<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2018 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace Alledia\OSDownloads\Free\Joomla\View\Site;

use Alledia\OSDownloads\Free\Joomla\View\Legacy;
use Exception;

defined('_JEXEC') or die();


class Base extends Legacy
{
    /**
     * @param string $tpl
     *
     * @return void
     * @throws Exception
     */
    public function display($tpl = null)
    {
        $this->prepareDocument();

        parent::display($tpl);
    }

    /**
     * Method to prepares the document
     *
     * @return  void
     * @throws Exception
     */
    protected function prepareDocument()
    {
        $app    = \JFactory::getApplication();
        $menus  = $app->getMenu();
        $doc    = \JFactory::getDocument();
        $params = $app->getParams();
        $title  = null;

        // Because the application sets a default page title, we need to get it from the menu item itself
        $menu = $menus->getActive();

        if ($menu) {
            $params->def('page_heading', $params->get('page_title', $menu->title));
        } else {
            $params->def('page_heading', '');
        }

        $title = $params->get('page_title', '');

        if (empty($title)) {
            $title = $app->get('sitename');
        } elseif ($app->get('sitename_pagetitles', 0) == 1) {
            $title = \JText::sprintf('JPAGETITLE', $app->get('sitename'), $title);
        } elseif ($app->get('sitename_pagetitles', 0) == 2) {
            $title = \JText::sprintf('JPAGETITLE', $title, $app->get('sitename'));
        }

        $doc->setTitle($title);

        if ($params->get('menu-meta_description')) {
            $doc->setDescription($params->get('menu-meta_description'));
        }

        if ($params->get('menu-meta_keywords')) {
            $doc->setMetadata('keywords', $params->get('menu-meta_keywords'));
        }

        if ($params->get('robots')) {
            $doc->setMetadata('robots', $params->get('robots'));
        }
    }
}
