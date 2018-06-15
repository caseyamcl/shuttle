<?php

namespace Shuttle\MigrateSource;

use phpDocumentor\Reflection\DocBlock\Tags\Source;
use Shuttle\Exception\MissingItemException;
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
     * Set during runtime
     *
     * @var array|SourceInterface[]
     */
    private $items;

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
     * @return iterable|SourceItem[]
     */
    public function getSourceIdIterator(): iterable
    {
        if ($this->keysAreIds) {
            return array_keys($this->getItems());
        } else {
            return array_map(function(SourceItem $item) {
                return $item->getId();
            }, $this->getItems());
        }
    }

    /**
     * @param string $id
     * @return SourceItem
     * @throws MissingItemException  If source item is not found
     */
    public function getSourceItem(string $id): SourceItem
    {
        if ($this->keysAreIds && array_key_exists($id, $this->getItems()))
            return $this->getItems()[$id];
        elseif (! $this->keysAreIds) {
            foreach ($this->getItems() as $item) {
                if ($item->getId() == $id) {
                    return $item;
                }
            }
        }

        // If made it here..
        throw new MissingItemException('Missing Item: ' . $id);

    }

    /**
     * Initialize the items as an array if not already done.
     *
     * @return array|SourceItem[]
     */
    private function getItems(): array
    {
        if (is_null($this->items)) {

            $items = call_user_func($this->getItems);
            $this->items = is_array($items) ? $items : iterator_to_array($items, true);
        }

        return $this->items;
    }

}