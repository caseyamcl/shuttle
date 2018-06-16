<?php

namespace Shuttle\MigrateSource;

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
     * @param iterable|array|\Traversable $items  Keys/indicies must be IDs
     */
    public function __construct(iterable $items)
    {
        $getItemsCallback = function () use ($items) {
            foreach ($items as $itemId => $itemData) {
                $out[$itemId] = ($itemData instanceof SourceItem) ? $itemData : new SourceItem($itemId, $itemData);
            }
            return $out ?? [];
        };

        parent::__construct($getItemsCallback, true);
    }
}
