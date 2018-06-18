<?php

namespace Shuttle\MigrateSource;

use Doctrine\DBAL\Query\QueryBuilder;
use phpDocumentor\Reflection\DocBlock\Tags\Source;
use Shuttle\Exception\MissingItemException;
use Shuttle\SourceIdIterator;
use Shuttle\SourceInterface;
use Shuttle\SourceItem;

/**
 * Class DoctrineTableSource
 * @package Shuttle\MigrateSource
 */
class DoctrineQuerySource implements SourceInterface
{
    /**
     * @var QueryBuilder
     */
    private $queryBuilder;

    /**
     * @var string
     */
    private $idColumn;

    /**
     * DoctrineTableSource constructor.
     *
     * @param QueryBuilder $queryBuilder  Query builder
     * @param string $idColumn            Column name (or expression) with table prefix (e.g "t.id")
     */
    public function __construct(QueryBuilder $queryBuilder, string $idColumn)
    {
        $this->queryBuilder = $queryBuilder;
        $this->idColumn = $idColumn;
    }

    /**
     * If is countable, return the number of source items, or NULL if unknown
     * @return int|null
     */
    public function countSourceItems(): ?int
    {
        $qb = clone $this->queryBuilder;
        $qb->resetQueryPart('select');
        $qb->select(sprintf('COUNT(%s)', $this->idColumn));
        $stmt = $qb->execute();
        return $stmt->fetchColumn(0);
    }

    /**
     * @param string $id
     * @return SourceItem
     * @throws MissingItemException  If source item is not found
     * @throws \Exception
     */
    public function getSourceItem(string $id): SourceItem
    {
        $qb = clone $this->queryBuilder;
        $qb->andWhere($qb->expr()->eq($this->idColumn, ':id'));
        $qb->setParameter(':id', $id);
        $stmt = $qb->execute();

        if ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            return new SourceItem($id, $row);
        } else {
            throw MissingItemException::forId($id);
        }
    }

    /**
     * Get the next source record, represented as an array
     *
     * Return an array for the next item, or NULL for no more item
     *
     * @return SourceIdIterator|string[]
     */
    public function getSourceIdIterator(): SourceIdIterator
    {
        $qb = clone $this->queryBuilder;
        $qb->resetQueryPart('select');
        $qb->select($this->idColumn);
        $stmt = $qb->execute();
        $stmt->setFetchMode(\PDO::FETCH_COLUMN, 0);
        return new SourceIdIterator($stmt, $this->countSourceItems());
    }
}
