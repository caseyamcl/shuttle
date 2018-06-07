<?php

namespace ShuttleTest\Fixture;
use Shuttle\Migrator\Exception\MissingItemException;
use Shuttle\Migrator\SourceInterface;

/**
 * Class ArraySource
 * @package ShuttleTest\Fixture
 */
class ArraySource implements \IteratorAggregate, SourceInterface
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
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     * @since 5.1.0
     */
    public function count()
    {
        return count($this->items);
    }

    /**
     * @return iterable|string[]  Get a list of record IDs in the source
     */
    public function listItemIds(): iterable
    {
        return array_map('strval', array_keys($this->items));
    }

    /**
     * @param string $id The item ID to get
     * @return array  The item, represented as key/value associative array
     * @throws MissingItemException
     */
    public function getItem(string $id): array
    {
        if (array_key_exists($id, $this->items)) {
            return $this->items[$id];
        }
        else {
            throw new MissingItemException('Missing Item: ' . $id);
        }
    }

    /**
     * @return \ArrayIterator|\Traversable
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->items);
    }
}