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

use Shuttle\MigrateSource\YamlSource;
use Shuttle\Migrator\SourceInterface;
use ShuttleTest\Service\Migrator\AbstractSourceInterfaceTest;

/**
 * Yaml Source Test
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class SimpleYamlSourceTest extends AbstractSourceInterfaceTest
{
    /**
     * @expectedException \Symfony\Component\Yaml\Exception\ParseException
     */
    public function testMalformedYamlSourceThrowsException()
    {
        $obj = new YamlSource('abcasdf---@(#*1230230--2-349u0h8dsfa', 'id');
    }

    /**
     * @return SourceInterface
     */
    protected function getSourceObj(): SourceInterface
    {
        return new YamlSource(file_get_contents(__DIR__ . '/../Fixture/files/source.yml'), 'id');
    }

    /**
     * @return string
     */
    protected function getExistingRecordId(): string
    {
        return '350';
    }

    /**
     * @return string
     */
    protected function getNonExistentRecordId(): string
    {
        return '4600';
    }
}
