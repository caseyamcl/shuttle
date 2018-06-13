<?php

namespace ShuttleTest\Fixture;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class RecordingEventDispatcher
 * @package ShuttleTest\Fixture
 */
class RecordingEventDispatcher extends EventDispatcher
{
    private $dispatchedEvents = [];

    public function dispatch($eventName, Event $event = null)
    {
        $result = parent::dispatch($eventName, $event);
        $this->dispatchedEvents[$eventName][] = $event;
        return $result;
    }

    /**
     * @param string $eventName
     * @return bool
     */
    public function eventWasDispatched(string $eventName): bool
    {
        return array_key_exists($eventName, $this->dispatchedEvents);
    }

    /**
     * @param string $eventName
     * @return null|Event
     */
    public function findFirstEvent(string $eventName): ?Event
    {
        return $this->dispatchedEvents[$eventName][0] ?? null;
    }

    /**
     * @param string $eventName
     * @param int $index
     * @return null|Event
     */
    public function findNthEvent(string $eventName, int $index): ?Event
    {
        return $this->dispatchedEvents[$eventName][$index] ?? null;
    }
}