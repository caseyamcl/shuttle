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

namespace Shuttle;

use ArrayIterator;
use Countable;
use MJS\TopSort\Implementations\StringSort;
use Shuttle\Migrator\MigratorInterface;

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
     * Does the specified migrator
     *
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool
    {
        return array_key_exists($name, $this->migrators);
    }

    /**
     * @param MigratorInterface $migrator
     */
    public function add(MigratorInterface $migrator): void
    {
        $this->migrators[$migrator->__toString()] = $migrator;
        $this->sorter->add($migrator->__toString(), $migrator->getDependsOn());
    }

    /**
     * Get a migrator bits name
     *
     * @param string $name
     * @return MigratorInterface
     * @throws \InvalidArgumentException  If invalid name specified
     */
    public function get(string $name): MigratorInterface
    {
        if (! $this->has($name)) {
            throw new \InvalidArgumentException("No migrator exists with slug/name: " . $name);
        }

        return $this->migrators[$name];
    }

    /**
     * Get multiple migrators
     *
     * This does not not do any dependency resolution or sorting.  If you need to do that,
     * use self::resolveDependencies()
     *
     * @param string[] $names
     * @return \ArrayIterator|MigratorInterface[]  An iterator of migrators in the same order of the supplied names
     */
    public function getMultiple(string ...$names): \ArrayIterator
    {
        return new \ArrayIterator(array_map([$this, 'get'], $names));
    }

    /**
     * Iterate over all migrators
     *
     * This returns migrators in dependency order.
     *
     * @return \ArrayIterator|MigratorInterface[]  An iterator of migrators in the order they must be processed
     * @throws \MJS\TopSort\CircularDependencyException
     * @throws \MJS\TopSort\ElementNotFoundException
     */
    public function getIterator(): \ArrayIterator
    {
        return new ArrayIterator(array_map([$this, 'get'], $this->sorter->sort()));
    }

    /**
     * Count migrators
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->migrators);
    }

    /**
     * Resolve dependencies for a given migrator slug or instance
     *
     * @param string[] $migrators  The migrator(s) or name(s) of the migrator(s)
     * @return \ArrayIterator|MigratorInterface[]  A list of migrators in the order they must be processed
     * @throws \MJS\TopSort\CircularDependencyException
     * @throws \MJS\TopSort\ElementNotFoundException
     */
    public function resolveDependencies(string ...$migrators): array
    {
        $sorter = new StringSort();

        foreach ($migrators as $migrator) {
            if (! $migrator instanceof MigratorInterface) {
                $migrator = $this->get($migrator);
            }

            foreach ($this->iterateDependencies($migrator) as $dependency) {
                $sorter->add($dependency->getName(), $dependency->getDependsOn());
            }
        }

        return new \ArrayIterator(array_map([$this, 'get'], $sorter->sort()));
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
        if (! in_array($migrator->__toString(), $visited)) {
            $visited[] = $migrator->__toString();
            yield $migrator;
        }

        foreach ($migrator->getDependsOn() as $dependency) {
            yield from $this->iterateDependencies($this->get($dependency), $visited);
        }
    }
}
