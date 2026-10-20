# Developer Guide

This guide covers building OSDownloads (Free) from source and understanding how the
codebase fits together. It targets Joomla 3.10+ through 6 and PHP 8.1+ (see
`src/osdownloads.xml` and `composer.json` for the authoritative requirements).

OSDownloads (Free) ships as a package containing the `com_osdownloads` component, the
`plg_content_osdownloads` content plugin, and the `mod_osdownloadsfiles` module.

A separate **OSDownloads-Pro** repository builds on top of this one. When changing code
here, make sure it keeps working both when Pro classes are present and when they are
absent.

## Tests and coding standard

Run these from the repo root. Each one installs the test tooling into `tests/vendor` first.

- `composer test` runs the [Codeception](https://codeception.com/) unit tests in
  `tests/Unit`.
- `composer lint` checks `src` and `tests` against PSR-12 with PHP_CodeSniffer.
- `composer lint:fix` automatically fixes the violations that phpcbf can correct.

Test dependencies live in `tests/composer.json`, separate from the shipped
`src/admin/vendor`. To update them, run `composer --working-dir=tests update`, never a
root `composer update`, and commit `tests/composer.lock`.

The unit tests cover only code that runs without Joomla or ShackFramework, since
neither is installed for the tests. `tests/bootstrap.php` defines `_JEXEC` and points
`JPATH_SITE` at a small fixture site in `tests/Support/Data/site`. Fixture classes go in
`tests/Support/Fixtures`, and data files go in `tests/Support/Data`.

## Building the package

Packages are assembled with [Phing](https://www.phing.info/) via the external
**ShackBuilder** project.

1. Check out **ShackBuilder**, **ShackInstaller**, **ShackDefaultFiles**,
   **ShackFramework**, and **ShackExtensionSupport** locally.
2. Copy `build.dist.properties` to `build.properties` (gitignored) and set `builder.path`
   to your ShackBuilder checkout, plus the matching `project.ShackInstaller.path`,
   `project.ShackDefaultFiles.path`, `project.ShackFramework.path`, and
   `project.ShackExtensionSupport.path` entries.
3. Run `phing` (default target `build`) to assemble the installable zip into `packages/`
   (gitignored). Set `builder.phplint=1` in `build.properties` to lint all PHP during the
   build.

The builder also pulls in the related extensions listed under
`<alledia><relatedExtensions>` in `src/osdownloads.xml`, and minifies
`media/js/jquery.osdownloads.js` and `jquery.reveal.js` into
`jquery.osdownloads.bundle(.min).js`. Those bundle files are committed, so rebuild them
whenever any of the source JS files change.

jQuery Validation isn't bundled. It comes from ShackFramework and is loaded with
`HTMLHelper::_('alledia.jqueryValidate')`, which must be called before the bundle is
loaded.

`Free\Joomla\Component\Site::loadScripts()` loads jQuery, jQuery Validation and the
bundle in the right order. Call it from view or module classes rather than from
templates, so sites that override a template don't lose the scripts.

### Dependencies

Composer dependencies (Mailchimp SDK, Guzzle, etc.) install into `src/admin/vendor`,
and that directory is committed. After running `composer update`, commit the resulting
vendor changes too.

### Releases

Run `phing build-new` to bump `<version>` and `<creationDate>` in `src/osdownloads.xml`
(see the "Release vX.Y.Z" commits for the pattern).

## Architecture

### Bootstrapping

`src/admin/include.php` is the shared bootstrap for every entry point (admin component,
site component, plugin, module, installer). It requires the **ShackFramework** library
(the `Alledia\Framework\*` namespace, installed separately) and bails out if it's
missing. It then defines the `OSDOWNLOADS_*` path constants, loads
`admin/vendor/autoload.php`, and registers the extension library autoloader.

`src/admin/library/` is autoloaded as the `Alledia\OSDownloads\` namespace. Everything
under `library/Free/` is `Alledia\OSDownloads\Free\…`. Pro provides a parallel
`Alledia\OSDownloads\Pro\…` tree.

`Alledia\OSDownloads\Factory::getPimpleContainer()` builds a Pimple DI container from
`Free\Services` or `Pro\Services`, depending on `Factory::getExtension()->isPro()`.
Services include `app`, `db`, `helperRoute`, `helperSEF`, `helperView`, and
`mailingLists`.

### Free/Pro class aliasing

The legacy Joomla MVC files (`admin/models/*.php`, `admin/controllers/*.php`,
`site/views/*/view.html.php`, the plugin, and the module) are thin shells. They use
`class_alias` to point either the `Pro\…` or the `Free\…` implementation at a neutral
name (e.g. `Alledia\OSDownloads\Joomla\Model\Emails`) and extend that.

Real logic belongs in `library/Free/…` so Pro can subclass or override it — not in the
legacy-named classes (`OsdownloadsModelEmails`, `PlgContentOsdownloads`, …).

### Site download flow

`library/Free/Joomla/Controller/Site.php` drives the download workflow:

1. Task `routedownload` loads the item, checks `require_agree`, then validates and
   stores the email (`require_user_email`: 1 = required, 2 = optional) via the site
   `Email` model. It renders the `item` view with the layout `thankyou` or `error_*`.
2. When all requirements pass, `authorizeDownload()` writes a session key that expires
   after `DOWNLOAD_AUTHORIZATION_TTL` (300 seconds).
3. Task `download` renders `site/views/download`, which must check
   `SiteController::isDownloadAuthorized()` before streaming the file (local files live
   in `media/com_osdownloads/files`, protected by `.htaccess`).

SEF routing lives in `site/router.php`, together with `library/Free/Helper/SEF.php` and
`Route.php`.

### Mailing lists

`library/MailingLists/Manager.php` discovers plugins by scanning
`library/Free/MailingList/` (and the Pro equivalent, where Pro files of the same name
win) for paired `Name.php` + `Name.xml` files:

- The PHP class (extends `MailingLists\AbstractClient`) is attached as a Table observer,
  so it fires when emails are stored.
- The XML holds form fragments (`<form group="…" order="…"><fields name="config|category|file">`).
  These get injected into any form that has a `<fields name="mailinglist"/>` target:
  component options, category edit, file edit.

To add a provider, drop in a new PHP/XML pair — no registration is needed.

### ShackFramework (`Alledia\Framework`)

Lives in the separate **AllediaFramework** repository (`src/Framework/…`, with its own
developer docs), located via the `project.ShackFramework.path` build property. Many
behaviors described below come from that framework and aren't visible in this repo:

- **Extension/license:** `Factory::getExtension()` returns a `Licensed` object built
  from the installed manifest. `isPro()` reads `<alledia><license>`, and
  `loadLibrary()` registers `Alledia\OSDownloads` → `admin/library`. Pro installs over
  the same component, so its code lands in `admin/library/Pro`
  (`getProLibraryPath()`).
- **Site dispatch:** `Free\Joomla\Component\Site` extends `AbstractComponent`. Its
  `init()` requires `site/controller.php` and calls
  `Alledia\OSDownloads\{Free|Pro}\Joomla\Controller\Site::getInstance()`, chosen by
  license, then runs `task`. `getModel()` and `getTable()` resolve
  `Alledia\OSDownloads\{Free|Pro}\Joomla\Model|Table\<Type>` first and fall back to the
  legacy `OSDownloadsModel<Type>` / `OSDownloadsTable<Type>` classes. The admin entry
  point (`admin/osdownloads.php`) uses plain `BaseController::getInstance('osdownloads')`
  instead.
- **MVC bases:** `Controller\AbstractBase`, `Model\AbstractListModel` /
  `AbstractAdminModel`, and `View\Admin\AbstractList` / `AbstractForm` and
  `View\Site\AbstractBase` (via `TraitAllediaView`).
- **Admin layout selection:** `View\Admin\AbstractBase::setLayout()` →
  `TraitAllediaView::getVersionedLayoutName()`. On Joomla 3 it uses `<layout>.j3.php`
  when that file exists. `AbstractList::display()` switches to the `emptystate` layout
  on J4+ when there are no items and the model's `getIsEmptyState()` is true. That's why
  admin templates come in `default.php` / `default.j3.php` / `emptystate.php` sets.
  Keep the J3 twin in sync when changing a template. Site views don't use this
  mechanism.
- Form fields that use the framework's `TraitLayouts` field shims look for
  `<layout>_j3.php` (underscore) on J3.
- The framework's convention is to detect capabilities (`class_exists`, `is_callable`)
  rather than check `Version::MAJOR_VERSION`.
- Public and protected framework APIs are shared by every Joomlashack extension. Don't
  change the framework to fix something specific to OSDownloads.

### Installer and schema

- `src/script.installer.php` → `library/Free/Installer/AbstractScript.php` (extends
  ShackInstaller's `Alledia\Installer\AbstractScript`). On install or update it makes
  sure a default category exists and clears leftover Pro data. On update it also
  migrates layouts and fixes up the schema.
- SQL: `admin/sql/install/mysql/install.sql` for fresh installs, plus versioned files in
  `admin/sql/updates/mysql/<version>.sql`. A schema change needs both.
  `osdownloads.mwb` is the MySQL Workbench model.
- Files and folders removed between versions must be listed under
  `<alledia><obsolete>` in `src/osdownloads.xml` so upgrades delete them.

## Conventions

- Every PHP file starts with the standard GPL header and `defined('_JEXEC') or die();`.
  That guard is wrapped in `// phpcs:disable PSR1.Files.SideEffects` /
  `// phpcs:enable` comments in namespaced files (PSR-12 style).
- Language strings use the `COM_OSDOWNLOADS_*` prefix in `admin/language` and
  `site/language` (multiple locales; en-GB is the source).
- `_scratch/` is untracked local work; it's ignored by git.
