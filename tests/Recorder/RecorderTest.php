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
use Shuttle\SourceItem;

/**
 * Recorder Test
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class RecorderTest extends TestCase
{
    const TYPE = 'test_type';

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
        $iterator = iterator_to_array($obj->getRecords('foo'));
        $this->assertEmpty($iterator);
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public function testMarkMigratedAddsRecordToDb()
    {
        $obj = new Recorder($this->dbConn);
        $obj->addMigrateRecord(new SourceItem(5, ['foo' => 'bar']), 10, static::TYPE);
        $iterator = iterator_to_array($obj->getRecords(static::TYPE));
        $this->assertEquals(1, count($iterator));
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     */
    public function testRemoveMigratedMarkWorks()
    {
        $obj = new Recorder($this->dbConn);

        $obj->addMigrateRecord(new SourceItem(5, ['foo' => 'bar']), 10, static::TYPE);
        $this->assertEquals(1, $obj->countRecords(static::TYPE));

        $obj->removeMigrateRecord(5, static::TYPE);
        $this->assertEquals(0, $obj->countRecords(static::TYPE));
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public function testGetNewIdsReturnsExpectedValues()
    {
        $obj = new Recorder($this->dbConn);

        $obj->addMigrateRecord(new SourceItem(5, ['foo']), '10', 'foo');
        $obj->addMigrateRecord(new SourceItem(6, ['foo']), '11', 'foo');
        $obj->addMigrateRecord(new SourceItem(7, ['foo']), '12', 'foo');
        $obj->addMigrateRecord(new SourceItem(8, ['foo']), '13', 'foo');
        $obj->addMigrateRecord(new SourceItem(9, ['bar']), '14', 'bar');
        $obj->addMigrateRecord(new SourceItem(10, ['bar']), '15', 'bar');

        /** @var \Traversable $fooList */
        /** @var \Traversable $barList */
        $fooList = $obj->getRecords('foo');
        $barList = $obj->getRecords('bar');

        $this->assertEquals(4, count(iterator_to_array($fooList)));
        $this->assertEquals(2, count(iterator_to_array($barList)));
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     * @expectedException \Exception
     */
    public function testAddMigrateRecordThrowsExceptionForDuplicateSourceId()
    {
        $obj = new Recorder($this->dbConn);
        $obj->addMigrateRecord(new SourceItem(5, ['foo']), '10', 'foo');
        $obj->addMigrateRecord(new SourceItem(5, ['foo']), '11', 'foo');
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     * @expectedException \Exception
     */
    public function testMarkMigratedThrowsExceptionForDuplicateDestinationId()
    {
        $obj = new Recorder($this->dbConn);
        $obj->addMigrateRecord(new SourceItem(5, ['foo']), '10', 'foo');
        $obj->addMigrateRecord(new SourceItem(6, ['foo']), '10', 'foo');
    }

    public function testFindMigrateRecordReturnsNullIfRecordDoesNotExist()
    {
        $obj = new Recorder($this->dbConn);
        $obj->addMigrateRecord(new SourceItem(5, ['foo']), '10', 'foo');
        $this->assertNull($obj->findRecord(10, 'foo'));
    }
}
