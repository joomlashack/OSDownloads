<?php
/**
 * @version       1.0.0
 * @author        Open Source Training (www.ostraining.com)
 * @copyright (C) 2014 Open Source Training
 * @license       GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 **/
// no direct access
defined('_JEXEC') or die('Restricted access');

class OsdownloadsTableDocument extends JTable
{
    public $id;
    public $cate_id;
    public $documents;
    public $name;
    public $alias;
    public $brief;
    public $description_1;
    public $description_2;
    public $description_3;
    public $show_email;
    public $require_email;
    public $require_agree;
    public $download_text;
    public $download_color;
    public $documentation_link;
    public $demo_link;
    public $support_link;
    public $other_name;
    public $other_link;
    public $file_path;
    public $file_url;
    public $downloaded;
    public $direct_page;
    public $published = true;
    public $ordering;
    public $external_ref;

    public function __construct(&$_db)
    {
        parent::__construct('#__osdownloads_documents', 'id', $_db);
    }

    public function store($updateNulls = false)
    {
        $isNew = false;
        if (!$this->id) {
            // New document
            $this->downloaded = 0;
            $isNew = true;
        }

        if (isset($this->alias) && isset($this->name) && $this->alias == "") {
            $this->alias = preg_replace("/ /", "-", strtolower($this->name));
        }

        if (version_compare(JVERSION, '3.0', '>=')) {
            $this->alias = JApplicationHelper::stringURLSafe($this->alias);
        } else {
            $this->alias = JApplication::stringURLSafe($this->alias);
        }

        // Trigger events to osdownloads plugins
        JPluginHelper::importPlugin('osdownloads');
        $dispatcher = JEventDispatcher::getInstance();
        $pluginResults = $dispatcher->trigger('onOSDownloadsBeforeSaveFile', array(&$this, $isNew));

        $result = false;
        if ($pluginResults !== false) {
            $result = parent::store($updateNulls);

            $dispatcher->trigger('onOSDownloadsAfterSaveFile', array($result, &$this));
        }

        return $result;
    }

    public function delete($pk = null)
    {
        $id = $this->id;

        // Trigger events to osdownloads plugins
        JPluginHelper::importPlugin('osdownloads');
        $dispatcher = JEventDispatcher::getInstance();
        $pluginResults = $dispatcher->trigger('onOSDownloadsBeforeDeleteFile', array(&$this, $pk));

        $result = false;
        if ($pluginResults !== false) {
            $result = parent::delete($pk);

            $dispatcher->trigger('onOSDownloadsAfterDeleteFile', array($result, $this->id, $pk));
        }

        return $result;
    }
}
