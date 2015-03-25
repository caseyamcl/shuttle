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

use ConveyorBelt\Service\Migrator\Exception\MissingRecordException;
use ConveyorBelt\Service\Migrator\SourceInterface;

/**
 * Example Source
 *
 * A very, very simple example with records loaded from an array
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class ExampleSource implements SourceInterface {

    /**
     * Source Records
     *
     * These would usually come from somewhere else, such as a
     *
     * @var array
     */
    private $records = [
        ['id' => 1, 'name' => 'Bob',  'color' => 'green' ],
        ['id' => 2, 'name' => 'Roy',  'color' => 'blue'  ],
        ['id' => 3, 'name' => 'Alex', 'color' => 'yellow']
    ];

    // ---------------------------------------------------------------

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->records);
    }

    /**
     * {@inheritdoc}
     */
    function listRecordIds()
    {
        return array_map(function($val) {
            return (string) $val['id'];
        }, $this->records);
    }

    /**
     * {@inheritdoc}
     */
    function getRecord($id)
    {
        foreach ($this->records as $rec) {
            if ($rec['id'] == $id) {
                unset($rec['id']);
                return $rec;
            }
        }

        // If made it here
        throw new MissingRecordException("Record with ID not in source array: $id");
    }
}
