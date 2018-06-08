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
    public function getName(): string;

    /**
     * @return string  A description of the records being migrated
     */
    public function getDescription(): string;

    /**
     * @return int  Number of records in the source
     */
    public function countSourceItems(): int;

    /**
     * @return iterable|string[]
     */
    public function getSourceIdIterator(): iterable;

    /**
     * @param string $sourceId
     * @return array
     */
    public function getItemFromSource(string $sourceId): array;

    /**
     * @param array $source
     * @return mixed
     */
    public function prepareSourceItem(array $source);

    /**
     * @param mixed $record
     * @return string
     */
    public function persistDestinationItem($record): string;

    /**
     * Revert a single record
     *
     * @param string $destinationRecordId
     * @return bool  If the record was actually deleted, return TRUE, else FALSE
     */
    public function removeDestinationItem(string $destinationRecordId): bool;

    /**
     * Get a list of migrator slugs that should be migrated before this one
     *
     * NOTE: This is not a comprehensive; it does not list transitive dependencies.  Use
     * MigratorCollection::listDependencies() to determine all dependencies for a given migrator
     *
     * @return array|string[]
     */
    public function getDependsOn(): array;

    /**
     * This should return the slug
     *
     * @return string
     */
    public function __toString(): string;
}
