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

namespace Shuttle\Migrator;

/**
 * Migrator Interface
 *
 * @package Shuttle\Service\Migrator
 */
interface MigratorInterface
{
    /**
     * @return string  A machine-friendly identifier for the type of record being migrated (e.g. 'posts', 'authors'...)
     */
    public function getSlug(): string;

    /**
     * @return string  A description of the records being migrated
     */
    public function getDescription(): string;

    /**
     * @return SourceInterface
     */
    public function getSource(): SourceInterface;

    /**
     * @return DestinationInterface
     */
    public function getDestination(): DestinationInterface;

    /**
     * @return int  Number of records in the source
     */
    public function countSourceItems(): int;

    /**
     * @return iterable|string[]
     */
    public function listSourceIds(): iterable;

    /**
     * Migrate a single record
     *
     * @param string $sourceRecordId  Record ID in the old system
     * @return string  The new Record ID
     */
    public function migrate(string $sourceRecordId): string;

    /**
     * Revert a single record
     *
     * @param string $destinationRecordId
     * @return bool  If the record was actually deleted, return TRUE, else FALSE
     */
    public function revert(string $destinationRecordId): bool;
}
