<?php
/**
 * @version 1.0.0
 * @author Open Source Training (www.ostraining.com)
 * @copyright (C) 2011- Open Source Training
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/
// No direct access
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
        require_once JPATH_COMPONENT.'/helpers/osdownloads.php';
        OSDownloadsHelper::addSubmenu(JRequest::getCmd('view', 'files'));

        $view = JRequest::getVar("view", "files");
        JRequest::setVar("view", $view);
        switch ($this->getTask()) {
            case "file":
                JRequest::setVar('view', 'file');
                break;
        }

        parent::display();
    }
}
