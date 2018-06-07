<?php

namespace ShuttleTest\Fixture;
use Shuttle\Migrator\DestinationInterface;

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
     * Does the destination contain the given record?
     *
     * @param string $destinationId The destination ID
     * @return bool  Record, represented as array
     */
    public function hasItem(string $destinationId): bool
    {
        return array_key_exists($destinationId, $this->items);
    }

    /**
     * Save a record
     *
     * Create or update the record
     *
     * @param mixed $recordData
     * @return string  The ID of the inserted record
     */
    public function saveItem($recordData): string
    {
        $key = count($this->items);
        $this->items[$key] = $recordData;
        return $key;
    }

    /**
     * Remove a record
     *
     * @param string $destinationId
     * @return bool  If a record existed to be deleted, returns TRUE, else FALSE
     */
    public function deleteItem(string $destinationId): bool
    {
        if (array_key_exists($destinationId, $this->items)) {
            unset($this->items[$destinationId]);
            return true;
        } else {
            return false;
        }
    }
}