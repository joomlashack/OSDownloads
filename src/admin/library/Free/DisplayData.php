<?php
/**
 * @package   OSDownloads-Pro
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2005-2022 Joomlashack.com. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
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
 * along with OSDownloads-Pro.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Alledia\OSDownloads\Free;

use Alledia\OSDownloads\Factory;
use Joomla\CMS\Form\Form;
use Joomla\Registry\Registry;

defined('_JEXEC') or die();

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
    public $params = null;

    /**
     * @var int
     */
    public $itemId = null;

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
    public function setForm(Form $form)
    {
        $this->form = $form;
    }

    /**
     * @param string $attribute
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
