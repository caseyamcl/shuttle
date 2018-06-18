<?php

namespace ShuttleTest\MigrateSource;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Schema\Table;
use Shuttle\SourceInterface;
use ShuttleTest\AbstractSourceInterfaceTest;

/**
 * Class DoctrineQuerySource
 * @package ShuttleTest\MigrateSource
 */
class DoctrineQuerySourceTest extends AbstractSourceInterfaceTest
{
    /**
     * @var Connection
     */
    private $dbConn;

    public function setUp()
    {
        parent::setUpBeforeClass();

        if (!extension_loaded('pdo_sqlite')) {
            self::markTestSkipped('No SQLite PDO Available');
        }

        $this->dbConn = DriverManager::getConnection(['url' => 'sqlite:///:memory:']);
        $schemaManager = $this->dbConn->getSchemaManager();

        $table = new Table('items');
        $table->addColumn('id', 'integer', ['nullable' => false]);
        $table->addColumn('name', 'string', ['nullable' => false]);
        $table->setPrimaryKey(['id']);
        $schemaManager->createTable($table);

        $this->dbConn->insert('items', ['id' => 100, 'name' => 'bob']);
        $this->dbConn->insert('items', ['id' => 200, 'name' => 'sally']);
        $this->dbConn->insert('items', ['id' => 300, 'name' => 'roy']);
    }

    /**
     * @param QueryBuilder|null $qb
     * @param string $idColumn
     * @return SourceInterface
     */
    protected function getSourceObj(?QueryBuilder $qb = null, string $idColumn = 't.id'): SourceInterface
    {
        if (! $qb) {
            $qb = $this->dbConn->createQueryBuilder();
            $qb->select('t.*')->from('items', 't');
        }

        return new \Shuttle\MigrateSource\DoctrineQuerySource($qb, 't.id');
    }

    /**
     * @return string
     */
    protected function getExistingRecordId(): string
    {
        return 100;
    }

    /**
     * @return string
     */
    protected function getNonExistentRecordId(): string
    {
        return 400;
    }

    /**
     * @return int|null
     */
    protected function getExpectedCount(): ?int
    {
        return 3;
    }
}
