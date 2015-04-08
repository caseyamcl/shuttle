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

namespace Shuttle\MigrateDestination;


use Shuttle\Service\Migrator\DestinationInterface;
use Shuttle\Service\Migrator\Exception\MissingRecordException;

/**
 * Class SimpleDbDestination
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class SimpleDbDestination implements DestinationInterface
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
    private $idFieldName;

    // ---------------------------------------------------------------

    /**
     * Constructor
     *
     * @param \PDO    $dbConn
     * @param string  $tableName
     * @param string  $idFieldName
     */
    public function __construct(\PDO $dbConn, $tableName, $idFieldName)
    {
        $this->dbConn      = $dbConn;
        $this->tableName   = $tableName;
        $this->idFieldName = $idFieldName;
    }

    // ---------------------------------------------------------------

    /**
     * Get record
     *
     * @param string $id
     * @return array  Record, represented as array
     * @throws MissingRecordException
     */
    function getRecord($id)
    {
        $sql = sprintf(
            "SELECT t.* FROM %s t WHERE t.%s = ?",
            $this->tableName,
            $this->idFieldName
        );

        $stmt = $this->dbConn->prepare($sql);
        $stmt->execute([$id]);

        if ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            return $row;
        }
        else {
            throw new MissingRecordException("Could not find record with ID: " . $id);
        }
    }

    // ---------------------------------------------------------------

    /**
     * Save a record
     *
     * Create or update the record
     *
     * @param array $recordData
     * @return string  The ID of the inserted record
     */
    function saveRecord(array $recordData)
    {
        $query = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $this->tableName,
            implode(', ', array_keys($recordData)),
            implode(', ', array_fill(0, count($recordData), '?'))
        );

        $stmt = $this->dbConn->prepare($query);
        $stmt->execute(array_values($recordData));

        return (isset($recordData[$this->idFieldName]))
            ? $recordData[$this->idFieldName]
            : (string) $this->dbConn->lastInsertId();
    }

    // ---------------------------------------------------------------

    /**
     * Remove a record
     *
     * @param string $id
     * @return bool
     */
    function deleteRecord($id)
    {
        $sql = sprintf("DELETE FROM %s WHERE %s = ?", $this->tableName, $this->idFieldName);
        $stmt = $this->dbConn->prepare($sql);

        $stmt->execute([$id]);
        return (bool) $stmt->rowCount();
    }
}
