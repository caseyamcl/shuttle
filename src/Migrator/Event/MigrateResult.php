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

namespace Shuttle\Migrator\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Migrate Result Value Object
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class MigrateResult extends Event implements MigrateResultInterface
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $oldId;

    /**
     * @var string
     */
    private $newId;

    /**
     * @var int
     */
    private $status;

    /**
     * @var string
     */
    private $message;

    // ---------------------------------------------------------------

    /**
     * Constructor
     *
     * @param string $type
     * @param string $sourceId
     * @param string $destinationId
     * @param int    $status
     * @param string $message
     */
    public function __construct(
        string $type,
        string $sourceId,
        int    $status,
        string $destinationId = '',
        string $message = ''
    ) {
        // Ensure valid status
        if (! in_array($status, [self::SKIPPED, self::PROCESSED])) {
            throw new \InvalidArgumentException("Invalid status: " . $status);
        }

        // Check logic
        if (empty($destinationId) && $status !== self::SKIPPED) {
            throw new \InvalidArgumentException("New ID cannot be empty unless status is skipped");
        }

        $this->status  = $status;
        $this->type    = $type;
        $this->oldId   = $sourceId;
        $this->newId   = $destinationId;
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getOldId(): string
    {
        return $this->oldId;
    }

    /**
     * @return string
     */
    public function getNewId(): string
    {
        return $this->newId;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return bool
     */
    public function isMigrated(): string
    {
        return $this->status == self::PROCESSED;
    }

    /**
     * @return bool
     */
    public function isSkipped(): bool
    {
        return $this->status == self::SKIPPED;
    }
}
