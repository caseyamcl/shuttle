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

namespace ConveyorBeltMigrator\Example;

use ConveyorBelt\Service\Migrator\BaseMigrator;

/**
 * Class ExampleMigrator
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class ExampleMigrator extends BaseMigrator
{
    /**
     * Example Migrator
     *
     * The constructor for the BaseMigrator is overridden so that we can
     * specify the values we want for the source, destination, etc.
     *
     * You can also require custom dependencies this way
     */
    public function __construct()
    {
        $source      = new ExampleSource();
        $dest        = new ExampleDestination();
        $slug        = 'example';
        $description = 'This is an example migrator, that simply migrates between two arrays';

        parent::__construct($slug, $source, $dest, $description);
    }

    // ---------------------------------------------------------------

    /**
     * This is where you convert the record from source-format to destination format
     *
     * @param array $record
     * @return array
     */
    protected function prepare(array $record)
    {
        // Remove ID field if it was included in the sourc record
        if (array_key_exists('id', $record)) {
            unset($record['id']);
        }

        // And... convert the entire record to lower-case
        return array_map('strtolower', $record);
    }


}
