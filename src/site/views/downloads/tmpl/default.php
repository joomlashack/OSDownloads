<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */
defined('_JEXEC') or die( 'Restricted access' );

use Alledia\Framework\Helper as AllediaHelper;

$app                    = JFactory::getApplication();
$lang                   = JFactory::getLanguage();
$doc                    = JFactory::getDocument();
$NumberOfColumn         = $this->params->get("number_of_column", 1);
$user                   = JFactory::getUser();
$authorizedAccessLevels = $user->getAuthorisedViewLevels();
$itemId                 = $app->input->getInt('Itemid');
$id                     = $app->input->getInt('id');
$showModal = false;

JHtml::_('jquery.framework');
JHtml::script(JUri::root() . '/media/com_osdownloads/js/jquery.osdownloads.bundle.min.js');

?>
<form action="<?php echo(JRoute::_("index.php?option=com_osdownloads&view=downloads&id=".$id."&Itemid=".$itemId));?>" method="post" name="adminForm" id="adminForm">
    <div class="contentopen osdownloads-container">
        <?php if (!empty($this->paths)) : ?>
            <h2>
                <?php for ($i = count($this->paths) - 1; $i >= 0; $i--):?>
                    <?php if ($i):?>
                        <a href="<?php echo(JRoute::_("index.php?option=com_osdownloads&view=downloads&id={$this->paths[$i]->id}"."&Itemid=".$itemId));?>">
                    <?php endif;?>
                    <?php echo($this->paths[$i]->title);?>
                    <?php if ($i > 0):?>
                        </a> <span class="divider">::</span>
                    <?php endif;?>
                <?php endfor;?>
            </h2>
        <?php endif; ?>

        <?php if (isset($this->paths[0])) : ?>
            <div class="sub_title">
                <?php echo($this->paths[0]->description);?>
            </div>
        <?php endif; ?>

        <?php if ($this->showCategoryFilter && count($this->categories) > 1) : ?>
            <div class="category_filter">
                <?php
                $i = 0;
                foreach($this->categories as $category):?>
                    <?php if (in_array($category->access, $authorizedAccessLevels)) : ?>
                        <div class="item<?php echo($i % $NumberOfColumn);?> cate_<?php echo($category->id);?>">
                            <h3>
                                <a href="<?php echo(JRoute::_("index.php?option=com_osdownloads&view=downloads&id={$category->id}"."&Itemid=".$itemId));?>">
                                    <?php echo($category->title);?>
                                </a>
                            </h3>
                            <div class="item_content">
                                <?php echo($category->description);?>
                            </div>
                        </div>
                        <?php if ($NumberOfColumn && $i % $NumberOfColumn == $NumberOfColumn - 1):?>
                            <div class="seperator"></div>
                            <div class="clr"></div>
                        <?php endif;?>
                        <?php $i++;?>
                    <?php endif; ?>
                <?php endforeach;?>
                <div class="clr"></div>
            </div>
        <?php endif; ?>

        <?php if (!empty($this->items)) : ?>
            <?php foreach($this->items as $file):?>
                <?php
                $requireEmail = $file->require_user_email;
                $requireAgree = (bool) $file->require_agree;
                $requireShare = (bool) @$file->require_share;

                if (!$showModal) {
                    $showModal = $requireEmail || $requireAgree || $requireShare;
                }

                ?>
                <?php if (in_array($file->access, $authorizedAccessLevels)) : ?>
                    <div class="item_<?php echo($file->id);?>">
                        <h3><a href="<?php echo(JRoute::_("index.php?option=com_osdownloads&view=item&id=".$file->id."&Itemid=".$itemId));?>"><?php echo($file->name);?></a></h3>
                        <div class="item_content"><?php echo($file->brief);?></div>

                        <?php if ($this->params->get('show_download_button', 0)) : ?>
                            <div class="osdownloadsactions">
                                <div class="btn_download">
                                    <?php
                                    $fileURL = JRoute::_('index.php?option=com_osdownloads&view=item&Itemid=' . $itemId . '&id=' . $file->id);
                                    ?>
                                    <a
                                        href="<?php echo JRoute::_('index.php?option=com_osdownloads&task=routedownload&tmpl=component&Itemid=' . $itemId . '&id=' . $file->id); ?>"
                                        class="osdownloadsDownloadButton"
                                        style="color:<?php echo $file->download_color;?>"
                                        data-direct-page="<?php echo $file->direct_page; ?>"
                                        data-require-email="<?php echo $requireEmail; ?>"
                                        data-require-agree="<?php echo $requireAgree ? 1 : 0; ?>"
                                        data-require-share="<?php echo $requireShare ? 1 : 0; ?>"
                                        data-id="<?php echo $file->id; ?>"
                                        data-url="<?php echo $fileURL; ?>"
                                        data-lang="<?php echo $lang->getTag(); ?>"
                                        data-name="<?php echo $file->name; ?>"
                                        data-agreement-article="<?php echo $file->agreementLink; ?>"
                                        <?php if ($this->isPro) : ?>
                                            data-hashtags="<?php echo str_replace('#', '', @$file->twitter_hashtags); ?>"
                                            data-via="<?php echo str_replace('@', '', @$file->twitter_via); ?>"
                                        <?php endif; ?>
                                        >
                                        <span>
                                            <?php echo $this->params->get('link_label', JText::_('COM_OSDOWNLOADS_DOWNLOAD')); ?>
                                        </span>
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($this->params->get('show_readmore_button', 1)) : ?>
                            <div class="osdownloads-readmore-wrapper readmore_wrapper">
                                <div class="osdownloads-readmore readmore">
                                    <a href="<?php echo(JRoute::_("index.php?option=com_osdownloads&view=item&id=".$file->id."&Itemid=".$itemId));?>">
                                        <?php echo(JText::_("COM_OSDOWNLOADS_READ_MORE"));?>
                                    </a>
                                </div>
                                <div class="clr"></div>
                            </div>
                        <?php endif; ?>
                        <div class="seperator"></div>
                    </div>
                <?php endif; ?>
            <?php endforeach;?>
        <?php else : ?>
            <div class="osd-alert">
                <?php echo JText::_('COM_OSDOWNLOADS_NO_DOWNLOADS'); ?>
            </div>
        <?php endif; ?>

        <div class="clr"></div>
        <div class="osdownloads-pages-counter"><?php echo $this->pagination->getPagesCounter(); ?></div>
        <div class="osdownloads-pagination"><?php echo $this->pagination->getPagesLinks(); ?></div>
    </div>

