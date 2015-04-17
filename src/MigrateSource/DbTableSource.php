<?php
/**
 * shuttle
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

namespace Shuttle\MigrateSource;

/**
 * Db Table Source
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class DbTableSource extends DbSource
{
    /**
     * Build from DSN
     *
     * @param string $dbConnString
     * @param string $username
     * @param string $password
     * @param string $tableName
     * @param string $idColumn
     * @return static
     */
    public static function build($dbConnString, $username, $password, $tableName, $idColumn = 'id')
    {
        return new static(new \PDO($dbConnString, $username, $password), $tableName, $idColumn);
    }

    // ---------------------------------------------------------------

    /**
     * Constructor
     *
     * @param \PDO   $dbConn     Database Connection
     * @param string $tableName  The name of the table
     * @param string $idColumn   The ID column in the table (defaults to 'id')
     */
    public function __construct(\PDO $dbConn, $tableName, $idColumn = 'id')
    {
        $countQuery  = sprintf('SELECT COUNT(t.%s) FROM %s t', $idColumn, $tableName);
        $listQuery   = sprintf('SELECT t.%s FROM %s t', $idColumn, $tableName);
        $singleQuery = sprintf('SELECT t.* FROM %s t WHERE t.%s = ?', $tableName, $idColumn);

        parent::__construct($dbConn, $countQuery, $listQuery, $singleQuery);
    }
}
