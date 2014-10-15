[![OS Training](http://www.ostraining.com/templates/ostraining/images/logo.png)](http://www.ostraining.com)
OSDownload
============

## About

OSDownloads is an extension to help you manage your downloads. It allows you to easily provide downloads in exchange for emails, which can in turn be automatically imported in MailChimp upon download.

## OSDownloads Plugins

### Events

I'm sure this still can be improved but here are some initial events:

* onBeforeOSDownloadsSaveFile($documentInstance, $isNew)
* onAfterOSDownloadsSaveFile($storedWithSuccess, $documentInstance)
* onBeforeOSDownloadsSaveEmail($emailInstance)
* onAfterOSDownloadsSaveEmail($storedWithSuccess, $emailInstance)
* onGetOSDownloadsExternalDownloadLink($documentInstance)

## Requirements

Joomla 2.5.x or 3.x

## License

[GNU General Public License v3](http://www.gnu.org/copyleft/gpl.html)
