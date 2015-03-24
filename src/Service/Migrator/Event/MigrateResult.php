<?php
/**
 * ticketmove
 *
 * @license ${LICENSE_LINK}
 * @link ${PROJECT_URL_LINK}
 * @version ${VERSION}
 * @package ${PACKAGE_NAME}
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * ------------------------------------------------------------------
 */

namespace ConveyorBelt\Service\Migrator\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Migrate Result Value Object
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class MigrateResult extends Event implements MigrateResultInterface
{
    const SKIPPED   = -1;
    const PROCESSED = 1;

    // ---------------------------------------------------------------

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
     * @param string $oldId
     * @param string $newId
     * @param int    $status
     * @param string $message
     */
    public function __construct($type, $oldId, $status, $newId = '', $message = '')
    {
        // Status
        if ( ! in_array($status, [self::SKIPPED, self::PROCESSED])) {
            throw new \InvalidArgumentException("Invalid status: " . $status);
        }
        $this->status = $status;

        // Check logic
        if (empty($newId) && $status !== self::SKIPPED) {
            throw new \InvalidArgumentException("New ID cannot be empty unless status is skipped");
        }

        $this->type    = $type;
        $this->oldId   = $oldId;
        $this->newId   = $newId;
        $this->message = $message;

    }

    // ---------------------------------------------------------------

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getOldId()
    {
        return $this->oldId;
    }

    /**
     * @return string
     */
    public function getNewId()
    {
        return $this->newId;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return bool
     */
    public function isMigrated()
    {
        return $this->status == self::PROCESSED;
    }

    /**
     * @return bool
     */
    public function isSkipped()
    {
        return $this->status == self::SKIPPED;
    }
}
