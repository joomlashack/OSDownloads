<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2018 Open Source Training, LLC. All rights reserved
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

use Alledia\OSDownloads\Free\Factory;

defined('_JEXEC') or die;

use Alledia\OSDownloads\Free\Joomla\Component\Site as FreeComponentSite;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
JHtml::_('formbehavior.chosen', 'select');

JFilterOutput::objectHTMLSafe($this->item);

$editor    = JFactory::getEditor();
$container = Factory::getContainer();

$index    = strpos($this->item->file_path, "_");
$realname = substr($this->item->file_path, $index + 1);
?>

<script type="text/javascript">
    Joomla.submitbutton = function(task)
    {
        if (task == 'cancel' || document.formvalidator.isValid(document.id('item-form')))
        {
            Joomla.submitform(task, document.getElementById('item-form'));
        }
    }
</script>

<form action="<?php echo JRoute::_($container->helperRoute->getAdminMainViewRoute());?>" method="post" name="adminForm" enctype="multipart/form-data" id="item-form" class="form-validate">

    <div class="form-inline form-inline-header">
        <?php
        echo $this->form->renderField('name');
        echo $this->form->renderField('alias');
        ?>
    </div>

    <div class="form-horizontal">
        <?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>

        <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'general', JText::_('COM_OSDOWNLOADS_FILE', true)); ?>

        <div class="row-fluid">
            <div class="span9">
                <?php if ($this->item->file_path):?>
                    <div class="control-group">
                        <div class="control-label">
                            <?php echo JText::_('COM_OSDOWNLOADS_CURRENT_FILE'); ?>
                        </div>
                        <div class="controls">
                            <?php echo $realname;?>
                            <input type="hidden" name="old_file" value="<?php echo($this->item->file_path);?>" />
                        </div>
                    </div>
                <?php endif;?>

                <?php
                echo $this->form->renderField('file');
                echo $this->form->renderField('file_url');
                ?>
                <div class="control-group">
                    <div class="control-label">
                        <?php echo JText::_('COM_OSDOWNLOADS_DESCRIPTIONS'); ?>
                    </div>
                    <div class="controls">
                        <?php echo JHtml::_('bootstrap.startTabSet', 'myTabDescriptions', array('active' => 'description1')); ?>

                        <?php echo JHtml::_('bootstrap.addTab', 'myTabDescriptions', 'description1', JText::_('COM_OSDOWNLOADS_DESCRIPTION_1H', true)); ?>
                        <?php echo $this->form->getField('description_1')->input; ?>
                        <?php echo JHtml::_('bootstrap.endTab'); ?>

                        <?php echo JHtml::_('bootstrap.addTab', 'myTabDescriptions', 'description2', JText::_('COM_OSDOWNLOADS_DESCRIPTION_2H', true)); ?>
                        <?php echo $this->form->getField('description_2')->input; ?>
                        <?php echo JHtml::_('bootstrap.endTab'); ?>

                        <?php echo JHtml::_('bootstrap.addTab', 'myTabDescriptions', 'description3', JText::_('COM_OSDOWNLOADS_DESCRIPTION_3H', true)); ?>
                        <?php echo $this->form->getField('description_3')->input; ?>
                        <?php echo JHtml::_('bootstrap.endTab'); ?>

                        <?php echo JHtml::_('bootstrap.endTabSet'); ?>
                    </div>
                </div>
            </div>
            <div class="span3">
                <fieldset class="form-vertical">
                    <?php
                        $fields = $this->form->getFieldset('file-vertical');

                        foreach ($fields as $fieldName => $field) {
                            echo $this->form->renderField($field->fieldname);
                        }
                    ?>
                </fieldset>
            </div>
        </div>
        <?php echo JHtml::_('bootstrap.endTab'); ?>

        <?php
        /*=====================================
        =          Custom Fields - Pro        =
        =====================================*/

        $component = FreeComponentSite::getInstance();
        if ($component->isPro()) :
            echo $this->loadTemplate('custom_fields');
        endif;

        /*=====  End of Custom Fields  ======*/
        ?>

        <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'requirements', JText::_('COM_OSDOWNLOADS_REQUIREMENTS_TO_DOWNLOAD', true)); ?>
        <?php
            $fields = $this->form->getFieldset('requirements');

            foreach ($fields as $fieldName => $field) {
                echo $this->form->renderField($field->fieldname);
            }
        ?>
        <?php echo JHtml::_('bootstrap.endTab'); ?>

        <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'advanced', JText::_('COM_OSDOWNLOADS_ADVANCED', true)); ?>
        <?php
            $fields = $this->form->getFieldset('advanced');

            foreach ($fields as $fieldName => $field) {
                echo $this->form->renderField($field->fieldname);
            }
        ?>
        <?php echo JHtml::_('bootstrap.endTab'); ?>
        <?php echo JHtml::_('bootstrap.endTabSet'); ?>
    </div>

    <input type="hidden" name="task" value="" />
    <input type="hidden" name="option" value="com_osdownloads" />
    <input type="hidden" name="id" value="<?php echo $this->item->id; ?>" />
    <input type="hidden" name="cid[]" value="<?php echo $this->item->id; ?>" />
    <?php echo JHTML::_( 'form.token' ); ?>
</form>

<?php echo $this->extension->getFooterMarkup(); ?>
