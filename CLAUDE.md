# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Overview

OSDownloads (Free) is a Joomla component (`com_osdownloads`) for managing downloads, optionally gated behind an email address (which can be pushed to Mailchimp) and/or a terms agreement. It ships as a package with a content plugin (`plg_content_osdownloads`) and a module (`mod_osdownloadsfiles`). Targets Joomla 3.10.4+ through 6 and PHP 8.1+ (`src/osdownloads.xml`, `composer.json`).

There is a separate **OSDownloads-Pro** repository that builds on top of this one. Code here must keep working when Pro classes are present and when they are absent.

## Build

### Tests and coding standard
- Unit tests use Codeception: run `composer test` from the repo root (it installs `tests/vendor` first). Test tooling has its own `tests/composer.json` and gitignored `tests/vendor`, so it never touches the shipped `src/admin/vendor`. `codeception.yml` is at the repo root. `tests/composer.json` autoloads `Alledia\OSDownloads\` from `src/admin/library`, and `tests/bootstrap.php` defines `_JEXEC`, `JPATH_SITE` (the fixture site in `tests/Support/Data/site`) and `OSDOWNLOADS_LIBRARY`.
- Only code that runs without Joomla CMS classes or ShackFramework is unit tested; neither is installed in `tests/vendor`. Skip Joomla-coupled constructors with `ReflectionClass::newInstanceWithoutConstructor()`, or use a fixture subclass that presets state (see `ExposedManager`). Use `Codeception\Attribute\DataProvider`, because Codeception ignores PHPUnit's attribute. Put fixture classes in `tests/Support/Fixtures` (`Tests\Support\Fixtures`), one class per file, and data files in `tests/Support/Data`. Use `setUp()` rather than Codeception's `_before()`.
- Coding standard is stock PSR-12, checked by PHP_CodeSniffer from `tests/vendor`. `composer lint` reports and `composer lint:fix` runs phpcbf. Both cover `src` and `tests` and skip vendor and generated folders. There is no ruleset file and no CI.
- Updating test dependencies: run `composer --working-dir=tests update`, never root `composer update` (that rewrites the shipped `src/admin/vendor`). Keep `config.platform.php` (8.2.0) in `tests/composer.json`, and commit `tests/composer.lock`.

### Packaging
Packages are built with Phing via the external **ShackBuilder** project:

- Copy `build.dist.properties` to `build.properties` (gitignored) and set `builder.path` plus the `project.Shack*.path` entries pointing at local checkouts of ShackInstaller, ShackDefaultFiles, ShackFramework and ShackExtensionSupport.
- `phing` (default target `build`) — assembles the installable zip into `packages/` (gitignored). Set `builder.phplint=1` to lint all PHP during the build.
- The builder also pulls in the related extensions listed under `<alledia><relatedExtensions>` in `src/osdownloads.xml` and minifies `media/js/jquery.osdownloads.js` and `jquery.reveal.js` into `jquery.osdownloads.bundle(.min).js`. The bundle files are committed, so rebuild them whenever any of those source JS files change.
- jQuery Validation is not bundled. It comes from ShackFramework via `HTMLHelper::_('alledia.jqueryValidate')`, which must be called before the bundle is loaded.
- Load the download scripts with `Free\Joomla\Component\Site::loadScripts()` from view/module classes, not from templates, so template overrides can't drop them.

Composer dependencies (Mailchimp SDK, Guzzle, etc.) install into `src/admin/vendor`, and that directory is **committed**. After `composer update`, commit the vendor changes too.

Releases: bump `<version>` and `<creationDate>` in `src/osdownloads.xml` (see the "Release vX.Y.Z" commits).

## Architecture

### Bootstrapping
- `src/admin/include.php` is the shared bootstrap for every entry point (admin component, site component, plugin, module, installer). It requires the **ShackFramework** library (`libraries/allediaframework`, the `Alledia\Framework\*` namespace, installed separately) and bails out if it's missing. It then defines the `OSDOWNLOADS_*` path constants, loads `admin/vendor/autoload.php`, and registers the extension library autoloader.
- `src/admin/library/` is autoloaded as the `Alledia\OSDownloads\` namespace. Everything under `library/Free/` is `Alledia\OSDownloads\Free\…`. Pro provides a parallel `Alledia\OSDownloads\Pro\…` tree.
- `Alledia\OSDownloads\Factory::getPimpleContainer()` builds a Pimple DI container from `Free\Services` or `Pro\Services`, depending on `Factory::getExtension()->isPro()`. Services include `app`, `db`, `helperRoute`, `helperSEF`, `helperView` and `mailingLists`.

### Free/Pro class aliasing
The legacy Joomla MVC files (`admin/models/*.php`, `admin/controllers/*.php`, `site/views/*/view.html.php`, the plugin and the module) are thin shells. They `class_alias` either the `Pro\…` or the `Free\…` implementation to a neutral name (e.g. `Alledia\OSDownloads\Joomla\Model\Emails`) and extend that. Put real logic in `library/Free/…` so Pro can subclass or override it. Don't put it in the legacy-named classes (`OsdownloadsModelEmails`, `PlgContentOsdownloads`, …).

### Site download flow
`library/Free/Joomla/Controller/Site.php` drives the download workflow:
1. Task `routedownload` loads the item, checks `require_agree`, then validates and stores the email (`require_user_email`: 1 = required, 2 = optional) via the site `Email` model. It renders the `item` view with the layout `thankyou` or `error_*`.
2. When all requirements pass, `authorizeDownload()` writes a session key that expires after `DOWNLOAD_AUTHORIZATION_TTL` (300s).
3. Task `download` renders `site/views/download`, which must check `SiteController::isDownloadAuthorized()` before streaming the file (local files live in `media/com_osdownloads/files`, protected by `.htaccess`).

SEF routing lives in `site/router.php` together with `library/Free/Helper/SEF.php` and `Route.php`.

### Mailing lists
`library/MailingLists/Manager.php` discovers plugins by scanning `library/Free/MailingList/` (and the Pro equivalent, where Pro files with the same name win) for paired `Name.php` + `Name.xml` files:
- The PHP class (extends `MailingLists\AbstractClient`) is attached as a Table observer, so it fires when emails are stored.
- The XML holds form fragments (`<form group="…" order="…"><fields name="config|category|file">`). These get injected into any form that has a `<fields name="mailinglist"/>` target: component options, category edit, file edit.

To add a provider, drop in a new PHP/XML pair. No registration is needed.

### ShackFramework (`Alledia\Framework`)
Lives in the separate AllediaFramework repository (`src/Framework/…`, with its own CLAUDE.md), whose location is set by `project.ShackFramework.path` in `build.properties`. Many behaviors here come from that framework and can't be seen in this repo:
- **Extension/license:** `Factory::getExtension()` returns a `Licensed` object built from the installed manifest. `isPro()` reads `<alledia><license>`, and `loadLibrary()` registers `Alledia\OSDownloads` → `admin/library`. Pro installs over the same component, so its code lands in `admin/library/Pro` (`getProLibraryPath()`).
- **Site dispatch:** `Free\Joomla\Component\Site` extends `AbstractComponent`. Its `init()` requires `site/controller.php` and calls `Alledia\OSDownloads\{Free|Pro}\Joomla\Controller\Site::getInstance()`, chosen by license, then runs `task`. `getModel()` and `getTable()` resolve `Alledia\OSDownloads\{Free|Pro}\Joomla\Model|Table\<Type>` first and fall back to the legacy `OSDownloadsModel<Type>` / `OSDownloadsTable<Type>` classes. The admin entry point (`admin/osdownloads.php`) uses plain `BaseController::getInstance('osdownloads')` instead.
- **MVC bases:** `Controller\AbstractBase`, `Model\AbstractListModel` / `AbstractAdminModel`, and `View\Admin\AbstractList` / `AbstractForm` and `View\Site\AbstractBase` (via `TraitAllediaView`).
- **Admin layout selection:** `View\Admin\AbstractBase::setLayout()` → `TraitAllediaView::getVersionedLayoutName()`. On Joomla 3 it uses `<layout>.j3.php` when that file exists. `AbstractList::display()` switches to the `emptystate` layout on J4+ when there are no items and the model's `getIsEmptyState()` is true. That is why admin templates come in `default.php` / `default.j3.php` / `emptystate.php` sets. Keep the J3 twin in sync when changing a template. Site views don't use this mechanism.
- Form fields that use the framework's `TraitLayouts` field shims look for `<layout>_j3.php` (underscore) on J3.
- The framework's convention is to detect capabilities (`class_exists`, `is_callable`) rather than check `Version::MAJOR_VERSION`.
- Public and protected framework APIs are shared by every Joomlashack extension. Don't change the framework to fix something specific to OSDownloads.

### Installer and schema
- `src/script.installer.php` → `library/Free/Installer/AbstractScript.php` (extends ShackInstaller's `Alledia\Installer\AbstractScript`). On install or update it makes sure a default category exists and clears leftover Pro data. On update it also migrates layouts and fixes up the schema.
- SQL: `admin/sql/install/mysql/install.sql` for fresh installs, plus versioned files in `admin/sql/updates/mysql/<version>.sql`. A schema change needs both. `osdownloads.mwb` is the MySQL Workbench model.
- Files and folders removed between versions must be listed under `<alledia><obsolete>` in `src/osdownloads.xml` so upgrades delete them.

## Conventions
- Every PHP file starts with the standard GPL header and `defined('_JEXEC') or die();`. That guard is wrapped in `// phpcs:disable PSR1.Files.SideEffects` / `enable` comments in namespaced files (PSR-12 style).
- Language strings use the `COM_OSDOWNLOADS_*` prefix in `admin/language` and `site/language` (multiple locales; en-GB is the source).
- `_scratch/` is untracked local work. Ignore it.
