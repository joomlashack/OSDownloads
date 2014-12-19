<?php
/**
 * @package   OSDownloads
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2014 Alledia.com, All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */
defined('_JEXEC') or die( 'Restricted access' );

$app    = JFactory::getApplication();
$doc    = JFactory::getDocument();
$lang   = JFactory::getLanguage();
$params = clone($app->getParams('com_osdownloads'));
$itemId = (int) $app->input->get('Itemid');

if ($this->params->get('load_jquery', false)) {
    $doc->addScript('media/com_osdownloads/js/jquery.js');
}

$doc->addScript('media/com_osdownloads/js/jquery.osdownloads.bundle.min.js', 'text/javascript', true);

if (! $this->extension->isPro()) {
    $this->item->require_share = false;
}

?>
<div class="contentopen osdownloads-container">
    <form method="post" id="adminForm" name="adminForm">
        <h1><?php echo($this->item->name);?></h1>
        <?php if ($this->params->get("show_category", 0)):?>
            <div class="cate_info">
                Category:
                <?php for ($i = count($this->paths) - 1; $i >= 0; $i--):?>
                    <a href="<?php echo(JRoute::_("index.php?option=com_osdownloads&view=downloads&id={$this->paths[$i]->id}"));?>">
                    <?php echo($this->paths[$i]->title);?>
                    </a>
                    <?php if ($i):?>
                         <span class="divider">::</span>
                    <?php endif;?>
                <?php endfor;?>
            </div>
        <?php endif;?>
        <?php if ($this->params->get("show_download_count", 0)):?>
        <div><?php echo(JText::_("COM_OSDOWNLOADS_DOWNLOADED"));?>: <?php echo($this->item->downloaded);?></div>
        <?php endif;?>
        <div class="reference">
            <?php if ($this->item->documentation_link):?>
                <div class="readmore"><a href="<?php echo($this->item->documentation_link);?>"><?php echo(JText::_("COM_OSDOWNLOADS_DOCUMENTATION"));?></a></div>
            <?php endif;?>
            <?php if ($this->item->demo_link):?>
                <div class="readmore"><a href="<?php echo($this->item->demo_link);?>"><?php echo(JText::_("COM_OSDOWNLOADS_DEMO"));?></a></div>
            <?php endif;?>
            <?php if ($this->item->support_link):?>
                <div class="readmore"><a href="<?php echo($this->item->support_link);?>"><?php echo(JText::_("COM_OSDOWNLOADS_SUPPORT"));?></a></div>
            <?php endif;?>
            <?php if ($this->item->other_link):?>
                <div class="readmore"><a href="<?php echo($this->item->other_link);?>"><?php echo($this->item->other_name);?></a></div>
            <?php endif;?>
            <div class="clr"></div>
        </div>
        <?php if ($this->item->brief || $this->item->description_1):?>
            <div class="description1"><?php echo($this->item->brief . $this->item->description_1);?></div>
        <?php endif;?>

        <?php if ($this->item->description_2):?>
            <div class="description2"><?php echo($this->item->description_2);?></div>
        <?php endif;?>
        <div class="osdownloadsactions">
            <div class="btn_download">
                <a
                    href="<?php echo JRoute::_("index.php?option=com_osdownloads&task=getdownloadlink&tmpl=component&Itemid=".$itemId."&id={$this->item->id}");?>"
                    id="osdownloadsDownloadButton"
                    style="color:<?php echo($this->item->download_color);?>"
                    class="readmore"
                    data-direct-page="<?php echo $this->item->direct_page; ?>"
                    data-show-email="<?php echo $this->item->show_email; ?>"
                    data-require-email="<?php echo $this->item->require_email; ?>"
                    data-require-agree="<?php echo $this->item->require_agree; ?>"
                    data-require-share="<?php echo $this->item->require_share; ?>"
                    data-url="<?php echo JURI::current(); ?>"
                    data-lang="<?php echo $lang->getTag(); ?>"
                    data-name="<?php echo $this->item->name; ?>"
                    <?php if (!empty($this->item->agreement_article_id)) : ?>
                        <?php
                        // Set the article item id, if exists
                        $articleLink = 'index.php?option=com_content&view=article&id=' . (int)$this->item->agreement_article_id;
                        $menu = JFactory::getApplication()->getMenu();
                        $menuItem = $menu->getItems('link', $articleLink, true);
                        if (!empty($menuItem)) {
                            $articleItemId = $menuItem->id;
                            $articleLink .= '&Itemid=' . $articleItemId;
                        }
                        ?>
                        data-agreement-article="<?php echo JRoute::_($articleLink); ?>"
                    <?php endif; ?>
                    <?php if ($this->extension->isPro() && (bool) @$this->item->require_share) : ?>
                        data-hashtags="<?php echo str_replace('#', '', @$this->item->twitter_hashtags); ?>"
                        data-via="<?php echo str_replace('@', '', @$this->item->twitter_via); ?>"
                        data-text="<?php echo str_replace('{name}', $this->item->name, @$this->item->twitter_text); ?>"
                    <?php endif; ?>
                    >
                    <span><?php echo($this->item->download_text ? $this->item->download_text : JText::_("COM_OSDOWNLOADS_DOWNLOAD"));?></span>
                </a>
            </div>
        </div>
        <div><?php echo($this->item->description_3);?></div>
    </form>
