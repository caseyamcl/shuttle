<?php
/**
 * ticketmove
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

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\PDOException;
use Doctrine\DBAL\Schema\Schema;
use Shuttle\Helper\DoctrineColumnIterator;

/**
 * DBAL Table-Based Recorder
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class Recorder implements RecorderInterface
{
    /**
     * @var Connection
     */
    private $dbConn;

    /**
     * @var string
     */
    private $tableName;

    // ---------------------------------------------------------------

    /**
     * @param Connection $dbConn
     * @param string     $tableName
     */
    public function __construct(Connection $dbConn, $tableName = 'tracker')
    {
        $this->dbConn    = $dbConn;
        $this->tableName = $tableName;

        $this->init();
    }

    // ---------------------------------------------------------------

    public function getMigratedCount($type)
    {
        $stmt = $this->dbConn->prepare("SELECT COUNT(rowid) as cnt FROM {$this->tableName} WHERE type = ?");
        $stmt->execute([$type]);
        return (int) $stmt->fetchColumn(0);
    }

    // ---------------------------------------------------------------

    public function getNewIds($type)
    {
        $stmt = $this->dbConn->prepare("SELECT new_id FROM {$this->tableName} WHERE type = ?");
        $stmt->execute([$type]);
        return new DoctrineColumnIterator($stmt, 'new_id');
    }

    // ---------------------------------------------------------------

    /**
     * @param string $type
     * @param string $oldId
     * @return boolean
     */
    public function isMigrated($type, $oldId)
    {
        return (bool) (strlen($this->getNewId($type, $oldId)));
    }

    // ---------------------------------------------------------------

    /**
     * @param string $type
     * @param string $oldId
     * @return string
     */
    public function getNewId($type, $oldId)
    {
        $stmt = $this->dbConn->prepare("SELECT new_id FROM {$this->tableName} WHERE type = ? AND old_id = ?");
        $stmt->execute([$type, $oldId]);
        return (string) $stmt->fetchColumn(0);
    }

    // ---------------------------------------------------------------

    /**
     * @param string $type
     * @param string $newId
     * @return string
     */
    public function getOldId($type, $newId)
    {
        $stmt = $this->dbConn->prepare("SELECT old_id FROM {$this->tableName} WHERE type = ? AND new_id = ?");
        $stmt->execute([$type, $newId]);
        return (string) $stmt->fetchColumn(0);
    }

    // ---------------------------------------------------------------

    /**
     * @param string $type
     * @param string $oldId
     * @param string $newId
     */
    public function markMigrated($type, $oldId, $newId)
    {
        // Check and throw exception manually, since indexes seem to be failing silently.. (?)
        if ($this->getNewId($type, $oldId)) {
            throw new \PDOException(sprintf("Integrity violation (app layer):  Duplicate key for %s.oldId: %s", $type, $oldId));
        }
        if ($this->getOldId($type, $newId)) {

            throw new \PDOException(sprintf("Integrity violation (app layer):  Duplicate key for %s.newId: %s", $type, $newId));
        }

        $this->dbConn->insert($this->tableName, [
            'type'      => $type,
            'old_id'    => $oldId,
            'new_id'    => $newId,
            'timestamp' => time()
        ]);
    }

    // ---------------------------------------------------------------

    /**
     * @param string $type
     * @param string $newId
     */
    public function removeMigratedMark($type, $newId)
    {
        $this->dbConn->delete($this->tableName, ['type' => $type, 'new_id' => $newId]);
    }

    // ---------------------------------------------------------------

    /**
     * Initialize the database
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    private function init()
    {
        $tableName = $this->tableName;
        $sm = $this->dbConn->getSchemaManager();

        // Table exists? Nothing to do.
        if ($sm->tablesExist([$tableName])) {
            return;
        }

        $schema = new Schema();
        $table = $schema->createTable($tableName);
        $table->addColumn('type',      'string',  ['length'   => 32,   'notnull' => true]);
        $table->addColumn('old_id',    'string',  ['length'   => 32,   'notnull' => true]);
        $table->addColumn('new_id',    'string',  ['length'   => 32,   'notnull' => true]);
        $table->addColumn('timestamp', 'integer', ['unsigned' => true, 'notnull' => true]);

        // Only add PK if not SQLITE (SQLITE does it automatically)
        //if ($this->dbConn->getDriver()->getName() != 'pdo_sqlite') {
        $table->addColumn('rowid', 'integer', ['unsigned' => true, 'notnull' => true, 'autoincrement' => true]);
        $table->setPrimaryKey(['rowid']);
        //}

        $table->addIndex(['old_id']);
        $table->addIndex(['new_id']);
        $table->addUniqueIndex(['type', 'old_id', 'new_id']);  // Why doesn't this get enforced loudly?

        $queries = $schema->toSql($this->dbConn->getDatabasePlatform());
        foreach ($queries as $query) {
            $this->dbConn->query($query);
        }
    }
}
