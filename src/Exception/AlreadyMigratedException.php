<?php

namespace Shuttle\Exception;

/**
 * Thrown when attempting to persist a record is already migrated
 *
 * @package Shuttle\Exception
 */
class AlreadyMigratedException extends MigratorException
{
    // pass.
}