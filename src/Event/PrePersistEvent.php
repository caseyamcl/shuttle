<?php

namespace Shuttle\Event;
use Shuttle\SourceItem;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class PrePersistEvent
 * @package Shuttle\Event
 */
class PrePersistEvent extends Event
{
    /**
     * @var SourceItem
     */
    private $item;

    /**
     * @var mixed
     */
    private $preparedValue;

    /**
     * @var string
     */
    private $migratorName;

    /**
     * PrePersistEvent constructor.
     *
     * @param SourceItem $item
     * @param mixed $preparedValue
     * @param string $migratorName
     */
    public function __construct(SourceItem $item, $preparedValue, string $migratorName)
    {
        $this->item = $item;
        $this->preparedValue = $preparedValue;
        $this->migratorName = $migratorName;
    }

    /**
     * @return SourceItem
     */
    public function getItem(): SourceItem
    {
        return $this->item;
    }

    /**
     * @return mixed
     */
    public function getPreparedValue()
    {
        return $this->preparedValue;
    }

    /**
     * @return string
     */
    public function getMigratorName(): string
    {
        return $this->migratorName;
    }
}