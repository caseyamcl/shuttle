<?php
/**
 * shuttle
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

namespace Shuttle\MigrateSource;

/**
 * Db Table Source
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class DbTableSource extends DbSource
{
    /**
     * Constructor
     *
     * @param \PDO   $dbConn     Database Connection
     * @param string $tableName  The name of the table
     * @param string $idColumn   The ID column in the table (defaults to 'id')
     */
    public function __construct(\PDO $dbConn, string $tableName, string $idColumn = 'id')
    {
        $countQuery  = sprintf('SELECT COUNT(t.%s) FROM %s t', $idColumn, $tableName);
        $listQuery   = sprintf('SELECT t.%s FROM %s t', $idColumn, $tableName);
        $singleQuery = sprintf('SELECT t.* FROM %s t WHERE t.%s = ?', $tableName, $idColumn);

        parent::__construct($dbConn, $countQuery, $listQuery, $singleQuery);
    }
}
