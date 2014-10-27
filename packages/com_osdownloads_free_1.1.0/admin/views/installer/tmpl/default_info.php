<?php
/**
 * @package   AllediaInstaller
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2014 Alledia.com, All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die();
?>
<div class="alledia-details-container">

    <a href="javascript:void(0);" id="alledia-installer-footer-toggler">
        <?php echo JText::_('LIB_ALLEDIAINSTALLER_SHOW_DETAILS'); ?>
    </a>

    <div id="alledia-installer-footer" style="display: none;">
        <div class="alledia-license">
            Release: v<?php echo $this->manifest->version; ?>
        </div>
        <br>
        <?php if (!empty($this->manifest->alledia->relatedExtensions)) : ?>
            <table class="alledia-related-table">
                <thead>
                    <tr>
                        <th colspan="2"><?php echo JText::_('LIB_ALLEDIAINSTALLER_RELATED_EXTENSIONS'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($this->relatedExtensionFeedback as $element => $data) : ?>
                        <tr>
                            <td><?php echo JText::_($data['name']); ?></td>
                            <td>
                                <?php
                                $messages = array($data['message']);

                                if (isset($data['publish'])) {
                                    $messages[] = JText::_('LIB_ALLEDIAINSTALLER_PUBLISHED');
                                }

                                if (isset($data['ordering'])) {
                                    $messages[] = JText::_('LIB_ALLEDIAINSTALLER_SORTED');
                                }

                                $messages = implode(', ', $messages);
                                echo $messages;

                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <div class="alledia-license">
            <?php echo (string) $this->manifest->alledia->namespace; ?> and all related extensions are licensed as <a href="http://www.gnu.org/licenses/gpl-3.0.html">GNU/GPL v3.0</a>.
        </div>
    </div>

</div>

<script>
(function($) {

    $(function() {
        // More info button
        $('#alledia-installer-footer-toggler').on('click', function(event) {
            $('#alledia-installer-footer').show();
            $(this).hide();
        });
    });

})(jQueryAlledia);
</script>
