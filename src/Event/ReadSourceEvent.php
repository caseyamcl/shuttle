<?php

namespace Shuttle\Event;

use Shuttle\SourceItem;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class ReadSourceEvent
 * @package Shuttle\Event
 */
class ReadSourceEvent extends Event
{
    /**
     * @var SourceItem
     */
    private $sourceItem;

    /**
     * @var string
     */
    private $migratorName;

    /**
     * ReadSourceEvent constructor.
     *
     * @param SourceItem $sourceItem
     * @param string $migratorName
     */
    public function __construct(SourceItem $sourceItem, string $migratorName)
    {
        $this->sourceItem = $sourceItem;
        $this->migratorName = $migratorName;
    }

    /**
     * @return SourceItem
     */
    public function getSourceItem(): SourceItem
    {
        return $this->sourceItem;
    }

    /**
     * @return string
     */
    public function getMigratorName(): string
    {
        return $this->migratorName;
    }
}