</div>

<?php if ($this->item->require_email || $this->item->show_email || $this->item->require_agree || $this->item->require_share) : ?>
    <div id="osdownloadsRequirementsPopup" class="reveal-modal">
        <h1 class="title"><?php echo JText::_('COM_OSDOWNLOADS_BEFORE_DOWNLOAD'); ?></h1>

        <div id="osdownloadsEmailGroup" class="osdownloadsemail" style="display: none;">

            <p id="osdownloadsRequiredEmailMessage" style="display: none;">
                <?php echo JText::_('COM_OSDOWNLOADS_YOU_HAVE_INPUT_CORRECT_EMAIL_TO_GET_DOWNLOAD_LINK'); ?>
            </p>

            <label for="osdownloadsRequireEmail">
                <span>
                    <?php echo(JText::_("COM_OSDOWNLOADS_EMAIL")); ?>:
                </span>
                <input type="email" aria-required="true" required name="require_email" id="osdownloadsRequireEmail" />
            </label>

            <div class="error" style="display: none;" id="osdownloadsErrorInvalidEmail">
                <?php echo JText::_("COM_OSDOWNLOADS_INVALID_EMAIL"); ?>
            </div>
        </div>

        <div id="osdownloadsAgreeGroup" class="osdownloadsagree" style="display: none;">
            <label for="osdownloadsRequireAgree">
                <input type="checkbox" name="require_agree" id="osdownloadsRequireAgree" />
                <span>
                    * <?php echo(JText::_("COM_OSDOWNLOADS_DOWNLOAD_TERM"));?>
                </span>
            </label>

            <div class="error" style="display: none;" id="osdownloadsErrorAgreeTerms">
                <?php echo JText::_("COM_OSDOWNLOADS_YOU_HAVE_AGREE_TERMS_TO_DOWNLOAD_THIS"); ?>
            </div>
        </div>

        <?php if ($this->extension->isPro()) : ?>
            <?php
            echo $this->loadTemplate('pro_social_download');
            ?>
        <?php endif; ?>

        <a href="#"  id="osdownloadsDownloadContinue" class="readmore">
            <span>
                <?php echo JText::_("COM_OSDOWNLOADS_CONTINUE"); ?>
            </span>
        </a>

        <a class="close-reveal-modal">&#215;</a>
    </div>
<?php endif;?>

<script>
(function ($) {

    $(function osdownloadsDomReady() {
        $('#osdownloadsDownloadButton').osdownloads({
            animation: '<?php echo $params->get("popup_animation", "fade"); ?>',
            elementsPrefix: 'osdownloads',
            popupElementId: 'osdownloadsRequirementsPopup'
        });
    });

})(jQuery);
</script>
