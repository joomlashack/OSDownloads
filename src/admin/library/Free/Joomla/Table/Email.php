<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2005-2023 Joomlashack.com. All rights reserved
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

namespace Alledia\OSDownloads\Free\Joomla\Table;

use Alledia\Framework\Joomla\AbstractTable;
use Alledia\OSDownloads\Factory;
use Alledia\OSDownloads\MailingLists\AbstractClient;
use Joomla\Database\DatabaseDriver;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die();

// phpcs:enable PSR1.Files.SideEffects
// phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore

class Email extends AbstractTable
{
    /**
     * @var AbstractClient[]
     */
    protected $_mailinglists = null;

    /**
     * @inheritDoc
     * @param DatabaseDriver $db
     */
    public function __construct($db)
    {
        parent::__construct('#__osdownloads_emails', 'id', $db);

        $this->_mailinglists = Factory::getPimpleContainer()->mailingLists->registerObservers($this);
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function store($updateNulls = false)
    {
        $app = Factory::getApplication();

        $pluginResults = $app->triggerEvent('onOSDownloadsBeforeSaveEmail', [$this]);

        $result = false;
        if (in_array(false, $pluginResults, true) == false) {
            $result = parent::store($updateNulls);
        }

        $app->triggerEvent('onOSDownloadsAfterSaveEmail', [$result, $this]);

        return $result;
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function delete($pk = null)
    {
        $app = Factory::getApplication();

        $pluginResults = $app->triggerEvent('onOSDownloadsBeforeDeleteEmail', [$this, $pk]);

        $result = false;
        if (in_array(false, $pluginResults, true) == false) {
            $result = parent::delete($pk);

            $app->triggerEvent('onOSDownloadsAfterDeleteEmail', [$result, $this->get('id'), $pk]);
        }

        return $result;
    }
}
