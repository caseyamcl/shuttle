<?php
/**
 * conveyorbelt
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

namespace ConveyorBeltTest\MigrateSource;


use ConveyorBelt\MigrateSource\SimpleJsonSource;
use ConveyorBelt\Service\Migrator\SourceInterface;
use ConveyorBeltTest\Service\Migrator\AbstractSourceInterfaceTest;

class SimpleJsonSourceTest extends AbstractSourceInterfaceTest
{
    /**
     * @return SourceInterface
     */
    protected function getSourceObj()
    {
        return new SimpleJsonSource(file_get_contents(__DIR__ . '/../Fixture/files/source.json'), 'id');
    }

    /**
     * @return string
     */
    protected function getExistingRecordId()
    {
        return '45';
    }

    /**
     * @return string
     */
    protected function getNonExistentRecordId()
    {
        return '10000';
    }
}
