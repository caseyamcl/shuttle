# Changelog

All notable changes to `shuttle` will be documented in this file.

Updates should follow the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

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
