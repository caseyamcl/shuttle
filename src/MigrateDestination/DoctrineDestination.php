<?php

namespace Shuttle\MigrateDestination;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;
use Shuttle\Exception\MissingItemException;

/**
 * Class DoctrineEntityDestination
 * @package Shuttle\MigrateDestination
 */
class DoctrineDestination implements DestinationInterface
{
    const AUTO = null;

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
     * @var string
     */
    private $idFieldName;

    /**
     * DoctrineDestination constructor.
     *
     * @param ObjectManager $manager  A Doctrine object manager (EntityManager, DocumentManager, etc)
     * @param string $className       The Doctrine class
     * @param string $idFieldName     The field to use to track IDs (must be unique); leave NULL to use Doctrine's
     *                                ID field (i.e. the field you set in the mapping data as '@Id')
     * @param bool $autoFlush         Set to TRUE to persist each record immediately; false
     */
    public function __construct(
        ObjectManager $manager,
        string $className,
        string $idFieldName = self::AUTO,
        bool $autoFlush = true
    ) {
        $this->manager = $manager;
        $this->className = $className;
        $this->autoFlush = $autoFlush;
        $this->metadata = $manager->getClassMetadata($className);
        $this->idFieldName = $idFieldName;
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
        return (bool) $this->findItem($destinationId);
    }

    /**
     * Save a record
     *
     * Create or update the record
     *
     * @param object $recordData An entity
     * @return string  The ID of the inserted record
     */
    public function saveItem($recordData): string
    {
        $this->manager->persist($recordData);

        if ($this->autoFlush) {
            $this->manager->flush();
        }

        // Get the field from the entity/object
        if ($this->idFieldName) {
            $reflection = ClassUtils::newReflectionObject($recordData)->getProperty($this->idFieldName);
            $reflection->setAccessible(true);
            return $reflection->getValue($recordData);
        } else {
            return implode('', array_map('strval', $this->metadata->getIdentifierValues($recordData)));
        }
    }

    /**
     * Remove a record
     *
     * @param string $destinationId
     * @return bool  If a record existed to be deleted, returns TRUE, else FALSE
     */
    public function deleteItem(string $destinationId): bool
    {
        if ($rec = $this->findItem($destinationId)) {
            $this->manager->remove($rec);

            if ($this->autoFlush) {
                $this->manager->flush();
            }

            return true;
        } else {
            return false;
        }
    }

    /**
     * @param string $destinationId
     * @return null|object
     */
    private function findItem(string $destinationId)
    {
        return ($this->idFieldName)
            ? $this->manager->getRepository($this->className)->findOneBy([$this->idFieldName => $destinationId])
            : $this->manager->find($this->className, $destinationId);
    }
}
