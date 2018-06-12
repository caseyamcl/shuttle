<?php

namespace Shuttle;

/**
 * Class Events
 * @package Shuttle\NewShuttle
 */
final class ShuttleEvents
{
    /**
     * Read source record
     *
     * This event occurs after a source item has been read, but before
     * any other action has been taken
     */
    const READ_SOURCE_RECORD = 'shuttle.read_source';

    /**
     * Pre-prepare
     *
     * This event occurs after a source item has been read, but before it has been prepared.  It is
     * called directly after READ_SOURCE_RECORD
     */
    const PRE_PREPARE = 'shuttle.pre_prepare';

    /**
     * Pre-persist
     *
     * This event occurs after a source item has been prepared, but before it has been persisted in the destination
     */
    const PRE_PERSIST = 'shuttle.pre_persist';

    /**
     * Pre-revert
     *
     * This event occurs before an item has been removed from the destination
     */
    const PRE_REVERT = 'shuttle.pre_revert';

    /**
     * This event occurs after an item has been persisted (or removed) in the destination, but before the
     * recording of the migration occurs
     */
    const PRE_RECORD = 'shuttle.pre_record';

    /**
     * This event occurs after an item has been persisted in the destination, and the migration has been recorded
     */
    const POST_MIGRATE = 'shuttle.post_migrate';

    /**
     * This event occurs after an item has been removed from the destination, and the revert has been recorded
     */
    const POST_REVERT = 'shuttle.post_revert';

    /**
     * This event occurs when any part of an action (migrate or revert) fails
     */
    const ACTION_FAILED = 'shuttle.action_failed';
}