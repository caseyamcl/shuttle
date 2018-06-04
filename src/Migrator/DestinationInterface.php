<?php
/**
 * Shuttle
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
 * Interface DestinationInterface
 *
 * @package Shuttle\Service\Migrator\Destination
 */
interface DestinationInterface
{
    /**
     * Does the destination contain the given record?
     *
     * @param string $destinationId  The destination ID
     * @return bool  Record, represented as array
     */
    public function hasItem(string $destinationId): bool;

    /**
     * Save a record
     *
     * Create or update the record
     *
     * @param mixed $recordData
     * @return string  The ID of the inserted record
     */
    public function saveItem($recordData): string;

    /**
     * Remove a record
     *
     * @param string $destinationId
     * @return bool  If a record existed to be deleted, returns TRUE, else FALSE
     */
    public function deleteItem(string $destinationId): bool;
}
