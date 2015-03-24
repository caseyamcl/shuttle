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

namespace ConveyorBeltTest\Recorder;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use FSURCC\TicketMove\Service\Recorder\Recorder;

class RecorderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Connection
     */
    private $dbConn;

    // ---------------------------------------------------------------

    protected function setUp()
    {
        parent::setUp();

        $this->dbConn = DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
            'path' => sys_get_temp_dir() . '/ticketmove_test.sqlite'
        ]);
    }

    // ---------------------------------------------------------------

    protected function tearDown()
    {
        unlink(sys_get_temp_dir() . '/ticketmove_test.sqlite');
        parent::tearDown();
    }

    // ---------------------------------------------------------------

    public function testInstantiateSucceeds()
    {
        $obj = new Recorder($this->dbConn);
        $this->assertInstanceOf('\FSURCC\TicketMove\Service\Recorder\Recorder', $obj);
    }

    // ---------------------------------------------------------------

    public function testGetMigratedCountReturnsZeroForNothingMigrated()
    {
        $obj = new Recorder($this->dbConn);
        $this->assertEquals(0, $obj->getMigratedCount('foo'));
    }

    // ---------------------------------------------------------------

    public function testMarkMigratedAddsRecordToDb()
    {
        $obj = new Recorder($this->dbConn);
        $obj->markMigrated('foo', 5, 10);
        $this->assertEquals(1, $obj->getMigratedCount('foo'));
    }

    // ---------------------------------------------------------------

    public function testRemoveMigratedMarkWorks()
    {
        $obj = new Recorder($this->dbConn);

        $obj->markMigrated('foo', 5, 10);
        $this->assertEquals(1, $obj->getMigratedCount('foo'));

        $obj->removeMigratedMark('foo', 10);
        $this->assertEquals(0, $obj->getMigratedCount('foo'));
    }

    // ---------------------------------------------------------------

    public function testGetNewIdsReturnsExpectedValues()
    {
        $obj = new Recorder($this->dbConn);

        $obj->markMigrated('foo', 5,  10);
        $obj->markMigrated('foo', 6,  11);
        $obj->markMigrated('foo', 7,  12);
        $obj->markMigrated('foo', 8,  13);
        $obj->markMigrated('bar', 9,  14);
        $obj->markMigrated('bar', 10, 15);

        $this->assertEquals([10, 11, 12, 13], iterator_to_array($obj->getNewIds('foo')));
        $this->assertEquals([15, 14], iterator_to_array($obj->getNewIds('bar')));
    }

    // ---------------------------------------------------------------

    public function testMarkMigratedThrowsExceptionForDuplicateOldId()
    {
        $this->setExpectedException('\PDOException');

        $obj = new Recorder($this->dbConn);
        $obj->markMigrated('foo', 5,  10);
        $obj->markMigrated('foo', 5,  11);
    }

    // ---------------------------------------------------------------

    public function testMarkMigratedThrowsExceptionForDuplicateNewId()
    {
        $this->setExpectedException('\PDOException');

        $obj = new Recorder($this->dbConn);
        $obj->markMigrated('foo', 5,  10);
        $obj->markMigrated('foo', 6,  10);
    }

    // ---------------------------------------------------------------

}
