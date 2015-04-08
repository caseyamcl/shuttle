<?php
/**
 * Shuttle
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

namespace ShuttleTest\MigrateSource;

use Shuttle\MigrateSource\SimpleYamlSource;
use Shuttle\Service\Migrator\SourceInterface;
use ShuttleTest\Service\Migrator\AbstractSourceInterfaceTest;

/**
 * Yaml Source Test
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class SimpleYamlSourceTest extends AbstractSourceInterfaceTest
{
    public function testMalformedYamlSourceThrowsException()
    {
        $this->setExpectedException('Symfony\Component\Yaml\Exception\ParseException');
        $obj = new SimpleYamlSource('abcasdf---@(#*1230230--2-349u0h8dsfa', 'id');
    }

    /**
     * @return SourceInterface
     */
    protected function getSourceObj()
    {
        return new SimpleYamlSource(file_get_contents(__DIR__ . '/../Fixture/files/source.yml'), 'id');
    }

    /**
     * @return string
     */
    protected function getExistingRecordId()
    {
        return 350;
    }

    /**
     * @return string
     */
    protected function getNonExistentRecordId()
    {
        return 4600;
    }
}
