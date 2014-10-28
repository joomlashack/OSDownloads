<?php
/**
 * @package   OSDownloads
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2014 Alledia.com, All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */
defined('_JEXEC') or die;
JHTML::_('behavior.modal');

JHtml::_('stylesheet', JUri::base() . 'media/com_osdownloads/css/osdownloads.css');

$app = JFactory::getApplication();
$itemId = (int) $app->input->get('Itemid');

$moduleTag = $params->get('module_tag', 'div');
$headerTag = $params->get('header_tag', 'h3');
$linkTo    = $params->get('link_to', 'download');

// Module body
?>

<<?php echo $moduleTag; ?> class="mod_osdownloadsfiles<?php echo $params->get('moduleclass_sfx'); ?>">
    <ul>
        <?php foreach ($list as $file) : ?>
            <li>
                <h4><?php echo $file->name; ?></h4>
                <p><?php echo $file->description_1; ?></p>
                <p>
                    <?php if ($linkTo === 'download') : ?>
                        <a class="modOSDownloadsButton" href="<?php echo JRoute::_('index.php?option=com_osdownloads&task=getdownloadlink&tmpl=component&Itemid=' . $itemId . '&id=' . $file->id); ?>" data-direct-page="<?php echo $file->direct_page; ?>">
                            <?php echo $params->get('link_label', JText::_('MOD_OSDOWNLOADSFILES_DOWNLOAD')); ?>
                        </a>
                    <?php else: ?>
                        <a class="modOSDownloadsButton" href="<?php JRoute::_('index.php?option=com_osdownloads&view=item&Itemid=' . $itemId . '&id=' . $file->id); ?>" data-direct-page="<?php echo $file->direct_page; ?>">
                            <?php echo $params->get('link_label', JText::_('MOD_OSDOWNLOADSFILES_READ_MORE')); ?>
                        </a>
                    <?php endif; ?>
                </p>
            </li>
        <?php endforeach; ?>
    </ul>
</<?php echo $moduleTag; ?>>

<?php if ($linkTo === 'download') : ?>
    <script>
    window.addEvent('domready', function() {
        $$(".modOSDownloadsButton").each(function(el) {
            var directPage = function() {
                var dp = el.get('data-direct-page');

                if (dp) {
                    window.location = el.get('data-direct-page');
                }
            };

            el.addEvent('click', function(e) {
                (e).stop();

                SqueezeBox.open(el.get('href'), {
                    onClose: function onClose() {
                        directPage();
                    },
                    handler: 'iframe',
                    size: {x: <?php echo($params->get("width", 350));?>, y: <?php echo($params->get("height", 150));?>}
                });
            });
        });

    });
    </script>
<?php endif;
