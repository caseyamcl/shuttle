<?php

namespace ShuttleTest\Fixture;

use Shuttle\Exception\MissingItemException;
use Shuttle\SourceInterface;
use Shuttle\SourceItem;

/**
 * Class ArraySource
 * @package ShuttleTest\Fixture
 */
class ArraySource implements SourceInterface
{
    const DEFAULT_ITEMS = [['a', 'A'], ['b', 'B'], ['c', 'C']];

    /**
     * @var array
     */
    private $items;

    /**
     * ArraySource constructor.
     * @param array $items
     */
    public function __construct(array $items = self::DEFAULT_ITEMS)
    {
        $this->items = $items;
    }


    /**
     * If is countable, return the number of source items, or NULL if unknown
     * @return int|null
     */
    public function countSourceItems(): ?int
    {
        return count($this->items);
    }

    /**
     * Get the next source record, represented as an array
     *
     * Return an array for the next item, or NULL for no more item
     *
     * @return iterable|SourceItem[]
     */
    public function getSourceIterator(): iterable
    {
        foreach ($this->items as $id => $item) {
            yield new SourceItem($id, $item);
        }
    }

    /**
     * @param string $id
     * @return SourceItem
     * @throws \Exception  If source item is not found
     */
    public function getSourceItem(string $id): SourceItem
    {
        if (array_key_exists($id, $this->items)) {
            return new SourceItem($id, $this->items[$id]);
        } else {
            throw new MissingItemException('Missing Item: ' . $id);
        }
    }
}
