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

namespace Shuttle\Service\Migrator\Event;

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
    private $msg;

    /**
     * @var \Exception
     */
    private $exception;

    // ---------------------------------------------------------------

    /**
     * @param string     $destRecId
     * @param string     $msg
     * @param \Exception $e
     */
    public function __construct($destRecId, $msg, \Exception $e = null)
    {
        $this->recId       = $destRecId;
        $this->msg         = $msg;
        $this->exception   = $e;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return 0;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return "(new ID: {$this->recId}): " . $this->msg;
    }

    /**
     * @return \Exception
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * @return string
     */
    public function getRecId()
    {
        return $this->recId;
    }
}
