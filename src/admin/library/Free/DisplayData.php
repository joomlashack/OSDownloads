<?php
/**
 * @package   OSDownloads-Pro
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2005-2025 Joomlashack.com. All rights reserved
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 *
 * This file is part of OSDownloads-Pro.
 *
 * OSDownloads-Pro is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * OSDownloads-Pro is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OSDownloads-Pro.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace Alledia\OSDownloads\Free;

use Alledia\OSDownloads\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die();

// phpcs:enable PSR1.Files.SideEffects

class DisplayData
{
    /**
     * @var Form
     */
    protected $form = null;

    /**
     * @var object
     */
    public $item = null;

    /**
     * @var bool
     */
    public $isPro = null;

    /**
     * @var Registry
     */
    protected $params = null;

    /**
     * @var int
     */
    public $itemId = null;

    /**
     * @var string
     */
    public $buttonClasses = null;

    /**
     * @param ?Registry $params
     *
     * @return void
     */
    public function __construct(?Registry $params = null)
    {
        $extension   = Factory::getExtension('OSDownloads');
        $this->isPro = $extension->isPro();

        if (empty($params)) {
            $params = new Registry();
        }
        $this->params = $params;
    }

    /**
     * @param string $name
     *
     * @return mixed
     * @throws \Exception
     */
    public function __get(string $name)
    {
        if (property_exists($this, $name)) {
            return $this->{$name};
        }

        trigger_error(
            Text::sprintf(
                'COM_OSDOWNLOADS_ERROR_INVALID_ARGUMENT',
                get_class($this),
                $name
            )
        );

        return null;
    }

    /**
     * @return ?Form
     */
    public function getForm(): ?Form
    {
        return $this->form instanceof Form ? $this->form : null;
    }

    /**
     * @param Form $form
     *
     * @return void
     */
    public function setForm(Form $form): void
    {
        $this->form = $form;
    }

    /**
     * @param string $attribute
     * @param ?mixed $default
     *
     * @return mixed
     */
    public function get(string $attribute, $default = null)
    {
        if (isset($this->{$attribute})) {
            return $this->{$attribute};
        }

        return $default;
    }
}
