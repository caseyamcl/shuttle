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
 * Class MigrateFailedResult
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class MigrateFailedResult extends Event implements MigrateResultInterface
{
    /**
     * @var string
     */
    protected $recId;

    /**
     * @var string
     */
    protected $msg;

    /**
     * @var \Exception
     */
    private $exception;

    /**
     * @param string     $sourceId
     * @param string     $message
     * @param \Exception $e
     */
    public function __construct(string $sourceId, string $message, \Exception $e = null)
    {
        $this->recId     = $sourceId;
        $this->msg       = $message;
        $this->exception = $e;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return self::FAILED;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return "(Source ID: {$this->getRecordId()}): " . $this->msg;
    }

    /**
     * @return \Exception
     */
    public function getException(): \Exception
    {
        return $this->exception;
    }

    /**
     * @return string
     */
    public function getRecordId(): string
    {
        return $this->recId;
    }
}
