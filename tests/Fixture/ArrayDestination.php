<?php

namespace ShuttleTest\Fixture;

use Shuttle\DestinationInterface;

/**
 * Class ArrayDestination
 * @package ShuttleTest\Fixture
 */
class ArrayDestination implements DestinationInterface
{
    /**
     * @var array
     */
    private $items;

    /**
     * ArrayDestination constructor.
     * @param array $items
     */
    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    /**
     * @param mixed $preparedItem
     * @return string  Destination Id
     */
    public function persist($preparedItem): string
    {
        $key = count($this->items);
        $this->items[$key] = $preparedItem;
        return $key;
    }

    /**
     * @param string $destinationId
     * @throws \RuntimeException  Throw exception if destination not found
     */
    public function remove(string $destinationId)
    {
        if (array_key_exists($destinationId, $this->items)) {
            unset($this->items[$destinationId]);
        }
    }
}
