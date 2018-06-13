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

use Shuttle\MigrateSource\DbSource;
use Shuttle\SourceInterface;
use ShuttleTest\AbstractSourceInterfaceTest;

/**
 * Simple DB Test
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class SimpleDbSourceTest extends AbstractSourceInterfaceTest
{
    /**
     * @var \PDO
     */
    private static $dbConn;


    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        if (!extension_loaded('pdo_sqlite')) {
            self::markTestSkipped('No SQLite PDO Available');
        }

        $path = sys_get_temp_dir() . '/Shuttle.db.test.sqlite';
        self::$dbConn = new \PDO('sqlite:' . $path);

        self::$dbConn->query("CREATE TABLE items (
               id INT PRIMARY KEY     NOT NULL,
               name           TEXT    NOT NULL
            );");

        self::$dbConn->query("INSERT INTO items (id, name) VALUES (100, 'bob')");
        self::$dbConn->query("INSERT INTO items (id, name) VALUES (200, 'sally')");
        self::$dbConn->query("INSERT INTO items (id, name) VALUES (300, 'roy')");
    }

    public static function tearDownAfterClass()
    {
        unlink(sys_get_temp_dir() . '/Shuttle.db.test.sqlite');
        parent::tearDownAfterClass();
    }

    /**
     * @return SourceInterface
     */
    protected function getSourceObj(): SourceInterface
    {
        return new DbSource(
            self::$dbConn,
            "SELECT COUNT(i.id) FROM items i",
            "SELECT i.* FROM items i",
            "SELECT i.* FROM items i WHERE i.id = ?"
        );
    }

    /**
     * @return string
     */
    protected function getExistingRecordId(): string
    {
        return '200';
    }

    /**
     * @return string
     */
    protected function getNonExistentRecordId(): string
    {
        return '900';
    }

    /**
     * @return int|null
     */
    protected function getExpectedCount(): ?int
    {
        return 3;
    }
}
