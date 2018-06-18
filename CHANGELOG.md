# Changelog

All notable changes to `shuttle` will be documented in this file.

Updates should follow the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## [0.6] - 2018-06-18
### Added
- New `DoctrineQuerySource` to use Doctrine DBAL query builder as a source

## [0.5.1] - 2018-06-16
### Fixed
- Recorder should return NULL if record not found
- `MigratorInterface::getMigratedSourceIdIterator()` should return a `SourceIdIterator` instance
- Bugfixes to CLI commands 
- Print detailed exception if `--abort-on-failure` specified

## [0.5] - 2018-06-16
### Added
- `SourceIdIterator` class to clarify API for iterating source IDs

### Changed
- `SourceInterface` and `MigratorInterface::getSourceIdIterator` now require returning `SourceIdIterator` instance
- `ArraySource` and `CallbackSource` now generate a list of source IDs each time `getSourceIdIterator` is called, not
  just when they are constructed.

## [0.4] - 2018-06-15
### Added
- Added `CallbackSource` for callback item sources (loading source records deferred until source is used) 

### Changed
- `ArraySource` now extends `CallbackSource` and defers iterating over source record until source is used

## [0.3] - 2018-06-14

### Added
- Added the `SourceItem` object to help provide some structure to required source item requirements (Id and data)
- The `DoctrineDestination` pre-installed destination to support migrating to Doctrine ORM, ODM, etc.
- New method `MigratorInterface::getSourceItem()` to make migrations API more granular
- New method `MigratorInterface::prepare()` to make migrations API more granular
- New method `MigratorInterface::prepare::persist()` to make migrations API more granular
- New events: `READ_SOURCE_RECORD` and `PRE_PERSIST` 
- `ArrayRecorder`, `ArraySource` and `ArrayDestination`, mostly for testing; but these can be extended to other
  production use-cases.

### Changed
- BREAKING: Refactored the main API to be a bit more sane.  Lots of breaking changes.
- BREAKING: Refactored events
- Removed array type hint from `DestinationInterface` to support more diverse destinations 
- Code style and comment fixes

### Removed
- `DestinationInterface::getItem()`.  There is no need to retrieve items from the destination
- The `MigratorFactory` class (possibly to be refactored later)
- The `Migrator::migrate()` function.  The public interface for a migrator has become more granular (see 'added' above)
- The `MigratorInterface::getSource()` and `MigratorInterface::getDestination()` methods.  This will allow creating
  fully-functional migrators without using source or destination classes, if one chooses to do so.

## [0.2] - 2018-06-01

### Added
- PHP CodeSniffer
- Support for modern versions of Symfony
- Suggest `symfony/console` in Composer
- New class: `Shuttle\Helper\ConsoleCommandBuidler::build` (replaces `Shuttle::buildConsoleCommands()`)
- A proper license file
- Other development files (`.editorconfig`, `.travisci`, etc)
- Tests now produce code coverage by default

### Changed
- This library now requires PHP7.1 or newer; all APIs were updated
- Move some classes around and renamed some things
- No longer require `doctrine/dbal` (suggest it instead)
- No longer require `symfony/yaml` (suggest it instead)
- Updated PHPUnit to v7

### Fixed
- Removed all references to "Conveyor Belt" (my name is "Shuttle"!)
- Ensure consistent vocabulary ('item' instead of 'record')
- Refactored a number of variable names that were unclear
- License headers on PHP files
- A lot of PSR-2 compatibility errors

### Removed
- Removed `Shuttle::buildConsoleCommands()`; use `Shuttle\Helper\ConsoleCommandBuidler::build()` instead
- Defunct configuration file (no longer part of this library)
- Removed dependency on, and use of, TaskTracker library in Console Commands

## [0.1] - 2018-06-01

### Added
- Everything - Initial pre-release (lots of cleanup needed)
