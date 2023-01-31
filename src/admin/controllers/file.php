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

use Alledia\Framework\Joomla\Extension\Licensed;
use Alledia\OSDownloads\Factory;
use Joomla\CMS\MVC\Controller\FormController;

defined('_JEXEC') or die();

class OSDownloadsControllerFile extends FormController
{
    /**
     * @var Licensed
     */
    protected $extension = null;

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function __construct($default = [])
    {
        parent::__construct($default);

        // Load the extension
        $this->extension = Factory::getExtension('OSDownloads', 'component');
        $this->extension->loadLibrary();
    }
}
