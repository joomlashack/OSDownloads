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

namespace Alledia\OSDownloads\Free\Joomla\View\Admin;

use Joomla\CMS\Version;
use JViewLegacy;

class Base extends JViewLegacy
{
    /**
     * Load different layout depending on Joomla 2.5 vs 3.x
     * For default layout, the j2 version is not required.
     *
     * @TODO: Test for existence of j2 non-default layout
     *
     * @return string
     */
    public function getLayout()
    {
        $layout = parent::getLayout();
        if (version_compare(JVERSION, '4.0', 'lt')) {
            $layout .= '.j' . Version::MAJOR_VERSION;
        }
        return $layout;
    }
}