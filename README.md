[![Alledia](https://www.alledia.com/images/logo_circle_small.png)](https://www.alledia.com)

OSDownloads
============

## About

OSDownloads is an extension to help you manage your downloads. It allows you to easily provide downloads in exchange for emails, which can in turn be automatically imported in MailChimp upon download.

## OSDownloads Plugins

### Events

* onOSDownloadsBeforeSaveFile($documentInstance, $isNew)
* onOSDownloadsAfterSaveFile($storedWithSuccess, $documentInstance)
* onOSDownloadsBeforeDeleteFile($documentInstance, $pk)
* onOSDownloadsAfterDeleteFile($deletedWithSuccess, $id, $pk);

* onOSDownloadsBeforeSaveEmail($emailInstance)
* onOSDownloadsAfterSaveEmail($storedWithSuccess, $emailInstance)
* onOSDownloadsBeforeDeleteEmail($emailInstance, $pk)
* onOSDownloadsAfterDeleteEmail($deletedWithSuccess, $id, $pk);

* onOSDownloadsGetExternalDownloadLink($documentInstance)

## Requirements

Joomla 2.5.x or 3.x

## License

[GNU General Public License v3](http://www.gnu.org/copyleft/gpl.html)
