<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2017 Open Source Training, LLC. All rights reserved
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

use Alledia\OSDownloads\Free\Helper\Helper;


class OSDownloadsController extends JControllerLegacy
{
    protected $default_view = "files";

    public function __construct($default = array())
    {
        parent::__construct($default);

        $this->registerTask('cancel', 'display');
        $this->registerTask('file', 'display');
    }

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
            require_once JPATH_COMPONENT.'/helpers/osdownloads.php';
            Helper::addSubmenu($app->input->getCmd('view', $view));
        }

        parent::display();
    }
}
