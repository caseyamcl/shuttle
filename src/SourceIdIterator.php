<?php

namespace Shuttle;

/**
 * Class SourceItemIterator
 * @package Shuttle
 */
class SourceIdIterator extends \IteratorIterator implements \Countable
{
    /**
     * @var int
     */
    private $count;

    /**
     * Source Item Iterator
     *
     * @param iterable $items Either items are instances of SourceItem or keys are Ids
     * @param int|null $count
     */
    public function __construct(iterable $items, ?int $count = null)
    {
        parent::__construct($items instanceof \Traversable ? $items : new \ArrayIterator($items));
        $this->count = $count;
    }

    /**
     * Return the current ID as a string
     *
     * @return string
     */
    public function current(): string
    {
        return parent::current();
    }

    /**
     * @return int|null
     */
    public function count(): ?int
    {
        if (! is_null($this->count)) {
            return $this->count;
        } elseif (is_callable([$this->getInnerIterator(), 'count'])) {
            return call_user_func([$this->getInnerIterator(), 'count']);
        } else {
            return iterator_apply($this, function () {
            });
        }
    }
}
