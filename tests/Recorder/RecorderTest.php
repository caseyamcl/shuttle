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

namespace ShuttleTest\Recorder;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use PHPUnit\Framework\TestCase;
use Shuttle\Recorder\Recorder;

/**
 * Recorder Test
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class RecorderTest extends TestCase
{
    /**
     * @var Connection
     */
    private $dbConn;

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function setUp()
    {
        parent::setUp();

        $this->dbConn = DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
            'path' => sys_get_temp_dir() . '/Shuttle_test.sqlite'
        ]);
    }

    protected function tearDown()
    {
        unlink(sys_get_temp_dir() . '/Shuttle_test.sqlite');
        parent::tearDown();
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public function testInstantiateSucceeds()
    {
        $obj = new Recorder($this->dbConn);
        $this->assertInstanceOf(Recorder::class, $obj);
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public function testGetMigratedCountReturnsZeroForNothingMigrated()
    {
        $obj = new Recorder($this->dbConn);
        $this->assertEquals(0, $obj->getMigratedCount('foo'));
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public function testMarkMigratedAddsRecordToDb()
    {
        $obj = new Recorder($this->dbConn);
        $obj->markMigrated('foo', 5, 10);
        $this->assertEquals(1, $obj->getMigratedCount('foo'));
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     */
    public function testRemoveMigratedMarkWorks()
    {
        $obj = new Recorder($this->dbConn);

        $obj->markMigrated('foo', 5, 10);
        $this->assertEquals(1, $obj->getMigratedCount('foo'));

        $obj->removeMigratedMark('foo', 10);
        $this->assertEquals(0, $obj->getMigratedCount('foo'));
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public function testGetNewIdsReturnsExpectedValues()
    {
        $obj = new Recorder($this->dbConn);

        $obj->markMigrated('foo', 5, 10);
        $obj->markMigrated('foo', 6, 11);
        $obj->markMigrated('foo', 7, 12);
        $obj->markMigrated('foo', 8, 13);
        $obj->markMigrated('bar', 9, 14);
        $obj->markMigrated('bar', 10, 15);

        /** @var \Traversable $fooList */
        /** @var \Traversable $barList */
        $fooList = $obj->listDestinationIds('foo');
        $barList = $obj->listDestinationIds('bar');

        $this->assertEquals([10, 11, 12, 13], iterator_to_array($fooList));
        $this->assertEquals([15, 14], iterator_to_array($barList));
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     * @expectedException \PDOException
     */
    public function testMarkMigratedThrowsExceptionForDuplicateOldId()
    {
        $obj = new Recorder($this->dbConn);
        $obj->markMigrated('foo', 5, 10);
        $obj->markMigrated('foo', 5, 11);
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     * @expectedException \PDOException
     */
    public function testMarkMigratedThrowsExceptionForDuplicateNewId()
    {
        $obj = new Recorder($this->dbConn);
        $obj->markMigrated('foo', 5, 10);
        $obj->markMigrated('foo', 6, 10);
    }
}
