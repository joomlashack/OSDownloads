<?php
/**
 * @package   com_osdownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2018 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace Alledia\OSDownloads\Free\Joomla\Component;

defined('_JEXEC') or die();

use Alledia\Framework\Joomla\Extension\AbstractComponent;
use Alledia\Framework\Factory;
use JHtml;

class Site extends AbstractComponent
{
    protected static $instance;

    protected $version = 'auto';

    public static function getInstance($namespace = null)
    {
        return parent::getInstance('OSDownloads');
    }

    public function init()
    {
        $options = array('version' => $this->getMediaVersion(), 'relative' => true);

        JHtml::_('stylesheet', 'com_osdownloads/frontend.css', $options, array());

        parent::init();
    }

    public function getMediaVersion()
    {
        if ('auto' === $this->version) {
            $manifest = $this->getManifest();

            if (!empty($manifest)) {
                $this->version = md5($manifest->version);
            }
        }

        return $this->version;
    }
}
