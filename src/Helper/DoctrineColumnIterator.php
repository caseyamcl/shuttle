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

namespace Shuttle\Helper;

use Doctrine\DBAL\Driver\ResultStatement;

/**
 * Doctrine Column Iterator
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class DoctrineColumnIterator extends \IteratorIterator
{
    /**
     * @var ResultStatement
     */
    private $stmt;

    /**
     * @var string
     */
    private $columnName;

    /**
     * Constructor
     *
     * @param ResultStatement $stmt
     * @param string    $columnName
     */
    public function __construct(ResultStatement $stmt, $columnName)
    {
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);

        parent::__construct($stmt);
        $this->stmt = $stmt;
        $this->columnName = $columnName;
    }

    /**
     * Returns the specified column from the current record
     *
     * If the record exists, but the column does not, exception!
     * We ain't gonna tolerate that.
     *
     * @return string
     */
    public function current(): string
    {
        $val = parent::current();

        if ($val && array_key_exists($this->columnName, $val)) {
            return (string) $val[$this->columnName];
        } elseif ($val) {
            throw new \RuntimeException(sprintf(
                "Error retrieving '%s' column from record: ",
                $this->columnName,
                json_encode($val)
            ));
        } else {
            return $val;
        }
    }
}
