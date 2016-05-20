<?php
/**
 * @package   OSDownloads
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2016 Open Source Training, LLC. All rights reserved
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

abstract class OSDownloadsTableAbstract extends JTable
{
    /**
     * Event dispatcher
     *
     * @var JEventDispatcher
     */
    protected $dispatcher;

    /**
     * Returns the dispatcher instance
     *
     * @return JEventDispatcher
     */
    protected function getDispatcher()
    {
        JPluginHelper::importPlugin('osdownloads');

        if (!isset($this->dispatcher)) {
            $this->dispatcher = JEventDispatcher::getInstance();
        }

        return $this->dispatcher;
    }
}
