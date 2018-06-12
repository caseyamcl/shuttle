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

namespace ShuttleTest\MigrateSource;

use Shuttle\MigrateSource\JsonSource;
use ShuttleTest\Migrator\AbstractSourceInterfaceTest;

class SimpleJsonSourceTest extends AbstractSourceInterfaceTest
{
    /**
     * @return SourceInterface
     */
    protected function getSourceObj(): SourceInterface
    {
        return new JsonSource(file_get_contents(__DIR__ . '/../Fixture/files/source.json'), 'id');
    }

    /**
     * @return string
     */
    protected function getExistingRecordId(): string
    {
        return '45';
    }

    /**
     * @return string
     */
    protected function getNonExistentRecordId(): string
    {
        return '10000';
    }
}
