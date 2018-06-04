<?php

namespace Shuttle\Migrator\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * MigratePrePersistEvent
 *
 * This event is dispatched after preparing the item, but before persisting the item to the destination.
 * Throw any RuntimeException here to fail the migration of this item
 *
 * @package Shuttle\Migrator\Event
 */
class MigratePrePersistEvent extends Event
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
     * @var mixed
     */
    private $item;

    /**
     * MigratePrePersistEvent constructor.
     *
     * @param string $type      The type of item being migrated
     * @param string $sourceId  The source ID
     * @param mixed $item       The prepared item
     */
    public function __construct(string $type, string $sourceId, $item)
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
     * @return mixed
     */
    public function getItem()
    {
        return $this->item;
    }
}
