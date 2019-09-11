<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2005-2019 Joomlashack.com. All rights reserved
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

namespace Alledia\OSDownloads\Free\Joomla\Controller\Admin;

use Alledia\Framework\Factory;
use Alledia\OSDownloads\Free\Factory as OSDFactory;
use JControllerLegacy;
use JText;

defined('_JEXEC') or die();

class Emails extends JControllerLegacy
{
    public function delete()
    {
        $app       = Factory::getApplication();
        $container = OSDFactory::getContainer();

        $id_arr = $app->input->getVar('cid');
        $str_id = implode(',', $id_arr);
        $db     = Factory::getDBO();

        $query  = "DELETE FROM `#__osdownloads_emails` WHERE id IN (" . $str_id . ")";
        $db->setQuery($query)->execute();

        $this->setRedirect(
            $container->helperRoute->getAdminEmailListRoute(),
            JText::_("COM_OSDOWNLOADS_EMAIL_IS_DELETED")
        );
    }
}
