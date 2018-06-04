<?php

namespace Shuttle\MigrateDestination;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\ObjectManager;
use Shuttle\Migrator\DestinationInterface;
use Shuttle\Migrator\Exception\MissingItemException;

/**
 * Class DoctrineEntityDestination
 * @package Shuttle\MigrateDestination
 */
class DoctrineDestination implements DestinationInterface
{
    /**
     * @var ObjectManager
     */
    private $manager;

    /**
     * @var string
     */
    private $className;

    /**
     * @var bool
     */
    private $autoFlush;

    /**
     * @var ClassMetadata
     */
    private $metadata;

    /**
     * DoctrineDestination constructor.
     *
     * @param ObjectManager $manager
     * @param string $className
     * @param bool $autoFlush  TRUE to persist each record immediately
     */
    public function __construct(ObjectManager $manager, string $className, bool $autoFlush = true)
    {
        $this->manager = $manager;
        $this->className = $className;
        $this->autoFlush = $autoFlush;
        $this->metadata = $manager->getClassMetadata($className);
    }

    /**
     * Does the destination contain the record?
     *
     * @param string $destinationId The destination ID
     * @return bool
     * @throws MissingItemException
     */
    public function hasItem(string $destinationId): bool
    {
        return (bool) $this->manager->find($this->className, $destinationId);
    }

    /**
     * Save a record
     *
     * Create or update the record
     *
     * @param object $recordData  An entity
     * @return string  The ID of the inserted record
     */
    public function saveItem($recordData): string
    {
        $this->manager->persist($recordData);
        $this->manager->flush();
        return implode('', $this->metadata->getIdentifierValues($recordData));
    }

    /**
     * Remove a record
     *
     * @param string $destinationId
     * @return bool  If a record existed to be deleted, returns TRUE, else FALSE
     */
    public function deleteItem(string $destinationId): bool
    {
        if ($rec = $this->manager->find($this->className, $destinationId)) {
            $this->manager->remove($rec);
            return true;
        } else {
            return false;
        }
    }
}
