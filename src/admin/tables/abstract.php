<?php
/**
 * @package   OSDownloads
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2014 Alledia.com, All rights reserved
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
            if (version_compare(JVERSION, '3.0', '<')) {
                $this->dispatcher = JDispatcher::getInstance();
            } else {
                $this->dispatcher = JEventDispatcher::getInstance();
            }
        }

        return $this->dispatcher;
    }
}
