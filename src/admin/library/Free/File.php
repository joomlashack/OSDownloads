<?php
/**
 * @package   com_osdownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2018 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace Alledia\OSDownloads\Free;

use JEventDispatcher;
use JPluginHelper;
use JRoute;
use Alledia\OSDownloads\Free\Factory;

defined('_JEXEC') or die();

class File
{
    /**
     * Internal list of known mime types
     *
     * @var string[]
     */
    protected static $mimeTypes = array(
        'aif'  => 'audio/aiff',
        'aiff' => 'audio/aiff',
        'avi'  => 'video/msvideo',
        'bmp'  => 'image/bmp',
        'css'  => 'text/css',
        'doc'  => 'application/msword',
        'docx' => 'application/msword',
        'gif'  => 'image/gif',
        'htm'  => 'text/html',
        'html' => 'text/html',
        'jpe'  => 'image/jpg',
        'jpeg' => 'image/jpg',
        'jpg'  => 'image/jpg',
        'js'   => 'application/x-javascript',
        'json' => 'application/json',
        'mov'  => 'video/quicktime',
        'mp3'  => 'audio/mpeg3',
        'mpe'  => 'video/mpeg',
        'mpeg' => 'video/mpeg',
        'mpg'  => 'video/mpeg',
        'pdf'  => 'application/pdf',
        'php'  => 'text/html',
        'png'  => 'image/png',
        'pps'  => 'application/vnd.ms-excel',
        'ppt'  => 'application/vnd.ms-excel',
        'rtf'  => 'application/rtf',
        'swf'  => 'application/x-shockwave-flash',
        'tar'  => 'application/x-tar',
        'tiff' => 'image/tiff',
        'txt'  => 'text/plain',
        'wav'  => 'audio/wav',
        'wmv'  => 'video/x-ms-wmv',
        'xla'  => 'application/vnd.ms-excel',
        'xlc'  => 'application/vnd.ms-excel',
        'xld'  => 'application/vnd.ms-excel',
        'xll'  => 'application/vnd.ms-excel',
        'xlm'  => 'application/vnd.ms-excel',
        'xls'  => 'application/vnd.ms-excel',
        'xlt'  => 'application/vnd.ms-excel',
        'xlw'  => 'application/vnd.ms-excel',
        'xml'  => 'application/xml',
        'zip'  => 'application/zip'
    );

    /**
     * @var array[]
     */
    protected static $headers = array();

    /**
     * Return's the content type based on the file name
     * Can accept external urls to determine from URL
     *
     * @param  string $path Filename, full path or url
     *
     * @return string The content type
     */
    public static function getContentTypeFromFileName($path)
    {
        // Try from filename/extension
        if ($mimeType = static::getMimeType($path)) {
            return $mimeType;
        }

        // Either not recognized or no extension
        // if a url, maybe we can get it from the http headers
        $headers = static::getHeaders($path);
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

                    // Handle possibility of redirects
                    if ($headers = @get_headers($url, 1)) {
                        foreach ($headers as $property => $value) {
                            if (!is_int($property)) {
                                static::$headers[$key][$property] = is_array($value) ? array_pop($value) : $value;

                            } elseif (preg_match('#HTTP/[0-9\.]+\s+(\d+)#', $value, $code)) {
                                static::$headers[$key]['http_code'] = (int)$code[1];
                            }
                        }
                    }
                }
            }
        }

        return static::$headers[$key];
    }

    /**
     * Make effort to determine from filename
     *
     * @param string $filename
     *
     * @return null|string
     */
    public static function getMimeType($filename)
    {
        // Existing local file
        if (is_file($filename) && function_exists('mime_content_type')) {
            return mime_content_type($filename);
        }

        // May not be a local file, try file extension
        $pathinfo = pathinfo($filename);
        if (!empty($pathinfo['extension'])) {
            $extension = $pathinfo['extension'];
            if (!empty(static::$mimeTypes[$extension])) {
                return static::$mimeTypes[$extension];
            }
        }

        // Unable to determine from file name
        return null;
    }

    /**
     * Returns the download url for the file.
     *
     * @param int $fileId
     *
     * @return string
     */
    public static function getDownloadUrl($fileId)
    {
        $container = Factory::getContainer();

        return JRoute::_($container->helperRoute->getFileDownloadRoute($fileId));
    }
}