</form>

<?php if ($this->params->get('show_download_button', 1)) : ?>
    <div id="osdownloadsRequirementsPopup" class="reveal-modal osdownloads-modal <?php echo AllediaHelper::getJoomlaVersionCssClass(); ?>">
        <h2 class="title"><?php echo JText::_('COM_OSDOWNLOADS_BEFORE_DOWNLOAD'); ?></h2>

        <div id="osdownloadsEmailGroup" class="osdownloadsemail" style="display: none;">

            <label for="osdownloadsRequireEmail">
                <input type="email" aria-required="true" required name="require_email" id="osdownloadsRequireEmail" placeholder="<?php echo JText::_("COM_OSDOWNLOADS_ENTER_EMAIL_ADDRESS"); ?>" />
            </label>

            <div class="error" style="display: none;" id="osdownloadsErrorInvalidEmail">
                <?php echo JText::_("COM_OSDOWNLOADS_INVALID_EMAIL"); ?>
            </div>
        </div>

        <div id="osdownloadsAgreeGroup" class="osdownloadsagree" style="display: none;">
            <label for="osdownloadsRequireAgree">
                <input type="checkbox" name="require_agree" id="osdownloadsRequireAgree" value="1" />
                <span>
                    * <?php echo(JText::_("COM_OSDOWNLOADS_DOWNLOAD_TERM"));?>
                </span>
            </label>

            <div class="error" style="display: none;" id="osdownloadsErrorAgreeTerms">
                <?php echo JText::_("COM_OSDOWNLOADS_YOU_HAVE_AGREE_TERMS_TO_DOWNLOAD_THIS"); ?>
            </div>
        </div>

        <?php if ($this->isPro) : ?>
            <div id="osdownloadsShareGroup" class="osdownloadsshare" style="display: none;">
                <!-- Facebook -->
                <div id="fb-root"></div>

                <p id="osdownloadsRequiredShareMessage" style="display: none;">
                    <?php echo JText::_('COM_OSDOWNLOADS_YOU_MUST_TWEET_SHARE_FACEBOOK'); ?>
                </p>

                <div class="error" style="display: none;" id="osdownloadsErrorShare">
                    <?php echo JText::_("COM_OSDOWNLOADS_SHARE_TO_DOWNLOAD_THIS"); ?>
                </div>
            </div>

        <?php endif; ?>

        <a href="#"  id="osdownloadsDownloadContinue" class="osdownloads-readmore readmore">
            <span>
                <?php echo JText::_("COM_OSDOWNLOADS_CONTINUE"); ?>
            </span>
        </a>

        <a class="close-reveal-modal">&#215;</a>
    </div>

    <script>
    (function ($) {

        $(function osdownloadsDomReady() {
            $('.osdownloads-container .osdownloadsDownloadButton').osdownloads({
                animation: '<?php echo $this->params->get("popup_animation", "fade"); ?>',
                elementsPrefix: 'osdownloads',
                popupElementId: 'osdownloadsRequirementsPopup'
            });
        });

    })(jQuery);
    </script>

<?php endif;?>
