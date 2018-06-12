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
     * @throws \RuntimeException  Throw exception if destination not found
     */
    public function remove(string $destinationId);
}