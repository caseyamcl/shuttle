<?php
/**
 * conveyorbelt
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

namespace ConveyorBelt\MigrateDestination;


use ConveyorBelt\Service\Migrator\DestinationInterface;
use ConveyorBelt\Service\Migrator\Exception\MissingRecordException;

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
        $stmt = $this->dbConn->prepare("SELECT t.* FROM ? t WHERE t.? = ?");
        $stmt->execute([$this->tableName, $this->idFieldName, $id]);

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
            "INSERT INTO %s SET (%s) VALUES (%s)",
            $this->tableName,
            implode(', ', array_keys($recordData)),
            array_fill(0, count($recordData), '?')
        );

        $stmt = $this->dbConn->prepare($query);
        $stmt->execute(array_values($recordData));

        return $this->dbConn->lastInsertId();
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
        $stmt = $this->dbConn->prepare("DELETE FROM ? WHERE ? = ?");
        $stmt->execute([$this->tableName, $this->idFieldName, $id]);
        return (bool) $stmt->rowCount();
    }
}
