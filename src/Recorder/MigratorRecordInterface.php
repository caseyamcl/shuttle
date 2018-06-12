<?php

namespace Shuttle\Recorder;

/**
 * Class MigratorReportItem
 * @package Shuttle\NewShuttle
 */
interface MigratorRecordInterface
{
    /**
     * @return string
     */
    public function getSourceId(): string;

    /**
     * @return string
     */
    public function getDestinationId(): string;

    /**
     * @return \DateTimeInterface
     */
    public function getTimestamp(): \DateTimeInterface;

    /**
     * Get the migrator name (must match Migrator::__toString())
     * @return string
     */
    public function getMigratorName(): string;
}