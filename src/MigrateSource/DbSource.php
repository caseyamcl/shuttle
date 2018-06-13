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

use Shuttle\Exception\MissingItemException;
use Doctrine\Instantiator\Exception\InvalidArgumentException;
use Shuttle\SourceInterface;
use Shuttle\SourceItem;

/**
 * Simple Database Source
 *
 * Provides a simple database source for records.  Does not do paginated
 * retrieval or anything like that.
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class DbSource implements SourceInterface
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
     * Constructor
     *
     * @param \PDO $dbConn Database connection
     * @param string $countQuery Should accept no params and return a single row, single column with number of records
     * @param string $listQuery Should accept no params and return a single-column list of IDs
     * @param string $singleQuery Should accept one param, the ID (placeholder is a '?'), and return a single record
     * @param int $idType
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

    /**
     * If is countable, return the number of source items, or NULL if unknown
     * @return int|null
     */
    public function countSourceItems(): ?int
    {
        $stmt = $this->dbConn->prepare($this->countQuery);
        $stmt->execute();
        return $stmt->fetchColumn(0);
    }

    /**
     * @param string $id
     * @return SourceItem
     */
    public function getSourceItem(string $id): SourceItem
    {
        $stmt = $this->dbConn->prepare($this->singleQuery);
        $stmt->bindValue(1, $id);
        $stmt->execute();

        if ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            return new SourceItem($id, $row);
        }
        else {
            throw new MissingItemException();
        }
    }

    /**
     * Get the next source record, represented as an array
     *
     * Return an array for the next item, or NULL for no more item
     *
     * @return iterable|string[]
     */
    public function getSourceIdIterator(): iterable
    {
        $stmt = $this->dbConn->prepare($this->listQuery);
        $stmt->execute();

        while ($val = $stmt->fetchColumn(0)) {
            yield (string) $val;
        }
    }
}
