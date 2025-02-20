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

use Alledia\OSDownloads\Free\Joomla\Model\Emails as EmailsFree;
use Alledia\OSDownloads\Joomla\Model\Emails;
use Alledia\OSDownloads\Pro\Joomla\Model\Emails as EmailsPro;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die();

if (class_exists(EmailsPro::class) == false) {
    class_alias(EmailsFree::class, Emails::class);
} else {
    class_alias(EmailsPro::class, Emails::class);
}

// phpcs:enable PSR1.Files.SideEffects
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace

class OsdownloadsModelEmails extends Emails
{
}
