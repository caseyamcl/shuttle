<?php

namespace Shuttle\Event;

use Shuttle\ShuttleAction;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class MigrateFailedEvent
 * @package Shuttle\Event
 */
class MigrateFailedEvent extends Event implements ActionResultInterface
{
    /**
     * @var string
     */
    private $migratorName;

    /**
     * @var string
     */
    private $sourceId;

    /**
     * @var string
     */
    private $reason;

    /**
     * @var null|\Throwable
     */
    private $exception;

    /**
     * RevertFailedEvent constructor.
     * @param string $migratorName
     * @param string $sourceId
     * @param string $reason
     * @param null|\Throwable $exception
     */
    public function __construct(string $migratorName, string $sourceId, string $reason, ?\Throwable $exception = null)
    {
        $this->migratorName = $migratorName;
        $this->sourceId = $sourceId;
        $this->reason = $reason;
        $this->exception = $exception;
    }

    /**
     * @return string
     */
    public function getMigratorName(): string
    {
        return $this->migratorName;
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
    public function getReason(): string
    {
        return $this->reason;
    }

    /**
     * @return null|\Throwable
     */
    public function getException(): ?\Throwable
    {
        return $this->exception;
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return ShuttleAction::MIGRATE;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return static::FAILED;
    }
}