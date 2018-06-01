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
    private $newDbConn;

    /**
     * @var Connection
     */
    private $oldDbConn;

    /**
     * @var array
     */
    private $searchNs;

    // ---------------------------------------------------------------

    /**
     * Constructor
     *
     * @param Connection        $oldDbConn
     * @param Connection        $newDbConn
     * @param RecorderInterface $recorder
     * @param array             $searchNamespaces  Provide a list of namespaces, so that short class names can be used
     */
    public function __construct(Connection $oldDbConn, Connection $newDbConn, RecorderInterface $recorder, array $searchNamespaces = [])
    {
        $this->oldDbConn = $oldDbConn;
        $this->newDbConn = $newDbConn;
        $this->recorder  = $recorder;
        $this->searchNs  = $searchNamespaces;
    }

    // ---------------------------------------------------------------

    /**
     * Build a new
     * @param string $className  Fully-qualified class-name
     * @param array  $params     Parameters
     * @return Migrator
     */
    public function build($className, array $params = [])
    {
        $className = $this->resolveClassName($className);

        if (! is_a($className, __NAMESPACE__ . "\\Migrator", true)) {
            throw new RuntimeException(sprintf(
                "%s can only build objects that subclass: %s",
                get_called_class(),
                __NAMESPACE__ . "\\Migrator"
            ));
        }

        return new $className($this->oldDbConn, $this->newDbConn, $this->recorder, $params);
    }

    // ---------------------------------------------------------------

    /**
     * Resolve Class Name
     *
     * @param $className
     * @return string
     * @throws \RuntimeException  If class not found
     */
    private function resolveClassName($className)
    {
        // If the class name exists as given, return it..
        if (class_exists($className)) {
            return $className;
        }

        $searched = [];

        // If default namespaces provided, loop through in order until one works
        foreach ($this->searchNs as $ns) {
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
