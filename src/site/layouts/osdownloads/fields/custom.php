<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2022-2025 Joomlashack.com. All rights reserved
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

use Alledia\OSDownloads\Factory;
use Alledia\OSDownloads\Free\DisplayData;
use Alledia\OSDownloads\Free\Joomla\Module\File as FileModule;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Layout\LayoutHelper;
use OSDownloadsViewDownloads as ViewDownloads;
use OSDownloadsViewItem as ViewItem;

defined('_JEXEC') or die();

/**
 * @var FileLayout                                    $this
 * @var ViewItem|ViewDownloads|DisplayData|FileModule $displayData
 * @var string                                        $layoutOutput
 * @var string                                        $path
 */

$elementId = $this->getOptions()->get('elementId');

if ($elementId && $displayData->item->require_user_email && ComponentHelper::isEnabled('com_fields')) :
    $form = new Form('com_osdownloads.download');
    $form->load('<?xml version="1.0" encoding="utf-8"?><form><fieldset/></form>');

    Factory::getApplication()->triggerEvent(
        'onContentPrepareForm',
        [
            $form,
            [
                'catid' => $displayData->item->catid ?? null,
            ],
        ]
    );

    if ($form->getFieldsets()) :
        // Add IDs to avoid ID crashing when multiple forms on the page
        $fields = $form->getXml()->xpath('//field');
        foreach ($fields as $field) :
            $field['id'] = $elementId . '_' . $field['name'];
        endforeach;
        ?>
        <div class="osdownloads-custom-fields-container">
            <?php
            $displayData->setForm($form);
            echo LayoutHelper::render('joomla.edit.fieldset', $displayData);
            ?>
        </div>
    <?php endif;
endif;
