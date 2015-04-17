<?php
/**
 * Shuttle
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


use Shuttle\Service\Migrator\Exception\MissingRecordException;
use Shuttle\Service\Migrator\SourceInterface;

/**
 * Simple JSON Source
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class JsonSource implements \IteratorAggregate, SourceInterface
{
    /**
     * @var array  Array of arrays
     */
    private $recs;

    // ---------------------------------------------------------------

    /**
     * Constructor
     *
     * @param string $rawData
     * @param string $idFieldName  If empty, assume key/value arrangement
     */
    public function __construct($rawData, $idFieldName = '')
    {
        $this->recs = $this->decodeInput($rawData, $idFieldName);
    }

    // ---------------------------------------------------------------

    /**
     * Decode Input
     *
     * @param string $rawInput
     * @param string $idFieldName
     * @return array Keys are IDs, values are records (as associative arrays)
     */
    protected function decodeInput($rawInput, $idFieldName)
    {
        $arr = [];

        foreach (json_decode($rawInput, true) as $key => $val) {
            $id = $idFieldName ? $val[$idFieldName] : $key;
            $arr[$id] = $val;
        }

        return $arr;
    }

    // ---------------------------------------------------------------

    /**
     * @return int
     */
    public function count()
    {
        return $this->count($this->recs);
    }

    // ---------------------------------------------------------------

    /**
     * @return string[]  Get a list of record IDs in the source
     */
    function listRecordIds()
    {
        return array_map('strval', array_keys($this->recs));
    }

    // ---------------------------------------------------------------

    /**
     * @param string $id
     * @return array Record, represented as key/value associative array
     */
    function getRecord($id)
    {
        if (array_key_exists($id, $this->recs)) {
            return $this->recs[$id];
        }
        else {
            throw new MissingRecordException("Could not find record with ID: " . $id);
        }
    }

    // ---------------------------------------------------------------

    function getIterator()
    {
        return new \ArrayIterator($this->listRecordIds());
    }
}
