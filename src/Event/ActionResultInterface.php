<?php

namespace Shuttle\Event;

/**
 * Class PostActionEventInterface
 * @package Shuttle\Event
 */
interface ActionResultInterface
{
    const FAILED = 'failed';
    const PROCESSED = 'processed';
    const SKIPPED = 'skipped';

    /**
     * @return string
     */
    public function getAction(): string;

    /**
     * @return string
     */
    public function getMigratorName(): string;

    /**
     * @return string
     */
    public function getStatus(): string;
}