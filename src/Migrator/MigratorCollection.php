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

use ArrayIterator;
use Countable;
use MJS\TopSort\Implementations\StringSort;

/**
 * Class MigratorCollection
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class MigratorCollection implements \IteratorAggregate, Countable
{
    /**
     * @var array|MigratorInterface[]
     */
    private $migrators = [];

    /**
     * @var StringSort
     */
    private $sorter;

    /**
     * Constructor
     *
     * @param iterable|MigratorInterface[] $migrators
     */
    public function __construct(iterable $migrators = [])
    {
        $this->sorter = new StringSort();
        foreach ($migrators as $migrator) {
            $this->add($migrator);
        }
    }

    /**
     * @param string $name
     * @return bool
     */
    public function has($name): bool
    {
        return array_key_exists($name, $this->migrators);
    }

    /**
     * @param MigratorInterface $migrator
     */
    public function add(MigratorInterface $migrator): void
    {
        $this->migrators[$migrator->getSlug()] = $migrator;
        $this->sorter->add($migrator->getSlug(), $migrator->getDependsOn());
    }

    /**
     * @param string $name
     * @return MigratorInterface
     */
    public function get($name): MigratorInterface
    {
        if (! $this->has($name)) {
            throw new \InvalidArgumentException("No migrator exists with slug/name: " . $name);
        }

        return $this->migrators[$name];
    }

    /**
     * @return ArrayIterator|MigratorInterface[]
     * @throws \MJS\TopSort\CircularDependencyException
     * @throws \MJS\TopSort\ElementNotFoundException
     */
    public function getIterator(): \Iterator
    {
        return new ArrayIterator(array_map([$this, 'get'], $this->sorter->sort()));
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->migrators);
    }

    /**
     * Resolve dependencies for a given migrator slug or instance
     *
     * @param string|MigratorInterface $migrator The migrator or name of the migrator
     * @return iterable|MigratorInterface[]  A list of migrators in the order they must be processed
     * @throws \MJS\TopSort\CircularDependencyException
     * @throws \MJS\TopSort\ElementNotFoundException
     */
    public function resolveDependencies(string $migrator): iterable
    {
        if (! $migrator instanceof MigratorInterface) {
            $migrator = $this->get($migrator);
        }

        $sorter = new StringSort();
        foreach ($this->iterateDependencies($migrator) as $dependency) {
            $sorter->add($dependency->getSlug(), $dependency->getDependsOn());
        }

        return array_map([$this, 'get'], $sorter->sort());
    }

    /**
     * Resolve dependencies for a migrator (in no particular order)
     *
     * Recursive method walks dependency graph and adds each dependent migrator exactly once.
     *
     * @param MigratorInterface $migrator      The migrator to resolve dependencies for
     * @param array $visited                   Visited migrators (only need to add each one once)
     * @return \Generator|MigratorInterface[]
     * @throws \RuntimeException  If recursion depth exceeds the maximum recursion allowed
     */
    private function iterateDependencies(MigratorInterface $migrator, array $visited = [])
    {
        if (! in_array($migrator->getSlug(), $visited)) {
            $visited[] = $migrator->getSlug();
            yield $migrator;
        }

        foreach ($migrator->getDependsOn() as $dependency) {
            yield from $this->iterateDependencies($this->get($dependency), $visited);
        }
    }
}
