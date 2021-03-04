<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2005-2021 Joomlashack.com. All rights reserved
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
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;

defined('_JEXEC') or die();

class Emails extends BaseController
{
    public function delete()
    {
        $app       = Factory::getApplication();
        $container = OSDFactory::getContainer();

        $ids = array_filter(
            array_unique(
                array_map('intval', $app->input->get('cid', [], 'array'))
            )
        );

        $db    = Factory::getDbo();
        $query = $db->getQuery(true)
            ->delete('#__osdownloads_emails')
            ->where(sprintf('id IN (%s)', join(',', $ids)));

        $db->setQuery($query)->execute();

        $this->setRedirect(
            $container->helperRoute->getAdminEmailListRoute(),
            Text::_("COM_OSDOWNLOADS_EMAIL_IS_DELETED")
        );
    }
}
