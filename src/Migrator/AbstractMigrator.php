<?php

namespace Shuttle\Migrator;

/**
 * Class AbstractMigrator
 * @package Shuttle\NewShuttle
 */
abstract class AbstractMigrator implements MigratorInterface
{
    public const NAME        = null;
    public const DESCRIPTION = '';
    public const DEPENDS_ON  = [];

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->requireConstant('NAME');
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return static::DESCRIPTION;
    }

    /**
     * @return array|string[]
     */
    public function getDependsOn(): array
    {
        return static::DEPENDS_ON;
    }

    /**
     * @param string $constant
     * @return mixed
     * @throws \RuntimeException  If missing required constant
     */
    final protected function requireConstant(string $constant)
    {
        $val = constant(get_called_class() . '::' . $constant);

        $caller = debug_backtrace()[1]['function'];
        if (! $val) {
            throw new \RuntimeException(sprintf(
                '%s must implement constant %s or method %s',
                get_called_class(),
                $constant,
                $caller
            ));
        }

        return $val;
    }
}
