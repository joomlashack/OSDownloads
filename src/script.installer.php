<?php
/**
 * @package   OSDownloads
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2014 Alledia.com, All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

$includePath = __DIR__ . '/admin/library/installer/include.php';
if (file_exists($includePath)) {
    require_once $includePath;
} else {
    require_once __DIR__ . '/library/installer/include.php';
}

jimport('joomla.installer.installer');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

class Com_OSDownloadsInstallerScript extends AllediaInstallerAbstract
{
    /**
     * Method to run after an install/update method
     *
     * @return void
     */
    public function postFlight($type, $parent)
    {
        parent::postFlight($type, $parent);

        $db = JFactory::getDBO();
        $db->setQuery("SHOW COLUMNS FROM #__osdownloads_documents");
        $rows = $db->loadObjectList();
        $db_version = "1.0.0";

        $has_show_email    = false;
        $has_description_3 = false;
        $has_direct_field  = false;
        $has_file_url      = false;
        $has_parent_id     = false;
        $has_cms_version   = false;
        $has_picture       = false;
        $has_external_ref  = false;
        $has_access        = false;

        foreach ($rows as $row) {

            if ($row->Field == "show_email") {
                $has_show_email = true;
            }

            if ($row->Field == "description_3") {
                $has_description_3 = true;
            }

            if ($row->Field == "direct_page") {
                $has_direct_field = true;
            }

            if ($row->Field == "file_url") {
                $has_file_url = true;
            }

            if ($row->Field == "file_url") {
                $has_file_url = true;
            }

            if ($row->Field == 'parent_id') {
                $has_parent_id = true;
            }

            if ($row->Field == 'cms_version') {
                $has_cms_version = true;
            }

            if ($row->Field == 'picture') {
                $has_picture = true;
            }

            if ($row->Field == 'external_ref') {
                $has_external_ref = true;
            }

            if ($row->Field == 'access') {
                $has_access = true;
            }
        }

        if ($has_show_email && !$has_description_3) {
            $db_version = "1.0.1";
        }

        if ($has_show_email && $has_description_3) {
            $db_version = "1.0.2";
        }

        if ($has_direct_field) {
            $db_version = "1.0.3";
        }

        if ($db_version == "1.0.0") {
            echo("<div>Migrate database from version 1.0.0 to 1.0.3</div>");
            $db->setQuery(file_get_contents(JPATH_ADMINISTRATOR.DS."components".DS."com_osdownloads".DS."sql".DS."update_1.0.0_1.0.3.mysql.utf8.sql"));
            $db->query();
        }
        if ($db_version == "1.0.1") {
            echo("<div>Migrate database from version 1.0.1 to 1.0.3</div>");
            $db->setQuery(file_get_contents(JPATH_ADMINISTRATOR.DS."components".DS."com_osdownloads".DS."sql".DS."update_1.0.1_1.0.3.mysql.utf8.sql"));
            $db->query();
        }

        if ($db_version == "1.0.2") {
            echo("<div>Migrate database from version 1.0.2 to 1.0.3</div>");
            $db->setQuery(file_get_contents(JPATH_ADMINISTRATOR.DS."components".DS."com_osdownloads".DS."sql".DS."update_1.0.2_1.0.3.mysql.utf8.sql"));
            $db->query();
        }
        if (!$has_description_3) {
            // Fix database if upgrade from version 1.0.2 (new install). If upgrade from 1.0.x to 1.0.2 it still correct
            $db->setQuery("SHOW COLUMNS FROM #__osdownloads_documents");
            $rows = $db->loadObjectList();
            $has_description_3 = false;
            foreach ($rows as $row) {
                if ($row->Field == "description_3") {
                    $has_description_3 = true;
                }
            }
            if (!$has_description_3) {
                echo("<div>Apply patch for database version 1.0.2</div>");
                $db->setQuery("ALTER TABLE `#__osdownloads_documents` ADD `description_3` TEXT NOT NULL AFTER `description_2`");
                $db->query();
            }
        }

        if (!$has_file_url) {
            echo("<div>Migrate database for remote files</div>");
            $sql = file_get_contents(JPATH_ADMINISTRATOR."/components/com_osdownloads/sql/updates/mysql/1.0.17.sql");
            $db->setQuery($sql);
            $db->query();
        }

        if (!$has_external_ref) {
            $db->setQuery('ALTER TABLE `#__osdownloads_documents` ADD COLUMN `external_ref` VARCHAR(100);');
            $db->execute();
        }

        if (!$has_access) {
            $db->setQuery('ALTER TABLE `#__osdownloads_documents` ADD COLUMN `access` INT(11) NOT NULL DEFAULT 1;');
            $db->execute();
        }

        // Remove old columns
        if ($has_parent_id) {
            $db->setQuery('ALTER TABLE `#__osdownloads_documents` DROP COLUMN `parent_id`');
            $db->execute();
        }

        if ($has_cms_version) {
            $db->setQuery('ALTER TABLE `#__osdownloads_documents` DROP COLUMN `cms_version`');
            $db->execute();
        }

        if ($has_picture) {
            $db->setQuery('ALTER TABLE `#__osdownloads_documents` DROP COLUMN `picture`');
            $db->execute();
        }

        // Remove the old pkg_osdownloads, if existent
        $query = 'DELETE FROM `#__extensions` WHERE `type`="package" AND `element`="pkg_osdownloads"';
        $db->setQuery($query);
        $db->execute();

        return true;
    }
}
