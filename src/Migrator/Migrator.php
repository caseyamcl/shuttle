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

use Shuttle\Migrator\Behavior\HasDestinationTrait;
use Shuttle\Migrator\Behavior\HasSourceTrait;

/**
 * Base Migrator
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class Migrator implements MigratorInterface
{
    use HasSourceTrait;
    use HasDestinationTrait;

    /**
     * @var string
     */
    private $name;

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
     * @param string $name
     * @param SourceInterface $source
     * @param DestinationInterface $destination
     * @param string $description
     * @param array|string[] $dependsOn  An array of slugs that should be migrated before this
     */
    public function __construct(
        string $name,
        SourceInterface $source,
        DestinationInterface $destination,
        $description = '',
        array $dependsOn = []
    ) {
        // Set the simple stuff first..
        $this->setName($name);
        $this->setDescription($description);
        $this->dependsOn = $dependsOn;

        // ..and this stuff second.
        $this->source      = $source;
        $this->destination = $destination;
    }

    /**
     * @return string  A unique identifier for the type of record being migrated
     */
    public function getName(): string
    {
        return $this->name;
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
    protected function setName($slug): Migrator
    {
        $this->name = $slug;
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
     * @param array $source
     * @return mixed
     */
    public function prepareSourceItem(array $source)
    {
        // By default, do nothing to the record..
        return $source;
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
        return $this->getName();
    }

    /**
     * @return DestinationInterface
     */
    public function getDestination(): DestinationInterface
    {
        return $this->destination;
    }

    /**
     * @return SourceInterface
     */
    public function getSource(): SourceInterface
    {
        return $this->source;
    }


}
