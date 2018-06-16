<?php

namespace ShuttleTest\MigrateSource;

use Shuttle\MigrateSource\CallbackSource;
use Shuttle\SourceInterface;
use Shuttle\SourceItem;
use ShuttleTest\AbstractSourceInterfaceTest;

/**
 * Class CallbackSourceTest
 * @package ShuttleTest\MigrateSource
 */
class CallbackSourceTest extends AbstractSourceInterfaceTest
{
    /**
     * @return SourceInterface
     */
    protected function getSourceObj(): SourceInterface
    {
        return new CallbackSource(function () {
            yield new SourceItem('1', ['foo'  => 'bar']);
            yield new SourceItem('2', ['baz'  => 'biz']);
            yield new SourceItem('3', ['buzz' => 'fuzz']);
        }, false);
    }

    /**
     * @return string
     */
    protected function getExistingRecordId(): string
    {
        return '3';
    }

    /**
     * @return string
     */
    protected function getNonExistentRecordId(): string
    {
        return 4;
    }

    /**
     * @return int|null
     */
    protected function getExpectedCount(): ?int
    {
        return 3;
    }
}
