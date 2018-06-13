<?php

namespace ShuttleTest\Fixture;

/**
 * Class TestEntity
 * @package ShuttleTest\Fixture
 */
class TestDoctrineEntity
{
    /**
     * @var string

     */
    private $identifier;

    /**
     * @var string
     */
    private $name;

    /**
     * TestEntity constructor.
     * @param string $identifier
     * @param string $name
     */
    public function __construct(string $identifier, string $name)
    {
        $this->identifier = $identifier;
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}