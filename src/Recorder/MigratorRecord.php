<?php

namespace Shuttle\Recorder;

use Shuttle\Recorder\MigratorRecordInterface;

/**
 * Class MigratorRecord
 * @package Shuttle\NewShuttle
 */
class MigratorRecord implements MigratorRecordInterface
{
    /**
     * @var string
     */
    private $sourceId;

    /**
     * @var string
     */
    private $destinationId;

    /**
     * @var \DateTimeInterface
     */
    private $timestamp;

    /**
     * @var string
     */
    private $migratorName;

    /**
     * MigratorRecord constructor.
     * @param string $sourceId
     * @param string $destinationId
     * @param string $migratorName
     * @param \DateTimeInterface $timestamp
     * @throws \Exception
     */
    public function __construct(
        string $sourceId,
        string $destinationId,
        string $migratorName,
        ?\DateTimeInterface $timestamp = null
    ) {
        $this->sourceId = $sourceId;
        $this->destinationId = $destinationId;
        $this->migratorName = $migratorName;
        $this->timestamp = $timestamp ?: new \DateTimeImmutable();
    }


    /**
     * @return string
     */
    public function getSourceId(): string
    {
        return $this->sourceId;
    }

    /**
     * @return string
     */
    public function getDestinationId(): string
    {
        return $this->destinationId;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getTimestamp(): \DateTimeInterface
    {
        return $this->timestamp;
    }

    /**
     * Get the migrator name (must match Migrator::__toString())
     * @return string
     */
    public function getMigratorName(): string
    {
        return $this->migratorName;
    }
};