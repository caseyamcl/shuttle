<?php

namespace Shuttle\MigrateDestination;

use Shuttle\DestinationInterface;
use Shuttle\Exception\MissingItemException;

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
        $key = (max(array_keys($this->items)) ?: 0) + 100;
        $this->items[$key] = $preparedItem;
        return (string) $key;
    }

    /**
     * @param string $destinationId
     * @return bool
     */
    public function remove(string $destinationId): bool
    {
        if (array_key_exists((int) $destinationId, $this->items)) {
            unset($this->items[$destinationId]);
            return true;
        } else {
            return false;
        }
    }
}
