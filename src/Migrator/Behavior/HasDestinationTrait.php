<?php

namespace Shuttle\Migrator\Behavior;

use Shuttle\Migrator\DestinationInterface;

/**
 * Class HasDestinationTrait
 * @package Shuttle\Migrator\Behavior
 */
trait HasDestinationTrait
{
    /**
     * @return DestinationInterface
     */
    abstract public function getDestination(): DestinationInterface;

    /**
     * @param mixed $record
     * @return string
     */
    public function persistDestinationItem($record): string
    {
        return $this->getDestination()->saveItem($record);
    }

    /**
     * Revert a single record
     *
     * @param string $destinationRecordId
     * @return bool
     */
    public function removeDestinationItem(string $destinationRecordId): bool
    {
        return $this->getDestination()->deleteItem($destinationRecordId);
    }

}