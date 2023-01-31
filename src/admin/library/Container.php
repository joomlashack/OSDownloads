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

namespace Alledia\OSDownloads;

use Alledia\OSDownloads\Free\Helper\Route;
use Alledia\OSDownloads\Free\Helper\SEF;
use Alledia\OSDownloads\Free\Helper\View;
use Alledia\OSDownloads\MailingLists\Manager;
use Joomla\CMS\Application\CMSApplication;
use Joomla\Database\DatabaseDriver;

defined('_JEXEC') or die();

/**
 * Class Container
 *
 * @package OSDownloads
 *
 * @property-read CMSApplication $app
 * @property-read DatabaseDriver $db
 * @property-read Manager        $mailingLists
 * @property-read Route          $helperRoute
 * @property-read SEF            $helperSEF
 * @property-read View           $helperView
 *
 * @method CMSApplication app()
 * @method DatabaseDriver db()
 * @method Manager        mailingLists()
 * @method Route          getHelperRoute()
 * @method SEF            helperSEF()
 * @method View           helperView()
 */
class Container extends \Pimple\Container
{
    /**
     * @var Container
     */
    protected static $instance = null;

    /**
     * @inheritDoc
     */
    public function __construct(array $values = [])
    {
        $values = array_merge(
            [
                'helperRoute'    => null,
                'helperSanitize' => null,
            ],
            $values
        );

        parent::__construct($values);
    }

    /**
     * Magic method to get attributes.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function __get(string $name)
    {
        if (isset($this[$name])) {
            return $this[$name];
        }

        return null;
    }

    /**
     * Get the current instance
     *
     * @return Container
     */
    public static function getInstance(): Container
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }

        return static::$instance;
    }
}
