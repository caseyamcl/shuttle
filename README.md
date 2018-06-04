# Shuttle

A Data Migration Tool - Get Data from Here to There!

## Overview

Shuttle is a PHP library to facilitate moving data from one place (a database, web service, spreadsheet, text file, etc)
to another.  This tool was developed to provide a general-purpose implementation of the concept behind the 
[Drupal Migrate Module](https://www.drupal.org/project/migrate).

This library allows you to migrate records from a given source, transform it as necessary, and dump it into a 
destination.  Shuttle will keep track of record IDs as they are migrated, and will allow you to revert and re-migrate 
your records as needed.

It provides a quick way to get started for 80% of use cases, but also provides advanced capabilities for more complex
workloads.

## Installation

Install via Composer:

    composer require caseyamcl/shuttle

## Quick concepts

To migrate data, you need four things:

* A **source**: Shuttle defines a `SourceInterface`.  You can use one of the built-in sources in the
  `Shuttle\MigrateSource` namespace or create your own:
    * `CsvSource` - Comma-separated values source (stream/resource or file path)
    * `DbTableSource` - Retrieve source items from a table in a database
    * `DbSource` - Use database queries to retrieve source items
    * `JsonSource` - Retrieve source items from a JSON string
    * `YamlSource` - Retrieve source items from a YAML string
* A **destination**: Shuttle defines a `DestinationInterface`.  You can use the built-in 
    `Shuttle\MigrateDestination\DbTableDestination` or create your own.
* An set of **items** in the source to migrate.  These are typically database rows, CSV rows, JSON records, or
  something similar.  In Shuttle, the data must have a unique identifier and be represented as an array.
* A **recorder**: This is the mechanism that keeps track of source items and destination items.  It maps source item 
  IDs and destination item IDs so that items are migrated only once and can be reverted.

## Migrate from one database to another

If you're migrating from one database to another, you can use the bundled `DbSource` and `DbDestination`



To migrate a set of data, you need three components:

* A Source, represented by a `MigrateSource` class
* A Destination, represented by a `MigrateDestination` class
* A Migrator, represented by a `Migrator` class

Shuttle includes several built-in source and destination classes.  The documentation is below.

To migrate data, you need to create a class that extends the Migrator class and override the constructor:

    use Shuttle\Service\Migrator\Migrator;
    
    class MyMigrator extends Migrator
    {
        public function __construct()
        {
            $slug        = 'mydata';
            $description = 'Migrate some Data';
            $source      = SomeSourceClass();
            $destination = SomeDestinationClass();
        
            parent::__construct($slug, $description, $source, $destination);
        }
    }
    
## Custom Mapping Logic

Override the `Migrator::prepare` method to provide custom logic during migration.

## Using the Console Commands

If your application uses Symfony Console component, you can add console commands to your app for listing migrators, 
migrating, and reverting records.
  
There are two ways to expose `migrate` and `revert` commands in your application.

1. Create a single command and let the user specify the migrator by its name as a CLI argument
2. Create a separate command for each migrator

### Creating a Single Command

### Creating a Separate Command for Each Migrator

## Tracking Migrations using Events

Shuttle uses the [Symfony Event Dispatcher](https://symfony.com/doc/current/components/event_dispatcher.html) library 
to dispatch events as records are migrated or reverted. You can create event listeners to report on progress or log 
these events if you wish.
