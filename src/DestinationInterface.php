<?php

namespace Shuttle;

/**
 * Class DestinationInterface
 * @package Shuttle\NewShuttle
 */
interface DestinationInterface
{
    /**
     * @param mixed $preparedItem
     * @return string  Destination Id
     */
    public function persist($preparedItem): string;

    /**
     * @param string $destinationId
     * @return bool  TRUE if record was found and removed, FALSE if record not found
     */
    public function remove(string $destinationId): bool;
}