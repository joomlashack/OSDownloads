<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');


class JFormFieldGoPro extends JFormField
{
    public $fromInstaller = false;

    public function getInput() {

        if (version_compare(JVERSION, '3.0', 'ge')){
            $class_ = 'ost-joomla-3';
        }else{
            $class_ = 'ost-joomla-2';
        }

        JHtml::stylesheet( JURI::root() . 'media/lib_allediaframework/css/style_gopro_field.css' );
        $html = '<div class="ost-alert-gopro ' . $this->class . ' ' . $class_ . ' ' . ($this->fromInstaller ? 'no_offset':'') . '">
            <a href="https://www.alledia.com/plans/" class="ost-alert-btn" target="_blank">
                <i class="icon-publish"></i> Go Pro to access more features
            </a>
            <img src="' . JURI::root() . 'media/lib_allediaframework/images/alledia_logo.png" style="width:120px; height:auto;" alt=""/>
            ' . ($this->fromInstaller ? '<span>&copy; 2014 Alledia.com. All rights reserved.</span>':'' ) . '
        </div>';

        return $html;
    }

    public function getLabel(){
        return '';
    }
}
