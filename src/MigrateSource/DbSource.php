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

namespace Shuttle\MigrateSource;

use Shuttle\Migrator\SourceInterface;
use Shuttle\Migrator\Exception\MissingItemException;
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

    /**
     * Build from DSN (DB Connection String)
     *
     * @param string $dsn         PDO-compatible DSN
     * @param string $username    Database username
     * @param string $password    Database password
     * @param string $countQuery  Should accept no params and return a single row, single column with number of items
     * @param string $listQuery   Should accept no params and return a single-column list of item IDs
     * @param string $singleQuery Should accept one param, the ID (placeholder is a '?'), and return a single item
     * @return static
     */
    public static function build(
        string $dsn,
        string $username,
        string $password,
        string $countQuery,
        string $listQuery,
        string $singleQuery
    ) {
        return new static(
            new \PDO($dsn, $username, $password),
            $countQuery,
            $listQuery,
            $singleQuery
        );
    }

    /**
     * Constructor
     *
     * @param \PDO   $dbConn       Database connection
     * @param string $countQuery   Should accept no params and return a single row, single column with number of records
     * @param string $listQuery    Should accept no params and return a single-column list of IDs
     * @param string $singleQuery  Should accept one param, the ID (placeholder is a '?'), and return a single record
     */
    public function __construct(\PDO $dbConn, string $countQuery, string $listQuery, string $singleQuery)
    {
        $this->dbConn      = $dbConn;
        $this->countQuery  = $countQuery;
        $this->listQuery   = $listQuery;
        $this->singleQuery = $singleQuery;

        // Sanity check
        if (substr_count($this->singleQuery, '?') != 1) {
            throw new InvalidArgumentException(
                "The single record query should include a single parameter "
                . "placeholder ('?' symbol) to represent the ID"
            );
        }
    }

    public function count(): int
    {
        $stmt = $this->dbConn->prepare($this->countQuery);
        $stmt->execute();
        return $stmt->fetchColumn(0);
    }

    /**
     * @return iterable|string[]  Get a list of item IDs in the source
     */
    public function listItemIds(): iterable
    {
        $stmt = $this->dbConn->prepare($this->listQuery);
        $stmt->execute();

        while ($id = $stmt->fetchColumn(0)) {
            yield $id;
        }
    }

    public function getItem(string $id): array
    {
        $stmt = $this->dbConn->prepare($this->singleQuery);
        $stmt->execute([$id]);

        if ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            return $row;
        } else {
            throw new MissingItemException("Could not find record with ID: " . $id);
        }
    }

    /**
     * @return iterable|string[]
     */
    public function getIterator()
    {
        return $this->listItemIds();
    }
}
