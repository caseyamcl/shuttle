<?php
/**
 * ticketmove
 *
 * @license ${LICENSE_LINK}
 * @link ${PROJECT_URL_LINK}
 * @version ${VERSION}
 * @package ${PACKAGE_NAME}
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * ------------------------------------------------------------------
 */

namespace Shuttle\Service\Migrator;

use ArrayIterator, Countable;

/**
 * Class MigratorCollection
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class MigratorCollection implements \IteratorAggregate, \Countable
{
    /**
     * @var array|MigratorInterface[]
     */
    private $migrators;

    // ---------------------------------------------------------------

    /**
     * Constructor
     *
     * @param MigratorInterface[] $migrators
     */
    public function __construct($migrators = [])
    {
        $this->migrators = [];

        foreach ($migrators as $migrator) {
            $this->add($migrator);
        }
    }

    // ---------------------------------------------------------------

    /**
     * @param string $name
     * @return bool
     */
    public function has($name)
    {
        return array_key_exists($name, $this->migrators);
    }

    // ---------------------------------------------------------------

    /**
     * @param MigratorInterface $migrator
     */
    public function add(MigratorInterface $migrator)
    {
        $this->migrators[$migrator->getSlug()] = $migrator;
    }

    // ---------------------------------------------------------------

    /**
     * @param string $name
     * @return MigratorInterface
     */
    public function get($name)
    {
        if ( ! $this->has($name)) {
            throw new \InvalidArgumentException("No migrator exists with slug/name: " . $name);
        }

        return $this->migrators[$name];
    }

    // ---------------------------------------------------------------

    /**
     * @return ArrayIterator|MigratorInterface[]
     */
    public function getIterator()
    {
        return new ArrayIterator($this->migrators);
    }

    // ---------------------------------------------------------------

    /**
     * @return int
     */
    public function count()
    {
        return count($this->migrators);
    }
}
