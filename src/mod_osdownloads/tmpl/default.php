<?php
/**
 * @version 1.0.0
 * @author Open Source Training (www.ostraining.com)
 * @copyright (C) 2014 Open Source Training
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/
defined('_JEXEC') or die;
JHTML::_('behavior.modal');

$app = JFactory::getApplication();
$itemId = $app->input->get('Itemid');

$moduleTag = $params->get('module_tag', 'div');
$headerTag = $params->get('header_tag', 'h3');
$linkTo    = $params->get('link_to', 'download');

// Title
if ((bool)$module->showtitle) {
    echo sprintf('<%s class="%s">%s</%s>', $headerTag, $params->get('header_class'), $module->title, $headerTag);
}

// Module body
?>

<<?php echo $moduleTag; ?> class="mod_osdownloads<?php echo $params->get('moduleclass_sfx'); ?>">
    <ul>
        <?php foreach ($list as $file) : ?>
            <li>
                <h3><?php echo $file->name; ?></h3>
                <p><?php echo $file->description_1; ?></p>
                <p>
                    <?php if ($linkTo === 'download') : ?>
                        <a class="modOSDownloadsButton" href="index.php?option=com_osdownloads&task=getdownloadlink&tmpl=component&Itemid=<?php echo $itemId; ?>&id=<?php echo $file->id; ?>" data-direct-page="<?php echo $file->direct_page; ?>">
                            <?php echo $params->get('link_label', JText::_('MOD_OSDOWNLOADS_DOWNLOAD')); ?>
                        </a>
                    <?php else: ?>
                        <a class="modOSDownloadsButton" href="index.php?option=com_osdownloads&view=item&Itemid=<?php echo $itemId; ?>&id=<?php echo $file->id; ?>" data-direct-page="<?php echo $file->direct_page; ?>">
                            <?php echo $params->get('link_label', JText::_('MOD_OSDOWNLOADS_READ_MORE')); ?>
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
