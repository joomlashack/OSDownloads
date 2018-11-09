<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2018 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

use Alledia\Framework\Helper as AllediaHelper;
use Alledia\OSDownloads\Free\Factory;
use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die();

$lang          = JFactory::getLanguage();
$container     = Factory::getContainer();
$app           = JFactory::getApplication('site');
$compParams    = $app->getParams('com_osdownloads');
$elementsId    = md5('osdownloads_download_button_' . $displayData->item->id . '_' . uniqid());
$buttonClasses = isset($displayData->buttonClasses) ? $displayData->buttonClasses : '';
$actionUrl     = JRoute::_(
    $container->helperRoute->getFileDownloadContentRoute($displayData->item->id, $displayData->itemId)
);

JHtml::_('behavior.formvalidator');

JFactory::getDocument()->addScriptDeclaration("
jQuery(function osdownloadsDomReady($) {
        $('#{$elementsId}_link').osdownloads();
    });
");

$attribs = array(
    'href'                   => $actionUrl,
    'id'                     => $elementsId . '_link',
    'style'                  => (!empty($displayData->item->download_color)) ? 'background:' . $displayData->item->download_color : '',
    'class'                  => 'osdownloads-download-button osdownloads-readmore readmore ' . $buttonClasses,
    'data-direct-page'       => $displayData->item->direct_page,
    'data-require-email'     => $displayData->item->require_user_email,
    'data-require-agree'     => $displayData->item->require_agree,
    'data-require-share'     => $displayData->item->require_share,
    'data-url'               => JURI::current(),
    'data-lang'              => $lang->getTag(),
    'data-name'              => $displayData->item->name,
    'data-agreement-article' => $displayData->item->agreementLink,
    'data-prefix'            => $elementsId,
    'data-animation'         => $displayData->params->get("popup_animation", "fade"),
    'data-fields-layout'     => $compParams->get('download_form_fields_layout', 'block')
);

if ($displayData->isPro && (bool)@$displayData->item->require_share) :
    $attribs['data-hashtags'] = str_replace('#', '', @$displayData->item->twitter_hashtags);
    $attribs['data-via']      = str_replace('@', '', @$displayData->item->twitter_via);
    $attribs['data-text']     = str_replace('{name}', $displayData->item->name, @$displayData->item->twitter_text);
endif;
?>
<a <?php echo ArrayHelper::toString($attribs); ?>>
    <span>
        <?php
        echo $displayData->item->download_text
            ? $displayData->item->download_text
            : JText::_("COM_OSDOWNLOADS_DOWNLOAD");
        ?>
    </span>
</a>

<div id="<?php echo $elementsId . '_popup'; ?>"
     class="reveal-modal osdownloads-modal <?php echo AllediaHelper::getJoomlaVersionCssClass(); ?>"
     data-prefix="<?php echo $elementsId; ?>">
    <?php
    if ($displayData->item->require_user_email
        || $displayData->item->require_agree
        || $displayData->item->require_share
    ) :
        ?>
        <h2 class="title">
            <?php
            echo JText::_($compParams->get('download_form_title', 'COM_OSDOWNLOADS_BEFORE_DOWNLOAD'));
            ?>
        </h2>

        <?php
        $header = $compParams->get('download_form_header');
        if (!empty($header)) :
            echo $header;
        endif;
        ?>

        <form action="<?php echo $actionUrl; ?>"
              id="<?php echo $elementsId . '_form'; ?>"
              name="<?php echo $elementsId . '_form'; ?>"
              class="form-validate"
              method="post">

            <div id="<?php echo $elementsId . 'EmailGroup'; ?>"
                 class="osdownloadsemail osdownloads-email-group"
                 style="display: none;">

                <label for="<?php echo $elementsId; ?>RequireEmail">
                    <input type="email"
                           aria-required="true"
                           <?php echo $displayData->item->require_user_email == 1 ? 'required' : ''; ?>
                           name="require_email"
                           id="<?php echo $elementsId; ?>RequireEmail"
                           class="osdownloads-field-email"
                           placeholder="<?php echo JText::_("COM_OSDOWNLOADS_ENTER_EMAIL_ADDRESS"); ?>"/>
                </label>

                <div class="error osdownloads-error-email" style="display: none;"
                     id="<?php echo $elementsId; ?>ErrorInvalidEmail">
                    <?php echo JText::_("COM_OSDOWNLOADS_INVALID_EMAIL"); ?>
                </div>
            </div>
            <?php
            if ($displayData->item->require_user_email && JComponentHelper::isEnabled('com_fields')) :
                ?>
                <div class="osdownloads-custom-fields-container">
                    <?php
                    $displayData->tab_name = $elementsId . '-tab-' . $displayData->item->id;
                    echo JHtml::_('bootstrap.startTabSet', $displayData->tab_name, array('active' => false));

                    $displayData->form = new JForm('com_osdownloads.download');

                    $dispatcher = JEventDispatcher::getInstance();
                    $dispatcher->trigger(
                        'onContentPrepareForm',
                        array(
                            $displayData->form,
                            array(
                                'catid' => $displayData->item->cate_id
                            )
                        )
                    );

                    echo JLayoutHelper::render('joomla.edit.params', $displayData);

                    echo JHtml::_('bootstrap.endTabSet');
                    ?>
                </div>
            <?php
            endif;
            ?>
            <div id="<?php echo $elementsId; ?>AgreeGroup"
                 class="osdownloadsagree osdownloads-group-agree"
                 style="display: none;">
                <label for="<?php echo $elementsId; ?>RequireAgree">
                    <input type="checkbox"
                           name="require_agree"
                           id="<?php echo $elementsId; ?>RequireAgree"
                           value="1"
                           class="osdownloads-field-agree"/>
                    <span>
                        * <?php echo(JText::_("COM_OSDOWNLOADS_DOWNLOAD_TERM")); ?>
                    </span>
                </label>
                <div class="error osdownloads-error-agree"
                     style="display: none;"
                     id="<?php echo $elementsId; ?>ErrorAgreeTerms">
                    <?php echo JText::_("COM_OSDOWNLOADS_YOU_HAVE_AGREE_TERMS_TO_DOWNLOAD_THIS"); ?>
                </div>
            </div>
        </form>
        <?php
        if ($displayData->isPro) :
            echo JLayoutHelper::render('social_download', $displayData);
        endif;
        ?>

        <a href="#" id="<?php echo $elementsId; ?>DownloadContinue"
           class="osdownloads-readmore readmore osdownloads-continue-button">
            <span>
                <?php echo JText::_($compParams->get('download_form_button_label', 'COM_OSDOWNLOADS_CONTINUE')); ?>
            </span>
        </a>

        <?php
        $footer = $compParams->get('download_form_footer');
        if (!empty($footer)) :
            echo $footer;
        endif;
        ?>

        <a class="close-reveal-modal">&#215;</a>
    <?php
    else :
        ?>
        <form action="<?php echo $actionUrl; ?>"
              id="<?php echo $elementsId . '_form'; ?>"
              name="<?php echo $elementsId . '_form'; ?>"
              method="post">
        </form>
    <?php
    endif;
    ?>
</div>
