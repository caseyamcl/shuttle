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

use Shuttle\Migrator\Exception\MissingItemException;

/**
 * Source Interface
 *
 * @package Shuttle\Service\Migrator\Source
 */
interface SourceInterface extends \Traversable, \Countable
{
    /**
     * @return iterable|string[]  Get a list of record IDs in the source
     */
    function listItemIds(): iterable;

    /**
     * @param string $id  The item ID to get
     * @return array  The item, represented as key/value associative array
     * @throws MissingItemException
     */
    function getItem(string $id): array;
}
