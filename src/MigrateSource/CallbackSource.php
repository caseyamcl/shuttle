<?php

namespace Shuttle\MigrateSource;

use Shuttle\Exception\MissingItemException;
use Shuttle\SourceIdIterator;
use Shuttle\SourceInterface;
use Shuttle\SourceItem;

/**
 * Class CallbackSource
 * @package Shuttle\MigrateSource
 */
class CallbackSource implements SourceInterface
{
    /**
     * @var callable
     */
    private $getItems;


    /**
     * @var bool
     */
    private $keysAreIds;

    /**
     * Callback source
     *
     * @param callable $getItems  Must return an interator of SourceItem instances
     * @param bool $keysAreIds  TRUE if keys in resulting array are IDs (improves performance)
     */
    public function __construct(callable $getItems, bool $keysAreIds = true)
    {
        $this->getItems = $getItems;
        $this->keysAreIds = $keysAreIds;
    }

    /**
     * @return int|null
     */
    public function countSourceItems(): ?int
    {
        return count($this->getItems());
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
        $items = $this->getItems();

        if ($this->keysAreIds) {
            $iterator = array_keys($items);
        } else {
            $iterator = array_map(function (SourceItem $item) {
                return $item->getId();
            }, $items);
        }

        return new SourceIdIterator($iterator);
    }

    /**
     * @param string $id
     * @return SourceItem
     * @throws MissingItemException  If source item is not found
     */
    public function getSourceItem(string $id): SourceItem
    {
        $items = $this->getItems();

        if ($this->keysAreIds && array_key_exists($id, $items)) {
            return $items[$id];
        } elseif (! $this->keysAreIds) {
            foreach ($items as $item) {
                if ($item->getId() == $id) {
                    return $item;
                }
            }
        }

        // If made it here..
        throw MissingItemException::forId($id);
    }

    /**
     * Initialize the items as an array if not already done.
     *
     * @return array|SourceItem[]
     */
    private function getItems(): array
    {
        $items = call_user_func($this->getItems);
        return is_array($items) ? $items : iterator_to_array($items, true);
    }
}
