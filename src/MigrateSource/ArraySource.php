<?php

namespace Shuttle\MigrateSource;

use Shuttle\Exception\MissingItemException;
use Shuttle\SourceInterface;
use Shuttle\SourceItem;

/**
 * Class ArraySource
 * @package ShuttleTest\Fixture
 */
class ArraySource implements SourceInterface
{
    /**
     * @var array
     */
    private $items;

    /**
     * ArraySource constructor.
     * @param iterable $items
     */
    public function __construct(iterable $items)
    {
        $this->items = ($items instanceOf \Traversable) ? iterator_to_array($items) : $items;
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
            yield new SourceItem((string) $id, $item);
        }
    }

    /**
     * @param string $id
     * @return SourceItem
     * @throws MissingItemException  If source item is not found
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
