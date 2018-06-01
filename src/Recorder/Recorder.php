<?php
/**
 * Shuttle Library
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

    /**
     * @param Connection $dbConn
     * @param string $tableName
     * @throws \Doctrine\DBAL\DBALException
     */
    public function __construct(Connection $dbConn, $tableName = 'tracker')
    {
        $this->dbConn    = $dbConn;
        $this->tableName = $tableName;

        $this->init();
    }

    /**
     * @param string $type
     * @return int
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getMigratedCount(string $type): int
    {
        $stmt = $this->dbConn->prepare("SELECT COUNT(rowid) as cnt FROM {$this->tableName} WHERE type = ?");
        $stmt->execute([$type]);
        return (int) $stmt->fetchColumn(0);
    }

    /**
     * @param string $type
     * @return iterable
     * @throws \Doctrine\DBAL\DBALException
     */
    public function listDestinationIds(string $type): iterable
    {
        $stmt = $this->dbConn->prepare("SELECT new_id FROM {$this->tableName} WHERE type = ?");
        $stmt->execute([$type]);
        return new DoctrineColumnIterator($stmt, 'new_id');
    }

    /**
     * @param string $type
     * @param string $sourceId
     * @return bool
     * @throws \Doctrine\DBAL\DBALException
     */
    public function isMigrated(string $type, string $sourceId): bool
    {
        return (bool) (strlen($this->findDestinationId($type, $sourceId)));
    }

    /**
     * @param string $type
     * @param string $sourceId
     * @return null|string
     * @throws \Doctrine\DBAL\DBALException
     */
    public function findDestinationId(string $type, string $sourceId): ?string
    {
        $stmt = $this->dbConn->prepare("SELECT new_id FROM {$this->tableName} WHERE type = ? AND old_id = ?");
        $stmt->execute([$type, $sourceId]);
        return (string) $stmt->fetchColumn(0);
    }

    /**
     * @param string $type
     * @param string $destinationId
     * @return string|null
     * @throws \Doctrine\DBAL\DBALException
     */
    public function findSourceId(string $type, string $destinationId): ?string
    {
        $stmt = $this->dbConn->prepare("SELECT old_id FROM {$this->tableName} WHERE type = ? AND new_id = ?");
        $stmt->execute([$type, $destinationId]);
        return (string) $stmt->fetchColumn(0);
    }

    /**
     * @param string $type
     * @param string $sourceId
     * @param string $destinationId
     * @throws \Doctrine\DBAL\DBALException
     */
    public function markMigrated(string $type, string $sourceId, string $destinationId): void
    {
        // Check and throw exception manually, since indexes seem to be failing silently.. (?)
        if ($this->findDestinationId($type, $sourceId)) {
            throw new \PDOException(sprintf(
                "Integrity violation (app layer):  Duplicate key for %s.oldId: %s",
                $type,
                $sourceId
            ));
        }
        if ($this->findSourceId($type, $destinationId)) {
            throw new \PDOException(sprintf(
                "Integrity violation (app layer):  Duplicate key for %s.newId: %s",
                $type,
                $destinationId
            ));
        }

        $this->dbConn->insert($this->tableName, [
            'type'      => $type,
            'old_id'    => $sourceId,
            'new_id'    => $destinationId,
            'timestamp' => time()
        ]);
    }

    /**
     * @param string $type
     * @param string $destinationId
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     */
    public function removeMigratedMark(string $type, string $destinationId): void
    {
        $this->dbConn->delete($this->tableName, ['type' => $type, 'new_id' => $destinationId]);
    }

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
        $table->addColumn('type', 'string', ['length'   => 32,   'notnull' => true]);
        $table->addColumn('old_id', 'string', ['length'   => 32,   'notnull' => true]);
        $table->addColumn('new_id', 'string', ['length'   => 32,   'notnull' => true]);
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
