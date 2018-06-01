<?php
/**
 * shuttle
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

namespace Shuttle\Recorder;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use RuntimeException;

/**
 * Default Database Builder builds a Database for the recorder using SQLite in a hidden file
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class DefaultDatabaseBuilder
{
    const AUTO = null;

    // ---------------------------------------------------------------

    /**
     * @var string
     */
    private $dbPath;

    /**
     * @var string
     */
    private $appSlug;

    /**
     * Build Default Database Connection
     *
     * @param string $appSlug  Unique alphanumeric 'slug' for the application
     * @param string $dbPath   Optionally use a custom directory to store the SQLite database
     * @return Connection  Connection to the SQLiteDatabase
     */
    public static function buildDefaultDatabaseConnection(
        string $appSlug = 'shttl',
        string $dbPath = self::AUTO
    ): Connection {
        $that = new static($appSlug, $dbPath);
        return $that->getDefaultConnection();
    }

    /**
     * Constructor
     *
     * @param string $appSlug  Unique alphanumeric 'slug' for the application
     * @param string $dbPath   Optionally use a custom directory to store the SQLite database
     */
    public function __construct(string $appSlug = 'shttl', string $dbPath = self::AUTO)
    {
        if (! class_exists('Doctrine\DBAL\Connection')) {
            throw new RuntimeException(
                "Cannot build tracking database without Doctrine DBAL library."
                . " Install with `composer require doctrine/dbal`"
            );
        }
        if (! class_exists('\SQLite3')) {
            throw new RuntimeException(
                "Cannot build tracking database without SQLite3 Extension."
                . " Install PHP SQLite3 Extension to fix this."
            );
        }

        if ($dbPath && ! is_dir($dbPath)) {
            throw new \RuntimeException("Invalid directory/folder for SQLite database: " . $dbPath);
        }

        $this->dbPath  = $dbPath ?: $this->buildDefaultPath();
        $this->appSlug = $appSlug;

        if (! is_writable(dirname($this->dbPath))) {
            throw new RuntimeException("Tracker/recorder database path not writable: " . $this->dbPath);
        }
    }

    /**
     * Get Default Database Connection
     *
     * @return Connection
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getDefaultConnection(): Connection
    {
        $dbFullPath = sprintf("%s/%s.sqlite", rtrim($this->dbPath, DIRECTORY_SEPARATOR), $this->appSlug);

        return DriverManager::getConnection([
            'path' => $dbFullPath,
            'driver' => 'pdo_sqlite'
        ]);
    }

    /**
     * Build Default Path
     *
     * @return string  Full path to SQLite Database
     */
    protected function buildDefaultPath(): string
    {
        // Auto-determine the path for the the SQLITE database
        $dirPath = sprintf('%s/.shttl', $_SERVER['HOME']);

        // Auto-create the path if it does not already exist
        if (! file_exists($dirPath) or ! is_dir($dirPath)) {
            mkdir($dirPath, 0700);
        }

        return $dirPath . DIRECTORY_SEPARATOR;
    }
}
