<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2005-2020 Joomlashack.com. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 *
 * This file is part of OSDownloads.
 *
 * OSDownloads is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * OSDownloads is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OSDownloads.  If not, see <http://www.gnu.org/licenses/>.
 */

defined('_JEXEC') or die();

class OSDownloadsController extends JControllerLegacy
{
    protected $default_view = "files";

    public function __construct($default = array())
    {
        parent::__construct($default);

        $this->registerTask('cancel', 'display');
        $this->registerTask('file', 'display');
    }

    /**
     * @param bool  $cachable
     * @param array $urlparams
     *
     * @return JControllerLegacy
     * @throws Exception
     */
    public function display($cachable = false, $urlparams = array())
    {
        $app = JFactory::getApplication();

        $view = $app->input->getCmd('view', 'files');
        $app->input->set('view', $view);

        switch ($this->getTask()) {
            case "file":
                $app->input->set('view', 'file');
                $view = 'file';
                break;
        }

        if ($view !== 'file') {
            require_once JPATH_COMPONENT . '/helpers/osdownloads.php';

            if (class_exists('\\Alledia\\OSDownloads\\Pro\\Helper\\Helper')) {
                Alledia\OSDownloads\Pro\Helper\Helper::addSubmenu($app->input->getCmd('view', $view));
            } else {
                Alledia\OSDownloads\Free\Helper\Helper::addSubmenu($app->input->getCmd('view', $view));
            }
        }

        return parent::display();
    }
}
