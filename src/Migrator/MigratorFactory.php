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

use Doctrine\DBAL\Connection;
use Shuttle\Recorder\RecorderInterface;
use RuntimeException;

/**
 * Migrator Factory
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class MigratorFactory
{
    /**
     * @var RecorderInterface
     */
    private $recorder;

    /**
     * @var Connection
     */
    private $destinationDbConnection;

    /**
     * @var Connection
     */
    private $sourceDbConnection;

    /**
     * @var array|string[]
     */
    private $searchNamespaces;

    /**
     * Constructor
     *
     * @param Connection        $sourceDbConnection
     * @param Connection        $destinationDbConnection
     * @param RecorderInterface $recorder
     * @param array             $searchNamespaces  Provide a list of namespaces, so that short class names can be used
     */
    public function __construct(
        Connection $sourceDbConnection,
        Connection $destinationDbConnection,
        RecorderInterface $recorder,
        array $searchNamespaces = []
    ) {
        $this->sourceDbConnection = $sourceDbConnection;
        $this->destinationDbConnection = $destinationDbConnection;
        $this->recorder = $recorder;
        $this->searchNamespaces  = $searchNamespaces;
    }

    /**
     * Build a new
     * @param string $className  Fully-qualified class-name
     * @param array  $params     Parameters
     * @return MigratorInterface
     */
    public function build(string $className, array $params = []): MigratorInterface
    {
        $className = $this->resolveClassName($className);

        if (! is_a($className, __NAMESPACE__ . "\\Migrator", true)) {
            throw new RuntimeException(sprintf(
                "%s can only build objects that subclass: %s",
                get_called_class(),
                __NAMESPACE__ . "\\Migrator"
            ));
        }

        return new $className($this->sourceDbConnection, $this->destinationDbConnection, $this->recorder, $params);
    }

    /**
     * Resolve Class Name
     *
     * @param string $className
     * @return string
     * @throws \RuntimeException  If class not found
     */
    private function resolveClassName(string $className): string
    {
        // If the class name exists as given, return it..
        if (class_exists($className)) {
            return $className;
        }

        $searched = [];

        // If default namespaces provided, loop through in order until one works
        foreach ($this->searchNamespaces as $ns) {
            $fqNs = "\\" . trim($ns, "\\") . "\\" . ltrim($className, "\\");
            $searched[] = $fqNs;
            if (class_exists($fqNs)) {
                return $fqNs;
            }
        }

        // If made it here, could not resolve
        throw new \RuntimeException(sprintf(
            "Could not find class: %s (searched %s, %s)",
            $className,
            $className,
            implode(', ', $searched)
        ));
    }
}
