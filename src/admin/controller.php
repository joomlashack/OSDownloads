<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2005-2021 Joomlashack.com. All rights reserved
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

use Alledia\OSDownloads\Factory;
use Alledia\OSDownloads\Free\Helper\Helper as FreeHelper;
use Alledia\OSDownloads\Pro\Helper\Helper as ProHelper;
use Joomla\CMS\MVC\Controller\BaseController;

defined('_JEXEC') or die();

class OSDownloadsController extends BaseController
{
    /**
     * @inheritdoc
     */
    protected $default_view = 'files';

    /**
     * @inheritDoc
     */
    public function __construct($default = [])
    {
        parent::__construct($default);

        $this->registerTask('cancel', 'display');
        $this->registerTask('file', 'display');
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function display($cachable = false, $urlparams = [])
    {
        $app = Factory::getApplication();

        $view = $app->input->getCmd('view', 'files');
        $app->input->set('view', $view);

        switch ($this->getTask()) {
            case 'file':
                $app->input->set('view', 'file');
                $view = 'file';
                break;
        }

        if ($view !== 'file') {
            $extension = Factory::getExtension('com_osdownloads', 'component');
            if ($extension->isPro()) {
                ProHelper::addSubmenu($app->input->getCmd('view', $view));
            } else {
                FreeHelper::addSubmenu($app->input->getCmd('view', $view));
            }
        }

        return parent::display();
    }
}
