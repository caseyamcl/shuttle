<?php
/**
 * shuttle
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

namespace Shuttle\Service\Recorder;

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

    // ---------------------------------------------------------------

    /**
     * Build Default Database Connection
     *
     * @param string $appSlug  Unique alphanumeric 'slug' for the application
     * @param string $dbPath   Optionally use a custom directory to store the SQLite database
     * @return \Doctrine\DBAL\Connection  Connection to the SQLiteDatabase
     */
    public static function buildDefaultDatabaseConnection($appSlug = 'shttl', $dbPath = self::AUTO)
    {
        $that = new static($appSlug, $dbPath);
        return $that->getDefaultConnection();
    }

    // ---------------------------------------------------------------

    /**
     * Constructor
     *
     * @param string $appSlug  Unique alphanumeric 'slug' for the application
     * @param string $dbPath   Optionally use a custom directory to store the SQLite database
     */
    public function __construct($appSlug = 'shttl', $dbPath = self::AUTO)
    {
        if ( ! function_exists('sqlite_open')) {
            throw new RuntimeException("Cannot build tracking database without SQLite Extension.  Install PHP SQLite Extension to fix this.");
        }

        if ($dbPath && ! is_dir($dbPath)) {
            throw new \RuntimeException("Invalid directory/folder for SQLite database: " . $dbPath);
        }

        $this->dbPath  = $dbPath ?: $this->buildDefaultPath();
        $this->appSlug = $appSlug;

        if ( ! is_writable(dirname($this->dbPath))) {
            throw new RuntimeException("Tracker/recorder database path not writable: " . $this->dbPath);
        }
    }

    // ---------------------------------------------------------------

    /**
     * Get Default Database Connection
     *
     * @return \Doctrine\DBAL\Connection
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getDefaultConnection()
    {
        // Get the Database URL
        $dbUrl = sprintf(
            'sqlite://%s%s%s',
            rtrim($this->dbPath, DIRECTORY_SEPARATOR),
            DIRECTORY_SEPARATOR,
            $this->appSlug
        );

        return DriverManager::getConnection(['url' => $dbUrl]);
    }

    // ---------------------------------------------------------------

    /**
     * Build Default Path
     *
     * @return string  Full path to SQLite Database
     */
    protected function buildDefaultPath()
    {
        // Auto-determine the path for the the SQLITE database
        $dirPath = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
            ? sprintf('%s\AppData\Local\.shttl', $_SERVER['HOME'])
            : sprintf("%s/.shttl", $_SERVER['HOME']);

        // Auto-create the path if it does not already exist
        if ( ! file_exists($dirPath) OR ! is_dir($dirPath)) {
            mkdir($dirPath, 0700);
        }

        return $dirPath . DIRECTORY_SEPARATOR;
    }
}
