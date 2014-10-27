<?php
/**
 * @package   AllediaInstaller
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2014 Alledia.com, All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die();
?>

<?php if ($isLicensesManagerInstalled) : ?>

    <div class="alledia-license-form">
        <?php if (!empty($licenseKey)) : ?>

            <a href="javascript:void(0);" class="alledia-installer-change-license-button alledia-button">
                <?php echo JText::_('LIB_ALLEDIAINSTALLER_CHANGE_LICENSE_KEY'); ?>
            </a>

        <?php endif; ?>

        <div id="alledia-installer-license-panel" style="display: <?php echo empty($licenseKey)? '' : 'none'; ?>;">
            <input
                type="text"
                name="alledia-license-keys"
                id="alledia-license-keys"
                value="<?php echo $licenseKey; ?>"
                placeholder="<?php echo JText::_('LIB_ALLEDIAINSTALLER_LICENSE_KEYS_PLACEHOLDER'); ?>" />

            <p class="alledia-empty-key-msg">
                <?php echo JText::_('LIB_ALLEDIAINSTALLER_MSG_LICENSE_KEYS_EMPTY'); ?>&nbsp;
                <a href="https://www.alledia.com/account/key/" target="_blank">
                    <?php echo JText::_('LIB_ALLEDIAINSTALLER_I_DONT_REMEMBER_MY_KEY'); ?>
                </a>
            </p>

            <a
                id="alledia-license-save-button"
                class="alledia-button"
                href="javascript:void(0);">

                <?php echo JText::_('LIB_ALLEDIAINSTALLER_SAVE_LICENSE_KEY'); ?>
            </a>
        </div>

        <div id="alledia-installer-license-success" style="display: none">
            <p>
                <?php echo JText::_('LIB_ALLEDIAINSTALLER_LICENSE_KEY_SUCCESS'); ?>
            </p>
        </div>

        <div id="alledia-installer-license-error" style="display: none">
            <p>
                <?php echo JText::_('LIB_ALLEDIAINSTALLER_LICENSE_KEY_ERROR'); ?>
            </p>
        </div>
    </div>

    <script>
    (function($) {

        $(function() {

            $('.alledia-installer-change-license-button').on('click', function() {
                $('#alledia-installer-license-panel').show();
                $(this).hide();
            });

            $('#alledia-license-save-button').on('click', function() {

                $.post('<?php echo JURI::root(); ?>/administrator/index.php?plugin=system_osmylicensesmanager&task=license.save',
                    {
                        'license-keys': $('#alledia-license-keys').val()
                    },
                    function(data) {
                        try
                        {
                            var result = JSON.parse(data);

                            $('#alledia-installer-license-panel').hide();

                            if (result.success) {
                                $('#alledia-installer-license-success').show();
                            } else {
                                $('#alledia-installer-license-error').show();
                            }
                        } catch (e) {
                            $('#alledia-installer-license-panel').hide();
                            $('#alledia-installer-license-error').show();
                        }
                    },
                    'text'
                ).fail(function() {
                    $('#alledia-installer-license-panel').hide();
                    $('#alledia-installer-license-error').show();
                });

            });
        });

    })(jQueryAlledia);
    </script>

<?php else : ?>
    <div class="error">
        <?php echo JText::_('LIB_ALLEDIAINSTALLER_LICENSE_KEYS_MANAGER_REQUIRED'); ?>
    </div>
<?php endif; ?>
