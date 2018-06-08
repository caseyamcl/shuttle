<?php
/**
 * Shuttle Library
 *
 * @license https://opensource.org/licenses/MIT
 * @link https://github.com/caseyamcl/phpoaipmh
 * @package caseyamcl/shuttle
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * ------------------------------------------------------------------
 */

namespace Shuttle\Recorder;

/**
 * Recorder Interface
 *
 * Tracks migration state for records
 *
 * @package Shuttle\Service\Recorder
 */
interface RecorderInterface
{
    /**
     * Get the number of items migrated already
     *
     * @param string $type
     * @return int
     */
    public function getMigratedCount(string $type): int;

    /**
     * Get a list of destination IDs for items that have been migrated
     *
     * @param string $type
     * @return iterable|string[]  Array of new IDs
     */
    public function listDestinationIds(string $type): iterable;

    /**
     * Get a list of source IDs for items that have been migrated (i.e. they have corresponding destination IDs)
     *
     * @param string $type
     * @return iterable
     */
    public function listMigratedSourceIds(string $type): iterable;

    /**
     * Determine if a source item has been migrated yet
     *
     * @param string $type
     * @param string $sourceId
     * @return boolean
     */
    public function isMigrated(string $type, string $sourceId): bool;

    /**
     * Get a destination ID for a source ID
     *
     * @param string $type
     * @param string $sourceId
     * @return string|null
     */
    public function findDestinationId(string $type, string $sourceId): ?string;

    /**
     * Get a source ID for a destination ID
     *
     * @param string $type
     * @param string $destinationId
     * @return string|null
     */
    public function findSourceId(string $type, string $destinationId): ?string;

    /**
     * Record that an item has been migrated
     *
     * @param string $type
     * @param string $sourceId
     * @param string $destinationId
     */
    public function markMigrated(string $type, string $sourceId, string $destinationId): void;

    /**
     * Update record to omit that an item has been migrated
     *
     * @param string $type
     * @param string $destinationId
     */
    public function removeMigratedMark(string $type, string $destinationId): void;
}
