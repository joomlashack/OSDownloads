<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2018 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace Alledia\OSDownloads\Free\Joomla\Table;

defined('_JEXEC') or die();

use Alledia\Framework\Joomla\Table\Base as BaseTable;
use Alledia\OSDownloads\Free\Factory;
use JEventDispatcher;

class Email extends BaseTable
{
    public function __construct(&$db)
    {
        parent::__construct('#__osdownloads_emails', 'id', $db);

        Factory::getContainer()->mailingLists->loadObservers($this);
    }

    public function store($updateNulls = false)
    {
        // Trigger events to osdownloads plugins
        $dispatcher = JEventDispatcher::getInstance();
        $pluginResults = $dispatcher->trigger('onOSDownloadsBeforeSaveEmail', array(&$this));

        $result = false;
        if ($pluginResults !== false) {
            $result = parent::store($updateNulls);

            $dispatcher->trigger('onOSDownloadsAfterSaveEmail', array($result, &$this));
        }

        return $result;
    }

    public function delete($pk = null)
    {
        // Trigger events to osdownloads plugins
        $dispatcher = JEventDispatcher::getInstance();
        $pluginResults = $dispatcher->trigger('onOSDownloadsBeforeDeleteEmail', array(&$this, $pk));

        $result = false;
        if ($pluginResults !== false) {
            $result = parent::delete($pk);

            $dispatcher->trigger('onOSDownloadsAfterDeleteEmail', array($result, $this->id, $pk));
        }

        return $result;
    }
}
