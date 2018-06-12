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

use Shuttle\Exception\MissingItemException;
use Shuttle\DestinationInterface;

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
     * @param mixed $preparedItem
     * @return string  Destination Id
     */
    public function persist($preparedItem): string
    {
        if (! is_array($preparedItem)) {
            throw new \InvalidArgumentException(get_called_class() . " expects record data to be an array");
        }

        $query = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $this->tableName,
            implode(', ', array_keys($preparedItem)),
            implode(', ', array_fill(0, count($preparedItem), '?'))
        );

        $stmt = $this->dbConn->prepare($query);
        $stmt->execute(array_values($preparedItem));

        return (isset($preparedItem[$this->idColumn]))
            ? $preparedItem[$this->idColumn]
            : (string) $this->dbConn->lastInsertId();
    }

    /**
     * @param string $destinationId
     * @return bool
     * @throws \RuntimeException  Throw exception if destination not found
     */
    public function remove(string $destinationId)
    {
        $sql = sprintf("DELETE FROM %s WHERE %s = ?", $this->tableName, $this->idColumn);
        $stmt = $this->dbConn->prepare($sql);

        $stmt->execute([$destinationId]);
        return (bool) $stmt->rowCount();
    }
}
