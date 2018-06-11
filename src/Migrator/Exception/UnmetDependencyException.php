<?php

namespace Shuttle\Migrator\Exception;

/**
 * Unmet Dependency Exception
 *
 * Utility exception that can be used to cancel a migration in the case that a dependent
 * migration hasn't occurred yet.
 *
 * @package Shuttle\Migrator\Exception
 */
class UnmetDependencyException extends MigratorException
{
    // pass..
}