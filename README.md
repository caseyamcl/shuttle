# Shuttle

> This project is **ABANDONED**.  The author recommends [Porter](https://github.com/ScriptFUSION/Porter) as an alternative.

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

Key Features:

* Migrate, revert, and report on data from any source to any destination
* Included classes for common sources (CSV, database, Doctrine, YAML, etc.) and destinations (database, Doctrine)
* Calculate dependencies for migrations and ensure migrations occur in the correct order (e.g. 'post' items must be
  migrated before 'comment' items)
* Event system to hook into migration process for logging or other purposes
* Fully extensible; interface-driven approach
* Included (but optional) Symfony console commands
* 100% unit tested and PSR-2 compliant

## Installation

Install via Composer:

    composer require caseyamcl/shuttle

## Quick concepts

To migrate data, you need three things:

* A **source**: Shuttle defines a `SourceInterface`.  You can use one of the built-in sources in the
  `Shuttle\MigrateSource` namespace or create your own:
    * `CsvSource` - Comma-separated values source (stream/resource or file path)
    * `DbTableSource` - Retrieve source items from a table in a database
    * `DbSource` - Use database queries to retrieve source items
    * `JsonSource` - Retrieve source items from a JSON string
    * `YamlSource` - Retrieve source items from a YAML string
* A **destination**: Shuttle defines a `DestinationInterface`.  You can use the built-in 
    `Shuttle\MigrateDestination\DbTableDestination` or create your own.
* A set of **items** in the source to migrate.  These are typically database rows, CSV rows, JSON records, or
  something similar.  In Shuttle, the data must have a unique identifier and be represented as an array.

## Quick Example - Migrate from one database to another

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

## Caveat when using Doctrine Migrations or Doctrine DBAL Schema Manager

If you are using the Doctrine DBAL Schema Manager in your application to manage schemas, you probably want to ignore the
tracking table.  To do this, set the `setFilterSchemaAssetsExpression` on the DBAL config when you are creating your
connection object: 

```php

// $connection is an instance of \Doctrine\DBAL\Connection
// Assuming your table name is using the default; 'data_migrate_tracker'
$connection->getCOnfiguration()->setFilterSchemaAssetsExpression('/^((?!data_migrate_tracker).)*$/'); 

```

If you are using Symfony, you can [set this in the configuration](https://symfony.com/doc/current/bundles/DoctrineMigrationsBundle/index.html#manual-tables):

```yaml
# Assuming your table name is using the default; 'data_migrate_tracker'
doctrine:
    dbal:
        schema_filter: ~^((?!data_migrate_tracker).)*$~
```  

## Tracking Migrations using Events

Shuttle uses the [Symfony Event Dispatcher](https://symfony.com/doc/current/components/event_dispatcher.html) library 
to dispatch events as records are migrated or reverted. You can create event listeners to report on progress or log 
these events if you wish.
