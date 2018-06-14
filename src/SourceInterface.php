<?php

namespace Shuttle;

use Shuttle\Exception\MissingItemException;

/**
 * Source Interface
 *
 * @package Shuttle\NewShuttle
 */
interface SourceInterface
{
    /**
     * If is countable, return the number of source items, or NULL if unknown
     * @return int|null
     */
    public function countSourceItems(): ?int;

    /**
     * @param string $id
     * @return SourceItem
     * @throws MissingItemException  If source item is not found
     */
    public function getSourceItem(string $id): SourceItem;

    /**
     * Get the next source record, represented as an array
     *
     * Return an array for the next item, or NULL for no more item
     *
     * @return iterable|string[]
     */
    public function getSourceIdIterator(): iterable;
}
