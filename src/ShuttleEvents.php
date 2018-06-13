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
     * This event occurs after a source item has been read, but before the record has been prepared
     */
    const READ_SOURCE_RECORD = 'shuttle.read_source';

    /**
     * Pre-persist
     *
     * This event occurs after a source item has been prepared, but before it has been persisted in the destination
     */
    const PRE_PERSIST = 'shuttle.pre_persist';

    /**
     * Pre-revert
     *
     * This event occurs before an item is removed from the destination
     */
    const PRE_REVERT = 'shuttle.pre_revert';

    /**
     * This event occurs after an item has been persisted in the destination, and the migration has been recorded
     */
    const MIGRATE_RESULT = 'shuttle.post_migrate';

    /**
     * This event occurs after an item has been removed from the destination, and the revert has been recorded
     */
    const REVERT_RESULT = 'shuttle.post_revert';

    /**
     * This event occurs if processing was interrupted by the $continue callback before the iterator completed
     */
    const ABORT = 'shuttle.abort';
}