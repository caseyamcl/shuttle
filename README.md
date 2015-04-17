# Shuttle

A Data Migration Tool - Get Data from Here to There!

## Overview

Shuttle is a PHP library and command-line tool to facilitate
moving data from one place (a database, web service, spreadsheet, text file,
etc) to another.  This tool was developed to provide a general-purpose
implementation of the concept behind the [Drupal Migrate Module](https://www.drupal.org/project/migrate).

In short, this library allows you to migrate records from a given source,
transform it as necessary, and dump it into a destination.  Shuttle will
keep track of record IDs as they are migrated and allow you to revert and re-migrate
as many times as you wish.

## Basic Usage

