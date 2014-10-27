<?php
/**
 * @package   AllediaFramework
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2014 Alledia.com, All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace Alledia\Framework;

use Alledia\Framework\Extension;

defined('_JEXEC') or die();

abstract class Helper
{
    /**
     * Return an array of Alledia extensions
     *
     * @param  string $license
     * @return array
     */
    public static function getAllediaExtensions($license = '')
    {
        // Get the extensions ids
        $db = \JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select($db->quoteName('extension_id'))
            ->select($db->quoteName('type'))
            ->select($db->quoteName('element'))
            ->select($db->quoteName('folder'))
            ->from('#__extensions')
            ->where($db->quoteName('custom_data') . " LIKE '%\"author\":\"Alledia\"%'")
            ->group($db->quoteName('extension_id'));

        $db->setQuery($query);
        $rows = $db->loadObjectList();

        $extensions = array();
        foreach ($rows as $row) {
            $extension = new Extension($row->element, $row->type, $row->folder);

            if (!empty($license)) {
                if ($license === 'pro' && ! $extension->isPro()) {
                    continue;
                } elseif ($license === 'free' && $extension->isPro()) {
                    continue;
                }
            }

            $extensions[$row->extension_id] = $extension;
        }

        return $extensions;
    }
}
