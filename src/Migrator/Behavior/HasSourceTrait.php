<?php

namespace Shuttle\Migrator\Behavior;
use Shuttle\Migrator\SourceInterface;

/**
 * Class HasSourceTrait
 * @package Shuttle\Migrator\Behavior
 */
trait HasSourceTrait
{
    /**
     * @return int  Number of records in the source
     */
    public function countSourceItems(): int
    {
        return $this->getSource()->count();
    }

    /**
     * @param string $sourceId
     * @return array
     */
    public function getItemFromSource(string $sourceId): array
    {
        return $this->getSource()->getItem($sourceId);
    }

    /**
     * @return array|string[]
     */
    public function getSourceIdIterator(): iterable
    {
        return $this->getSource()->listItemIds();
    }

    /**
     * @return SourceInterface
     */
    abstract public function getSource(): SourceInterface;
}