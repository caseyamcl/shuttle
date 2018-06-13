<?php

namespace Shuttle\MigrateDestination;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;
use Shuttle\DestinationInterface;

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
     * Save a record
     *
     * Create or update the record
     *
     * @param object $preparedItem An entity
     * @return string  The ID of the inserted record
     */
    public function persist($preparedItem): string
    {
        $this->manager->persist($preparedItem);

        if ($this->autoFlush) {
            $this->manager->flush();
        }

        // Get the field from the entity/object
        if ($this->idFieldName) {
            $reflection = ClassUtils::newReflectionObject($preparedItem)->getProperty($this->idFieldName);
            $reflection->setAccessible(true);
            return $reflection->getValue($preparedItem);
        } else {
            return implode('', array_map('strval', $this->metadata->getIdentifierValues($preparedItem)));
        }
    }

    /**
     * @param string $destinationId
     * @return bool  TRUE if record was found and removed, FALSE if record not found
     */
    public function remove(string $destinationId): bool
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
