<?php
/**
 * @package   OSSystem
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2014 Alledia.com, All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die();

jimport('joomla.filesystem.file');

/**
 * Helper class
 */
abstract class OSSystemHelper
{
    /**
     * This method inspect Joomla's CA Root Certificates file looking
     * for our certificate authority G2 registers. If not found,
     * it will inject it.
     *
     * @return void
     */
    public static function checkAndUpdateCARootFile()
    {
        // Get the original Joomla file
        $joomlaCACertificatesPath = JPATH_SITE . '/libraries/joomla/http/transport/cacert.pem';
        if (file_exists($joomlaCACertificatesPath)) {
            $contentUpdated = false;

            // Get Joomla certificates
            $joomlaCACertificates = JFile::read($joomlaCACertificatesPath);
            // Get Joomla certificate in a big block, without line breaks to make easier
            // identify each certificate individually and completely.
            $joomlaCACertificatesBlock = str_replace("\n", '', $joomlaCACertificates);

            // Get our certificates
            $allediaCACertificates = JFile::read(__DIR__ . '/bundle.g2.crt');
            $allediaCACertificates = explode('-----BEGIN CERTIFICATE-----', $allediaCACertificates);

            foreach ($allediaCACertificates as $certificate) {
                if (!empty($certificate)) {
                    // Get the certificate block, without line breaks, to make a better search
                    $certificateBlock = str_replace("\n", '', $certificate);
                    $certificateBlock = str_replace('-----BEGIN CERTIFICATE-----', '', $certificateBlock);
                    $certificateBlock = str_replace('-----END CERTIFICATE-----', '', $certificateBlock);

                    // Check if the certificate is not in Joomla's file
                    if (substr_count($joomlaCACertificatesBlock, $certificateBlock) === 0) {
                        $timestamp = date('Y-m-d H:i:s');

                        // Restore the header
                        $certificate = trim("-----BEGIN CERTIFICATE-----" . $certificate);
                        $certificate = "Go Daddy Class 2 CA - Alledia\n=============================\n" . $certificate;
                        $certificate = "## Added by OSSystem for Alledia - "
                            . $timestamp . "\n" . $certificate;

                        // Append the certificate
                        $joomlaCACertificates .= "\n\n" . $certificate;

                        $contentUpdated = true;
                    }
                }
            }
            unset($joomlaCACertificatesBlock, $allediaCACertificates, $certificateBlock, $certificate);

            if ($contentUpdated) {
                // We need to update the file, so let's check if we have a backup and create if needed
                $backupFilePath = JPATH_SITE . '/libraries/joomla/http/transport/cacert.pem.ossystem-backup';
                if (!JFile::exists($backupFilePath)) {
                    $backupDirPath = dirname($joomlaCACertificatesPath);

                    // Copy the current permissions and try to add write permission
                    $fileInfo = stat($backupDirPath);

                    // Force write permission
                    chmod($backupDirPath, 0777);

                    JFile::copy($joomlaCACertificatesPath, $backupFilePath);

                    // Restore the original permissions
                    chmod($backupDirPath, $fileInfo['mode']);
                }

                // Copy the current permissions and try to add write permission
                $fileInfo = stat($joomlaCACertificatesPath);

                // Force write permission
                chmod($joomlaCACertificatesPath, 0666);

                JFile::write($joomlaCACertificatesPath, $joomlaCACertificates);

                // Restore the original permissions
                chmod($joomlaCACertificatesPath, $fileInfo['mode']);
            }
        }
    }
}
