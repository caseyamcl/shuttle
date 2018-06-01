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

namespace Shuttle\MigrateDestination;

use Shuttle\Migrator\DestinationInterface;
use Shuttle\Migrator\Exception\MissingItemException;

/**
 * Class DbTableDestination
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class DbTableDestination implements DestinationInterface
{
    /**
     * @var \PDO
     */
    private $dbConn;

    /**
     * @var string
     */
    private $tableName;

    /**
     * @var string
     */
    private $idColumn;

    /**
     * Build from DSN
     *
     * @param string $dsn
     * @param string $username
     * @param string $password
     * @param string $tableName
     * @param string $idColumn
     * @return static
     */
    public static function build(string $dsn, string $username, string $password, string $tableName, string $idColumn)
    {
        return new static(new \PDO($dsn, $username, $password), $tableName, $idColumn);
    }

    /**
     * Constructor
     *
     * @param \PDO    $dbConn
     * @param string  $tableName
     * @param string  $idColumn
     */
    public function __construct(\PDO $dbConn, string $tableName, string $idColumn = 'id')
    {
        $this->dbConn    = $dbConn;
        $this->tableName = $tableName;
        $this->idColumn  = $idColumn;
    }

    /**
     * Get record
     *
     * @param string $destinationId
     * @return array  Record, represented as array
     * @throws MissingItemException
     */
    function getItem(string $destinationId): array
    {
        $sql = sprintf("SELECT t.* FROM %s t WHERE t.%s = ?", $this->tableName, $this->idColumn);

        $stmt = $this->dbConn->prepare($sql);
        $stmt->execute([$destinationId]);

        if ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            return $row;
        } else {
            throw new MissingItemException("Could not find record with ID: " . $destinationId);
        }
    }

    /**
     * Save a record
     *
     * Create or update the record
     *
     * @param array $recordData
     * @return string  The ID of the inserted record
     */
    function saveItem(array $recordData): string
    {
        $query = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $this->tableName,
            implode(', ', array_keys($recordData)),
            implode(', ', array_fill(0, count($recordData), '?'))
        );

        $stmt = $this->dbConn->prepare($query);
        $stmt->execute(array_values($recordData));

        return (isset($recordData[$this->idColumn]))
            ? $recordData[$this->idColumn]
            : (string) $this->dbConn->lastInsertId();
    }

    /**
     * Remove a record
     *
     * @param string $destinationId
     * @return bool
     */
    function deleteItem(string $destinationId): bool
    {
        $sql = sprintf("DELETE FROM %s WHERE %s = ?", $this->tableName, $this->idColumn);
        $stmt = $this->dbConn->prepare($sql);

        $stmt->execute([$destinationId]);
        return (bool) $stmt->rowCount();
    }
}
