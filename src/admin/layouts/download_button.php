<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2017 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');

use Alledia\Framework\Helper as AllediaHelper;
use Alledia\OSDownloads\Free\Factory;

$lang      = JFactory::getLanguage();
$container = Factory::getContainer();
?>
<a
    href="<?php echo JRoute::_($container->helperRoute->getFileDownloadContentRoute($displayData->item->id, $displayData->itemId)); ?>"
    id="osdownloadsDownloadButton<?php echo $displayData->item->id; ?>"
    style="background:<?php echo($displayData->item->download_color); ?>;"
    class="osdownloads-readmore readmore"
    data-direct-page="<?php echo $displayData->item->direct_page; ?>"
    data-require-email="<?php echo $displayData->item->require_user_email; ?>"
    data-require-agree="<?php echo $displayData->item->require_agree; ?>"
    data-require-share="<?php echo $displayData->item->require_share; ?>"
    data-url="<?php echo JURI::current(); ?>"
    data-lang="<?php echo $lang->getTag(); ?>"
    data-name="<?php echo $displayData->item->name; ?>"
    data-agreement-article="<?php echo $displayData->item->agreementLink; ?>"
    data-form-id="osdownloadsDownloadFieldsForm<?php echo $displayData->item->id; ?>"
    <?php if ($displayData->isPro && (bool)@$displayData->item->require_share) : ?>
        data-hashtags="<?php echo str_replace('#', '', @$displayData->item->twitter_hashtags); ?>"
        data-via="<?php echo str_replace('@', '', @$displayData->item->twitter_via); ?>"
        data-text="<?php echo str_replace('{name}', $displayData->item->name, @$displayData->item->twitter_text); ?>"
    <?php endif; ?>
>
    <span>
        <?php echo $displayData->item->download_text ? $displayData->item->download_text : JText::_("COM_OSDOWNLOADS_DOWNLOAD") ; ?>
    </span>
</a>

<?php if ($displayData->item->require_user_email || $displayData->item->require_agree || $displayData->item->require_share) : ?>
    <div
        id="osdownloadsDownloadFields<?php echo $displayData->item->id; ?>"
        class="reveal-modal osdownloads-modal <?php echo AllediaHelper::getJoomlaVersionCssClass(); ?>">

        <h2 class="title"><?php echo JText::_('COM_OSDOWNLOADS_BEFORE_DOWNLOAD'); ?></h2>

        <form
            action="<?php echo JRoute::_($container->helperRoute->getFileDownloadContentRoute($displayData->item->id, $displayData->itemId)); ?>"
            id="osdownloadsDownloadFieldsForm<?php echo $displayData->item->id; ?>"
            name="osdownloadsDownloadFieldsForm<?php echo $displayData->item->id; ?>"
            method="post" >

            <div id="osdownloadsEmailGroup" class="osdownloadsemail" style="display: none;">

                <label for="osdownloadsRequireEmail">
                    <input
                        type="email"
                        aria-required="true"
                        required name="require_email"
                        id="osdownloadsRequireEmail"
                        placeholder="<?php echo JText::_("COM_OSDOWNLOADS_ENTER_EMAIL_ADDRESS"); ?>"/>
                </label>

                <div class="error" style="display: none;" id="osdownloadsErrorInvalidEmail">
                    <?php echo JText::_("COM_OSDOWNLOADS_INVALID_EMAIL"); ?>
                </div>
            </div>

            <div class="osdownloads-custom-fields-container">
                <?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => false)); ?>

                <?php

                    $displayData->form = new JForm('com_osdownloads.download');

                    $dispatcher = JEventDispatcher::getInstance();
                    $dispatcher->trigger(
                        'onContentPrepareForm',
                        array(
                            $displayData->form,
                            array(
                                'catid' => $displayData->item->cate_id,
                            )
                        )
                    );

                    if (JComponentHelper::isEnabled('com_fields')) :
                        echo JLayoutHelper::render('joomla.edit.params', $displayData);
                    endif;
                ?>

                <?php echo JHtml::_('bootstrap.endTabSet'); ?>
            </div>

            <div id="osdownloadsAgreeGroup" class="osdownloadsagree" style="display: none;">
                <label for="osdownloadsRequireAgree">
                    <input type="checkbox" name="require_agree" id="osdownloadsRequireAgree" value="1"/>
                    <span>
                        * <?php echo(JText::_("COM_OSDOWNLOADS_DOWNLOAD_TERM")); ?>
                    </span>
                </label>

                <div class="error" style="display: none;" id="osdownloadsErrorAgreeTerms">
                    <?php echo JText::_("COM_OSDOWNLOADS_YOU_HAVE_AGREE_TERMS_TO_DOWNLOAD_THIS"); ?>
                </div>
            </div>
        </form>

        <?php
        if ($displayData->isPro) :
            echo $displayData->loadTemplate('pro_social_download');
        endif;
        ?>

        <a href="#" id="osdownloadsDownloadContinue" class="osdownloads-readmore readmore">
            <span>
                <?php echo JText::_("COM_OSDOWNLOADS_CONTINUE"); ?>
            </span>
        </a>

        <a class="close-reveal-modal">&#215;</a>
    </div>
<?php endif; ?>

<script>
    (function($) {
        $(function osdownloadsDomReady() {
            $('#osdownloadsDownloadButton<?php echo $displayData->item->id; ?>').osdownloads({
                animation     : '<?php echo $displayData->params->get("popup_animation", "fade"); ?>',
                elementsPrefix: 'osdownloads',
                popupElementId: 'osdownloadsDownloadFields<?php echo $displayData->item->id; ?>'
            });
        });
    })(jQuery);
</script>
