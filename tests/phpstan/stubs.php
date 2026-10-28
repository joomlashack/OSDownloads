<?php

/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2026 Joomlashack.com. All rights reserved
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

// phpcs:ignoreFile

/*
 * Declarations for classes PHPStan can't otherwise find: the neutral names the
 * Free/Pro shims create with class_alias(), shown here as the Free class, and
 * Joomla 3 classes that newer Joomla versions dropped. PHPStan scans this file
 * but never runs it.
 */

namespace {
    class JEventDispatcher
    {
    }

    class JFormFieldList extends Joomla\CMS\Form\Field\ListField
    {
    }
}

namespace Alledia\OSDownloads {
    class ContentPlugin extends Free\Joomla\Plugin\Content
    {
    }
}

namespace Alledia\OSDownloads\Helper {
    class Helper extends \Alledia\OSDownloads\Free\Helper\Helper
    {
    }
}

namespace Alledia\OSDownloads\Joomla\Model {
    class Emails extends \Alledia\OSDownloads\Free\Joomla\Model\Emails
    {
    }
}
