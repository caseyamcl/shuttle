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

namespace Shuttle\MigrateSource;

use Shuttle\Service\Migrator\SourceInterface;
use Shuttle\Service\Migrator\Exception\MissingRecordException;
use Doctrine\Instantiator\Exception\InvalidArgumentException;

/**
 * Simple Database Source
 *
 * Provides a simple database source for records.  Does not do paginated
 * retrieval or anything like that.
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class DbSource implements \IteratorAggregate, SourceInterface
{
    /**
     * @var \PDO
     */
    protected $dbConn;

    /**
     * @var string
     */
    private $countQuery;

    /**
     * @var string
     */
    private $listQuery;

    /**
     * @var string
     */
    private $singleQuery;

    // ---------------------------------------------------------------

    /**
     * Build from DSN (DB Connection String)
     *
     * @param string $dbConnString
     * @param string $username
     * @param string $password
     * @param string $countQuery
     * @param string $listQuery
     * @param string $singleQuery
     * @return static
     */
    public static function build($dbConnString, $username, $password, $countQuery, $listQuery, $singleQuery)
    {
        return new static(
            new \PDO($dbConnString, $username, $password),
            $countQuery,
            $listQuery,
            $singleQuery
        );
    }

    // ---------------------------------------------------------------

    /**
     * Constructor
     *
     * @param \PDO   $dbConn       Database connection
     * @param string $countQuery   Should accept no parameters and return a single row, single column with number of records
     * @param string $listQuery    Should accept no parameters and return a single-column list of IDs
     * @param string $singleQuery  Should accept one parameter, the ID (placeholder is a '?'), and return a single record
     */
    public function __construct(\PDO $dbConn, $countQuery, $listQuery, $singleQuery)
    {
        $this->dbConn      = $dbConn;
        $this->countQuery  = $countQuery;
        $this->listQuery   = $listQuery;
        $this->singleQuery = $singleQuery;

        // Sanity check
        if (substr_count($this->singleQuery, '?') != 1) {
            throw new InvalidArgumentException("The single record query should include a single parameter placeholder ('?' symbol) to represent the ID");
        }
    }

    // ---------------------------------------------------------------

    /**
     * @return int
     */
    public function count()
    {
        $stmt = $this->dbConn->prepare($this->countQuery);
        $stmt->execute();
        return $stmt->fetchColumn(0);
    }

    // ---------------------------------------------------------------

    /**
     * @return string[]  Get a list of record IDs in the source
     */
    function listRecordIds()
    {
        $stmt = $this->dbConn->prepare($this->listQuery);
        $stmt->execute();

        while ($id = $stmt->fetchColumn(0)) {
            yield $id;
        }
    }

    /**
     * @return array|array[]  Array of records, represented as arrays
     * @throws MissingRecordException
     */
    function getRecord($id)
    {
        $stmt = $this->dbConn->prepare($this->singleQuery);
        $stmt->execute([$id]);

        if ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            return $row;
        }
        else {
            throw new MissingRecordException("Could not find record with ID: " . $id);
        }
    }

    /**
     * @return string[]
     */
    public function getIterator()
    {
        return $this->listRecordIds();
    }
}
