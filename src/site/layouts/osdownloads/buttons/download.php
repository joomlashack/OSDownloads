<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2005-2022 Joomlashack.com. All rights reserved
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

use Alledia\Framework\Helper as FrameworkHelper;
use Alledia\OSDownloads\Factory;
use Alledia\OSDownloads\Free\DisplayData;
use Alledia\OSDownloads\Free\Joomla\Module\File as FileModule;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use OSDownloadsViewDownloads as ViewDownloads;
use OSDownloadsViewItem as ViewItem;

defined('_JEXEC') or die();

/**
 * @var FileLayout                                    $this
 * @var ViewItem|ViewDownloads|DisplayData|FileModule $displayData
 * @var string                                        $layoutOutput
 * @var string                                        $path
 */

$lang          = Factory::getLanguage();
$container     = Factory::getPimpleContainer();
$compParams    = ComponentHelper::getParams('com_osdownloads');
$elementId     = sprintf('file%s_%s', $displayData->item->id, uniqid());
$buttonClasses = $displayData->buttonClasses ?? '';
$actionUrl     = Route::_(
    $container->helperRoute->getFileDownloadContentRoute($displayData->item->id, $displayData->itemId)
);

//HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('script', 'com_osdownloads/jquery.validate.min.js', ['relative' => true]);

Factory::getDocument()->addScriptDeclaration("
jQuery(function osdownloadsDomReady($) {
        $('#{$elementId}_link').osdownloads();
    });
");

$attribs = [
    'href'                   => $actionUrl,
    'id'                     => $elementId . '_link',
    'style'                  => !empty($displayData->item->download_color)
        ? 'background:' . $displayData->item->download_color
        : '',
    'class'                  => 'osdownloads-download-button osdownloads-readmore readmore ' . $buttonClasses,
    'data-direct-page'       => $displayData->item->direct_page,
    'data-require-email'     => $displayData->item->require_user_email,
    'data-require-agree'     => $displayData->item->require_agree,
    'data-url'               => Uri::current(),
    'data-lang'              => $lang->getTag(),
    'data-name'              => $displayData->item->name,
    'data-agreement-article' => $displayData->item->agreementLink,
    'data-prefix'            => $elementId,
    'data-animation'         => $displayData->params->get('popup_animation', 'fade'),
    'data-fields-layout'     => $compParams->get('download_form_fields_layout', 'block')
];

echo HTMLHelper::_(
    'link',
    $actionUrl,
    sprintf('<span>%s</span>', $displayData->item->download_text ?: Text::_('COM_OSDOWNLOADS_DOWNLOAD')),
    $attribs
);

if ($compParams->get('download_form_translate')) :
    $header = Text::_('COM_OSDOWNLOADS_DOWNLOAD_FORM_HEADER');
    $footer = Text::_('COM_OSDOWNLOADS_DOWNLOAD_FORM_FOOTER');

else :
    $header = $compParams->get('download_form_header');
    $footer = $compParams->get('download_form_footer');
endif;

?>
<div id="<?php echo $elementId . '_popup'; ?>"
     class="reveal-modal osdownloads-modal <?php echo FrameworkHelper::getJoomlaVersionCssClass(); ?>"
     data-prefix="<?php echo $elementId; ?>">
    <?php if ($displayData->item->require_user_email || $displayData->item->require_agree) : ?>
        <h2 class="title">
            <?php
            echo Text::_($compParams->get('download_form_title', 'COM_OSDOWNLOADS_BEFORE_DOWNLOAD'));
            ?>
        </h2>
        <?php
        if ($header) :
            echo sprintf('<div class="osdownloads-header">%s</div>', $header);
        endif;
        ?>
        <form action="<?php echo $actionUrl; ?>"
              id="<?php echo $elementId . '_form'; ?>"
              name="<?php echo $elementId . '_form'; ?>"
              class="form-validate"
              method="post">

            <?php if ($displayData->item->require_user_email) : ?>
                <div id="<?php echo $elementId . '_emailgroup'; ?>"
                     class="osdownloadsemail osdownloads-email-group">

                    <label for="<?php echo $elementId; ?>_requireemail">
                        <input type="email"
                               required
                               name="require_email"
                               id="<?php echo $elementId; ?>_requireemail"
                               class="osdownloads-field-email"
                               data-msg="<?php echo Text::_('COM_OSDOWNLOADS_INVALID_EMAIL'); ?>"
                               placeholder="<?php echo Text::_('COM_OSDOWNLOADS_ENTER_EMAIL_ADDRESS'); ?>"/>
                    </label>
                </div>
            <?php
            endif;

            echo LayoutHelper::render(
                'osdownloads.fields.custom',
                $displayData,
                null,
                ['elementId' => $elementId]
            );

            if ($displayData->item->require_agree) : ?>
                <div id="<?php echo $elementId; ?>_agreegroup"
                     class="osdownloads-group-agree">
                    <label for="<?php echo $elementId; ?>_requireagree">
                        <input type="checkbox"
                               name="require_agree"
                               id="<?php echo $elementId; ?>_requireagree"
                               value="1"
                               required
                               data-msg="<?php echo Text::_('COM_OSDOWNLOADS_YOU_HAVE_AGREE_TERMS_TO_DOWNLOAD_THIS'); ?>"
                               class="osdownloads-field-agree"/>
                        <span>
                           * <?php echo(Text::_('COM_OSDOWNLOADS_DOWNLOAD_TERM')); ?>
                        </span>
                    </label>
                </div>
                <label for="require_agree"
                       class="error osdownloads-error-agree"
                       id="<?php echo $elementId; ?>_erroragreeterms">
                </label>
            <?php endif; ?>
        </form>

        <a href="#" id="<?php echo $elementId; ?>_downloadcontinue"
           class="osdownloads-readmore readmore osdownloads-continue-button">
            <span>
                <?php echo Text::_($compParams->get('download_form_button_label', 'COM_OSDOWNLOADS_CONTINUE')); ?>
            </span>
        </a>

        <?php
        if ($footer) :
            echo sprintf('<div class="osdownloads-footer">%s</div>', $footer);
        endif;
        ?>

        <a class="close-reveal-modal">&#215;</a>
    <?php else : ?>
        <form action="<?php echo $actionUrl; ?>"
              id="<?php echo $elementId . '_form'; ?>"
              name="<?php echo $elementId . '_form'; ?>"
              method="post">
        </form>
    <?php endif; ?>
</div>
