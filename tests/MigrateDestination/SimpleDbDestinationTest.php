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

namespace ShuttleTest\MigrateDestination;

use Shuttle\MigrateDestination\DbTableDestination;
use Shuttle\Migrator\DestinationInterface;
use ShuttleTest\Migrator\AbstractDestinationInterfaceTest;

/**
 * Class SimpleDbDestinationTest
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class SimpleDbDestinationTest extends AbstractDestinationInterfaceTest
{
    /**
     * @var \PDO
     */
    protected $dbConn;

    public function setUp()
    {
        parent::setUp();

        if (!extension_loaded('pdo_sqlite')) {
            self::markTestSkipped('No SQLite PDO Available');
        }

        $path = sys_get_temp_dir() . '/Shuttle.db.test.sqlite';
        $this->dbConn = new \PDO('sqlite:' . $path);

        $this->dbConn->query("CREATE TABLE items (
               id   INT PRIMARY KEY  NOT NULL,
               name TEXT             NOT NULL
            );");

        $this->dbConn->query("INSERT INTO items (id, name) VALUES (100, 'bob')");
        $this->dbConn->query("INSERT INTO items (id, name) VALUES (200, 'sally')");
        $this->dbConn->query("INSERT INTO items (id, name) VALUES (300, 'roy')");
    }

    public function tearDown()
    {
        unlink(sys_get_temp_dir() . '/Shuttle.db.test.sqlite');
        parent::tearDown();
    }

    /**
     * @return DestinationInterface
     */
    protected function getDestObj(): DestinationInterface
    {
        return new DbTableDestination($this->dbConn, 'items', 'id');
    }

    /**
     * @return string
     */
    protected function getExistingRecordId(): string
    {
        return '200';
    }

    /**
     * @return array key/value pairs for record to add
     */
    protected function getNewRecordData(): array
    {
        return ['id' => '400', 'name' => 'joe'];
    }

    /**
     * @return string
     */
    protected function getNonExistentRecordId(): string
    {
        return '900';
    }
}
