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
use Shuttle\SourceItem;

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
     * Find records for an item type
     *
     * @param string $type
     * @return iterable|\Generator|MigrateRecordInterface[]
     * @throws \Exception
     */
    public function getRecords(string $type): iterable
    {
        $qb = $this->dbConn->createQueryBuilder();
        $qb->select('t.type, t.source_id, t.destination_id, t.timestamp');
        $qb->where($qb->expr()->eq('t.type', ':type'));
        $qb->setParameter(':type', $type);
        $qb->from($this->tableName, 't');
        $qb->orderBy('t.timestamp', 'ASC');
        $stmt = $qb->execute();

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            yield new MigrateRecord(
                $row['source_id'],
                $row['destination_id'],
                $row['type'],
                $this->prepareTimestamp($row['timestamp'])
            );
        }
    }

    /**
     * Find a migration record; returns NULL if an item is not migrated
     *
     * @param string $sourceId
     * @param string $type
     * @return MigrateRecordInterface|null
     * @throws \Exception
     */
    public function findRecord(string $sourceId, string $type): ?MigrateRecordInterface
    {
        $qb = $this->dbConn->createQueryBuilder();
        $qb->select('t.type, t.source_id, t.destination_id, t.timestamp');
        $qb->from($this->tableName, 't');
        $qb->where($qb->expr()->eq('t.type', ':type'));
        $qb->andWhere($qb->expr()->eq('t.source_id', ':source_id'));
        $qb->setParameter(':type', $type);
        $qb->setParameter(':source_id', $sourceId);
        $stmt = $qb->execute();

        if ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            return new MigrateRecord(
                $row['source_id'],
                $row['destination_id'],
                $row['type'],
                $this->prepareTimestamp($row['timestamp'])
            );
        } else {
            return null;
        }
    }

    /**
     * Record a migration action
     *
     * @param SourceItem $source
     * @param string $destinationId
     * @param string $type
     * @return MigrateRecordInterface
     * @throws \Exception
     */
    public function addMigrateRecord(SourceItem $source, string $destinationId, string $type): MigrateRecordInterface
    {
        $timestamp = new \DateTimeImmutable();

        $this->dbConn->insert($this->tableName, [
            'type'           => $type,
            'source_id'      => $source->getId(),
            'destination_id' => $destinationId,
            'timestamp'      => $timestamp->format('Y-m-d H:i:s')
        ]);

        return new MigrateRecord($source->getId(), $destinationId, $type, $this->prepareTimestamp($timestamp));
    }

    /**
     * Record (or remove record) a revert action
     *
     * @param string $sourceId
     * @param string $type
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     */
    public function removeMigrateRecord(string $sourceId, string $type)
    {
        $this->dbConn->delete($this->tableName, ['type' => $type, 'source_id' => $sourceId]);
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
        $table->addColumn('type', 'string', ['length' => 64, 'notnull' => true]);
        $table->addColumn('source_id', 'string', ['length' => 128, 'notnull' => true]);
        $table->addColumn('destination_id', 'string', ['length' => 128, 'notnull' => true]);
        $table->addColumn('timestamp', 'datetime', ['notnull' => true]);

        $table->addColumn(
            'rowid',
            'integer',
            ['unsigned' => true, 'notnull' => true, 'autoincrement' => true]
        );
        $table->setPrimaryKey(['rowid']);
        $table->addUniqueIndex(['type', 'source_id']);
        $table->addUniqueIndex(['type', 'destination_id']);

        $queries = $schema->toSql($this->dbConn->getDatabasePlatform());
        foreach ($queries as $query) {
            $this->dbConn->query($query);
        }
    }

    /**
     * Count migrated records for type
     *
     * @param string $type
     * @return int|null
     */
    public function countRecords(string $type): ?int
    {
        $qb = $this->dbConn->createQueryBuilder();
        $qb->select('COUNT(t.source_id)')->from($this->tableName, 't');
        $qb->where($qb->expr()->eq('t.type', ':type'));
        $qb->setParameter(':type', $type);
        $stmt = $qb->execute();
        return (int) $stmt->fetchColumn();
    }


    /**
     * Resolve date/time object for date/time string
     *
     * @noinspection PhpDocMissingThrowsInspection
     *
     * @param string|\DateTimeInterface $timestamp
     * @return \DateTimeInterface
     */
    private function prepareTimestamp($timestamp)
    {
        return $timestamp instanceof \DateTimeInterface ? $timestamp : new \DateTimeImmutable($timestamp);
    }
}
