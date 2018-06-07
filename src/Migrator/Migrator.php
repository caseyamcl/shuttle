<?php
/**
 * Shuttle Library
 *
 * @license https://opensource.org/licenses/MIT
 * @link https://github.com/caseyamcl/phpoaipmh
 * @package caseyamcl/shuttle
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * ------------------------------------------------------------------
 */

namespace Shuttle\Migrator;

/**
 * Base Migrator
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class Migrator implements MigratorInterface
{
    /**
     * @var string
     */
    private $slug;

    /**
     * @var string
     */
    private $description;

    /**
     * @var SourceInterface
     */
    private $source;

    /**
     * @var DestinationInterface
     */
    private $destination;

    /**
     * @var array|string[]
     */
    private $dependsOn;

    /**
     * Constructor
     *
     * @param string $slug
     * @param SourceInterface $source
     * @param DestinationInterface $destination
     * @param string $description
     * @param array|string[] $dependsOn  An array of slugs that should be migrated before this
     */
    public function __construct(
        string $slug,
        SourceInterface $source,
        DestinationInterface $destination,
        $description = '',
        array $dependsOn = []
    ) {
        $this->source      = $source;
        $this->destination = $destination;

        $this->setSlug($slug);
        $this->setDescription($description);
        $this->dependsOn = $dependsOn;
    }

    /**
     * @return string  A unique identifier for the type of record being migrated
     */
    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * @return string  A description of the records being migrated
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Set Slug
     *
     * @param string $slug
     * @return Migrator
     */
    protected function setSlug($slug): Migrator
    {
        $this->slug = $slug;
        return $this;
    }

    /**
     * Set Description
     *
     * @param string $description
     * @return Migrator
     */
    protected function setDescription($description): Migrator
    {
        $this->description = (string) $description;
        return $this;
    }

    /**
     * @return SourceInterface
     */
    public function getSource(): SourceInterface
    {
        return $this->source;
    }

    /**
     * @return DestinationInterface
     */
    public function getDestination(): DestinationInterface
    {
        return $this->destination;
    }

    /**
     * @return int  Number of records in the source
     */
    public function countSourceItems(): int
    {
        return $this->source->count();
    }

    /**
     * @param string $sourceId
     * @return array
     */
    public function getItemFromSource(string $sourceId): array
    {
        return $this->getSource()->getItem($sourceId);
    }

    /**
     * @param array $source
     * @return mixed
     */
    public function prepareSourceItem(array $source)
    {
        // By default, do nothing to the record..
        return $source;
    }

    /**
     * @param mixed $record
     * @return string
     */
    public function persistDestinationItem($record): string
    {
        return $this->destination->saveItem($record);
    }

    /**
     * Revert a single record
     *
     * @param string $destinationRecordId
     * @return bool
     */
    public function revert(string $destinationRecordId): bool
    {
        return $this->destination->deleteItem($destinationRecordId);
    }

    /**
     * @return array|string[]
     */
    public function listSourceIds(): iterable
    {
        return $this->source->listItemIds();
    }

    /**
     * Get other migrators that this migrator depends on
     *
     * NOTE: This is not a comprehensive; it does not list transitive dependencies.  Use
     * MigratorCollection::listDependencies() to determine all dependencies for a given migrator
     *
     * @return array|string[]
     */
    public function getDependsOn(): array
    {
        return $this->dependsOn;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getSlug();
    }


}
