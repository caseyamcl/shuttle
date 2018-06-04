<?php

namespace Shuttle\Migrator\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * MigratePrePrepareEvent
 *
 *
 * This event is dispatched after retrieving the source item, but before preparing item for the destination.
 * Throw any RuntimeException here to fail the migration of this item
 *
 * @package Shuttle\Migrator\Event
 */
class MigratePrePrepareEvent extends Event
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $sourceId;

    /**
     * @var array
     */
    private $item;

    /**
     * MigratePrePersistEvent constructor.
     *
     * @param string $type      The type of item being migrated
     * @param string $sourceId  The source ID
     * @param array $item       The source item, before being prepared for destination
     */
    public function __construct(string $type, string $sourceId, array $item)
    {
        $this->type = $type;
        $this->sourceId = $sourceId;
        $this->item = $item;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getSourceId(): string
    {
        return $this->sourceId;
    }

    /**
     * @return array
     */
    public function getItem(): array
    {
        return $this->item;
    }
}
