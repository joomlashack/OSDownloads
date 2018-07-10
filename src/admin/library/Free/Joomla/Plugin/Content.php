<?php
/**
 * @package    OSDownloads
 * @contact    www.joomlashack.com, help@joomlashack.com
 * @copyright  2016-2018 Open Source Training, LLC. All rights reserved
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
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

namespace Alledia\OSDownloads\Free\Joomla\Plugin;

use Alledia\OSDownloads\Free\Factory;
use Exception;

defined('_JEXEC') or die();

class Content extends \JPlugin
{
    /**
     * @var \JApplicationCms
     */
    protected $app = null;

    /**
     * @var bool
     */
    protected $enabled = null;

    /**
     * @param \JForm $form
     * @param object $data
     *
     * @return bool
     * @throws Exception
     */
    public function onContentPrepareForm($form, $data)
    {
        if ($this->isEnabled()) {
            Factory::getContainer()->mailingLists->loadForms($form);
        }

        return true;
    }

    /**
     * @return bool
     */
    protected function isEnabled()
    {
        if ($this->enabled === null) {
            if (!defined('OSDOWNLOADS_LOADED')) {
                $includePath = JPATH_ADMINISTRATOR . '/components/com_osdownloads/include.php';
                if (is_file($includePath)) {
                    require_once $includePath;
                }
            }

            $this->enabled = defined('OSDOWNLOADS_LOADED');
        }

        return $this->enabled;
    }
}
