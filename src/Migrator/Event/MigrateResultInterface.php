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

interface MigrateResultInterface
{
    const SKIPPED   = -1;
    const FAILED    = 0;
    const PROCESSED = 1;

    /**
     * @return int  (-1 skipped; 0 failed; 1 success)
     */
    public function getStatus(): int;

    /**
     * @return string
     */
    public function getMessage(): string;
}
