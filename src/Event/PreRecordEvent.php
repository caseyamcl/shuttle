<?php

namespace Shuttle\Event;

use Shuttle\ShuttleAction;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class PreRecordEvent
 * @package Shuttle\Event
 */
class PreRecordEvent extends Event
{
    const MIGRATE = 'migrate';
    const REVERT = 'revert';

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
    private $destinationId;

    /**
     * @var string
     */
    private $action;

    /**
     * PreRecordEvent constructor.
     *
     * @param string $migratorName
     * @param string $sourceId
     * @param string $destinationId
     * @param string $action  Either 'revert' or 'migrate'
     */
    public function __construct(string $migratorName, string $sourceId, string $destinationId, string $action)
    {
        $this->migratorName = $migratorName;
        $this->sourceId = $sourceId;
        $this->destinationId = $destinationId;

        if (! ShuttleAction::isValidAction($action)) {
            throw new \InvalidArgumentException('Invalid action: '. $action);
        }

        $this->action = $action;
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
    public function getDestinationId(): string
    {
        return $this->destinationId;
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }
}
