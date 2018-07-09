<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2018 Open Source Training, LLC. All rights reserved
 * @license   GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

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
