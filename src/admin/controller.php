<?php
/**
 * @package   OSDownloads
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2014 Alledia.com, All rights reserved
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

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
        $view = JRequest::getCmd("view", "files");
        JRequest::setVar("view", $view);
        switch ($this->getTask()) {
            case "file":
                JRequest::setVar('view', 'file');
                break;
        }

        if ($view !== 'file') {
            require_once JPATH_COMPONENT.'/helpers/osdownloads.php';
            OSDownloadsHelper::addSubmenu(JRequest::getCmd('view', $view));
        }

        parent::display();
    }
}
