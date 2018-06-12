<?php

namespace Shuttle;

/**
 * Class SourceItem
 * @package Shuttle
 */
class SourceItem implements \ArrayAccess
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var array
     */
    private $data;

    /**
     * SourceItem constructor.
     * @param string $id
     * @param array $fields
     */
    public function __construct(string $id, array $fields)
    {
        $this->id = $id;
        $this->data = $fields;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->data);
    }

    /**
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        return $this->data[$offset];
    }

    /**
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        throw new \RuntimeException(get_called_class() . ' is immutable');
    }

    /**
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        throw new \RuntimeException(get_called_class() . ' is immutable');
    }
}