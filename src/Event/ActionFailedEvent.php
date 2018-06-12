<?php

namespace Shuttle\Event;

use Shuttle\ShuttleAction;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class ActionFailedEvent
 *
 * @package Shuttle\Event
 */
class ActionFailedEvent extends Event
{
    /**
     * @var string
     */
    private $action;

    /**
     * @var \Throwable
     */
    private $exception;

    /**
     * @var string
     */
    private $sourceId;

    /**
     * @var string|null
     */
    private $destinationId;

    /**
     * ActionFailedEvent constructor.
     * @param string $action
     * @param \Throwable $exception
     * @param string $sourceId
     * @param null|string $destinationId
     */
    public function __construct(string $action, \Throwable $exception, string $sourceId, ?string $destinationId = null)
    {
        $this->action = $action;
        $this->exception = $exception;
        $this->sourceId = $sourceId;
        $this->destinationId = $destinationId;

        if (! ShuttleAction::isValidAction($action)) {
            throw new \InvalidArgumentException("Invalid action:" . $action);
        }
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * @return \Throwable
     */
    public function getException(): \Throwable
    {
        return $this->exception;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->exception->getMessage();
    }

    /**
     * @return string
     */
    public function getSourceId(): string
    {
        return $this->sourceId;
    }

    /**
     * @return null|string
     */
    public function getDestinationId(): ?string
    {
        return $this->destinationId;
    }
}