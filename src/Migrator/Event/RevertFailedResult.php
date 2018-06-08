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

namespace Shuttle\Migrator\Event;

/**
 * Class RevertFailedResult
 *
 * @package Shuttle\Migrator\Event
 */
class RevertFailedResult extends MigrateFailedResult
{
    /**
     * RevertFailedResult constructor.
     * @param string $migratorName
     * @param string $destinationId Destination record ID
     * @param string $message
     * @param \Exception|null $e
     */
    public function __construct(string $migratorName, string $destinationId, string $message, \Exception $e = null)
    {
        parent::__construct($migratorName, $destinationId, $message, $e);
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return "(Destination ID: {$this->getRecordId()}): " . $this->msg;
    }
}
