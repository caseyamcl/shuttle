<?php

namespace Shuttle\MigrateSource;

use Shuttle\Exception\MissingItemException;
use Shuttle\SourceInterface;
use Shuttle\SourceItem;

/**
 * Class ArraySource
 * @package ShuttleTest\Fixture
 */
class ArraySource extends CallbackSource
{
    /**
     * ArraySource constructor.
     * 
     * TODO: MOVE THE LOGIC TO REALIZE THIS INTO THE getSourceIdIterator(), so that method becomes a factory for the iterator.
     * Will also need to update getItems($id) to be able to search the iterable for the item.  Add back $keysAreIds parameter
     * 
     * @param iterable|array|\Traversable $items  Keys/indicies must be IDs
     */
    public function __construct(iterable $items)
    {
        $getItemsCallback = function() use ($items) {
            foreach ($items as $itemId => $itemData) {
                $out[$itemId] = ($itemData instanceOf SourceItem) ? $itemData : new SourceItem($itemId, $itemData);
            }
            return $out ?? [];
        };

        parent::__construct($getItemsCallback, true);
    }
}
