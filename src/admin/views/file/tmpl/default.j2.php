<?php
/**
 * @package   OSDownloads
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2015 Open Source Training, LLC. All rights reserved
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');

JFilterOutput::objectHTMLSafe($this->item);
$editor = JFactory::getEditor();

$index = strpos($this->item->file_path, "_");
$realname = substr($this->item->file_path, $index + 1);
?>

<script type="text/javascript">
    Joomla.submitbutton = function(task)
    {
        if (task == 'cancel' || document.formvalidator.isValid(document.id('item-form'))) {
            <?php echo $this->form->getField('description_1')->save(); ?>
            Joomla.submitform(task, document.getElementById('item-form'));
        } else {
            alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
        }
    }
</script>

<form action="<?php echo JRoute::_("index.php?option=com_osdownloads");?>" method="post" name="adminForm" enctype="multipart/form-data" id="item-form" class="form-validate">

    <div class="width-70 fltlft">
        <fieldset class="adminform">
            <legend><?php echo JText::_('COM_OSDOWNLOADS_FIELDSET_DETAILS');?></legend>

            <ul class="adminformlist">
                <li>
                    <?php echo $this->form->getLabel('name'); ?>
                    <?php echo $this->form->getInput('name'); ?>
                </li>

                <li>
                    <?php echo $this->form->getLabel('alias'); ?>
                    <?php echo $this->form->getInput('alias'); ?>
                </li>

                <?php if (!empty($realname)) : ?>
                    <li>
                        <label for="">
                            <?php echo JText::_('COM_OSDOWNLOADS_CURRENT_FILE'); ?>
                        </label>
                        <div style="float: left;">
                            <?php echo $realname;?>
                            <input type="hidden" name="old_file" value="<?php echo($this->item->file_path);?>" />
                        </div>
                    </li>
                <?php endif; ?>

                <li>
                    <?php echo $this->form->getLabel('file'); ?>
                    <?php echo $this->form->getInput('file'); ?>
                </li>

                <li>
                    <?php echo $this->form->getLabel('file_url'); ?>
                    <?php echo $this->form->getInput('file_url'); ?>
                </li>

                <li>
                    <?php echo $this->form->getLabel('cate_id'); ?>
                    <?php echo $this->form->getInput('cate_id'); ?>
                </li>

                <li>
                    <?php echo $this->form->getLabel('published'); ?>
                    <?php echo $this->form->getInput('published'); ?>
                </li>

                <li>
                    <?php echo $this->form->getLabel('access'); ?>
                    <?php echo $this->form->getInput('access'); ?>
                </li>

                <li>
                    <label for="">
                        <?php echo JText::_('COM_OSDOWNLOADS_DESCRIPTIONS'); ?>
                    </label>

                    <?php echo JHtml::_('tabs.start', 'descriptions-tabs', array('useCookie'=>1)); ?>

                    <?php echo JHtml::_('tabs.panel', JText::_('COM_OSDOWNLOADS_DESCRIPTION_1H'), 'description1'); ?>
                    <?php echo $this->form->getField('description_1')->input; ?>

                    <?php echo JHtml::_('tabs.panel', JText::_('COM_OSDOWNLOADS_DESCRIPTION_2H'), 'description2'); ?>
                    <?php echo $this->form->getField('description_2')->input; ?>

                    <?php echo JHtml::_('tabs.panel', JText::_('COM_OSDOWNLOADS_DESCRIPTION_3H'), 'description3'); ?>
                    <?php echo $this->form->getField('description_3')->input; ?>

                    <?php echo JHtml::_('tabs.end'); ?>
                </li>
            </ul>
        </fieldset>
    </div>

    <div class="width-30 fltrt">
        <?php echo JHtml::_('sliders.start', 'categories-sliders-'.$this->item->id, array('useCookie'=>1)); ?>

        <?php echo JHtml::_('sliders.panel', JText::_('COM_OSDOWNLOADS_REQUIREMENTS_TO_DOWNLOAD'), 'requirement-download'); ?>
        <fieldset class="panelform">
            <ul class="adminformlist">
            <?php $fields = $this->form->getFieldset('requirements'); ?>
            <?php foreach ($fields as $field) : ?>
                <li>
                    <?php echo $field->label; ?>
                    <?php echo $field->input; ?>
                </li>
            <?php endforeach; ?>
            </ul>
        </fieldset>

        <?php echo JHtml::_('sliders.panel', JText::_('COM_OSDOWNLOADS_ADVANCED'), 'advanced'); ?>
        <fieldset class="panelform">
            <ul class="adminformlist">
            <?php $fields = $this->form->getFieldset('advanced'); ?>
            <?php foreach ($fields as $field) : ?>
                <li>
                    <?php echo $field->label; ?>
                    <?php echo $field->input; ?>
                </li>
            <?php endforeach; ?>
            </ul>
        </fieldset>

        <?php echo JHtml::_('sliders.end'); ?>
    </div>

    <input type="hidden" name="task" value="" />
    <input type="hidden" name="option" value="com_osdownloads" />
    <input type="hidden" name="id" value="<?php echo $this->item->id; ?>" />
    <input type="hidden" name="cid[]" value="<?php echo $this->item->id; ?>" />
    <?php echo JHTML::_( 'form.token' ); ?>
</form>

<?php echo $this->extension->getFooterMarkup(); ?>
