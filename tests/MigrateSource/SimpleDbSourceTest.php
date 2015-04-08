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


use Shuttle\MigrateSource\SimpleDbSource;
use Shuttle\Service\Migrator\SourceInterface;
use ShuttleTest\Service\Migrator\AbstractSourceInterfaceTest;

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
            );"
        );

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
    protected function getSourceObj()
    {
        return new SimpleDbSource(
            self::$dbConn,
            "SELECT COUNT(i.id) FROM items i",
            "SELECT i.* FROM items i",
            "SELECT i.* FROM items i WHERE i.id = ?"
        );
    }

    /**
     * @return string
     */
    protected function getExistingRecordId()
    {
        return '200';
    }

    /**
     * @return string
     */
    protected function getNonExistentRecordId()
    {
        return '900';
    }
}
