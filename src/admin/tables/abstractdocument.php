<?php
/**
 * @package   OSDownloads
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2015 Open Source Training, LLC. All rights reserved
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

require_once __DIR__ . '/abstract.php';

class OSDownloadsTableAbstractDocument extends OSDownloadsTableAbstract
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
    public $access;
    public $agreement_article_id;
    public $created_user_id;
    public $created_time;
    public $modified_user_id;
    public $modified_time;

    /**
     * Event dispatcher
     *
     * @var JEventDispatcher
     */
    protected $dispatcher;

    public function __construct(&$_db)
    {
        parent::__construct('#__osdownloads_documents', 'id', $_db);
    }

    public function store($updateNulls = false)
    {
        $isNew = false;
        $date  = JFactory::getDate();
        $user  = JFactory::getUser();

        $this->modified_time = $date->toSql();

        if (isset($this->id) && !empty($this->id)) {
            // Existing document
            $this->modified_user_id = $user->get('id');
        } else {
            // New document
            $this->downloaded      = 0;
            $this->created_time    = $date->toSql();
            $this->created_user_id = $user->get('id');
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
        $dispatcher = $this->getDispatcher();
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
        // Trigger events to osdownloads plugins
        $dispatcher = $this->getDispatcher();
        $pluginResults = $dispatcher->trigger('onOSDownloadsBeforeDeleteFile', array(&$this, $pk));

        $result = false;
        if ($pluginResults !== false) {
            $result = parent::delete($pk['id']);

            $dispatcher->trigger('onOSDownloadsAfterDeleteFile', array($result, $this->id, $pk));
        }

        return $result;
    }
}
