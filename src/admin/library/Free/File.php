<?php
/**
 * @package   com_osdownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace Alledia\OSDownloads\Free;

defined('_JEXEC') or die();

class File
{
    /**
     * @var array[]
     */
    protected static $headers = array();

    /**
     * Return's the content type based on the file name
     * Can accept external urls to determine from URL
     *
     * @param  string $filename The file filename
     *
     * @return string           The content type
     */
    public static function getContentTypeFromFileName($filename)
    {
        if (is_file($filename) && function_exists('mime_content_type')) {
            return mime_content_type($filename);
        }

        if (preg_match('|\.([a-z0-9]{2,4})$|i', $filename, $fileSuffix)) {
            switch (strtolower($fileSuffix[1])) {
                case 'js':
                    return 'application/x-javascript';

                case 'json':
                    return 'application/json';

                case 'jpg':
                case 'jpeg':
                case 'jpe':
                    return 'image/jpg';

                case 'png':
                case 'gif':
                case 'bmp':
                case 'tiff':
                    return 'image/' . strtolower($fileSuffix[1]);

                case 'css':
                    return 'text/css';

                case 'xml':
                    return 'application/xml';

                case 'doc':
                case 'docx':
                    return 'application/msword';

                case 'xls':
                case 'xlt':
                case 'xlm':
                case 'xld':
                case 'xla':
                case 'xlc':
                case 'xlw':
                case 'xll':
                    return 'application/vnd.ms-excel';

                case 'ppt':
                case 'pps':
                    return 'application/vnd.ms-powerpoint';

                case 'rtf':
                    return 'application/rtf';

                case 'pdf':
                    return 'application/pdf';

                case 'html':
                case 'htm':
                case 'php':
                    return 'text/html';

                case 'txt':
                    return 'text/plain';

                case 'mpeg':
                case 'mpg':
                case 'mpe':
                    return 'video/mpeg';

                case 'mp3':
                    return 'audio/mpeg3';

                case 'wav':
                    return 'audio/wav';

                case 'aiff':
                case 'aif':
                    return 'audio/aiff';

                case 'avi':
                    return 'video/msvideo';

                case 'wmv':
                    return 'video/x-ms-wmv';

                case 'mov':
                    return 'video/quicktime';

                case 'zip':
                    return 'application/zip';

                case 'tar':
                    return 'application/x-tar';

                case 'swf':
                    return 'application/x-shockwave-flash';
            }
        }

        // Exhausted all possibilities, do our best!
        $headers = static::getHeaders($filename);
        if (!empty($headers['Content-Type'])) {
            return $headers['Content-Type'];
        }

        return 'application/octet-stream';
    }

    /**
     * Get header info for a url. Only http(s) urls will work
     *
     * @param string $url
     *
     * @return string[]
     */
    public static function getHeaders($url)
    {
        $key = md5($url);
        if (!isset(static::$headers[$key])) {
            static::$headers[$key] = array();
            if (filter_var($url, FILTER_VALIDATE_URL) !== false) {
                if (preg_match('#^(https?)://#i', $url, $schema)) {
                    stream_context_set_default(
                        array(
                            $schema[1] => array(
                                'method' => 'HEAD'
                            )
                        )
                    );
                    static::$headers[$key] = get_headers($url, 1) ?: array();
                }
            }
        }

        return static::$headers[$key];
    }
}
