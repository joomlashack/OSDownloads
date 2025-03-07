<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2005-2025 Joomlashack.com. All rights reserved
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
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
 * along with OSDownloads.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace Alledia\OSDownloads\Free\Joomla\Plugin;

use Alledia\OSDownloads\Factory;
use Exception;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Plugin\CMSPlugin;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die();
// phpcs:enable PSR1.Files.SideEffects

class Content extends CMSPlugin
{
    /**
     * @var CMSApplication
     */
    protected $app = null;

    /**
     * @var bool
     */
    protected $enabled = null;

    /**
     * @param Form   $form
     * @param object $data
     *
     * @return bool
     * @throws Exception
     */
    public function onContentPrepareForm($form, $data): bool
    {
        if ($this->isEnabled()) {
            Factory::getPimpleContainer()->mailingLists->loadForms($form);
        }

        return true;
    }

    /**
     * @return bool
     */
    protected function isEnabled(): bool
    {
        if ($this->enabled === null) {
            $include       = JPATH_ADMINISTRATOR . '/components/com_osdownloads/include.php';
            $this->enabled = is_file($include) && include $include;
        }

        return $this->enabled;
    }
}
