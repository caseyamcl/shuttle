<?php
/**
 * conveyorbelt
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

namespace ConveyorBelt\MigrateSource;

/**
 * Class SimpleCSVSource
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class SimpleCSVSource extends SimpleJsonSource
{
    /**
     * Constructor
     *
     * @param string $csvSourceUri
     * @param string $idFieldName
     */
    public function __construct($csvSourceUri, $idFieldName)
    {
        parent::__construct($csvSourceUri, $idFieldName);
    }

    // ---------------------------------------------------------------

    protected function decodeInput($rawInput, $idFieldName)
    {
        $recs = [];

        $fh = fopen($rawInput, 'r');
        while ($row = fgetcsv($fh)) {
            $recs[$row[$idFieldName]] = $row;
        }
        fclose($fh);

        return $recs;
    }
}
