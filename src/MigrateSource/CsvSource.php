<?php
/**
 * Shuttle
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
 * Class CsvSource
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class CsvSource extends JsonSource
{
    /**
     * @var bool
     */
    private $hasHeaders;

    /**
     * Constructor
     *
     * @param string $csvSourceUri
     * @param string $idFieldName
     * @param bool   $hasHeaders
     */
    public function __construct($csvSourceUri, $idFieldName, $hasHeaders = true)
    {
        $this->hasHeaders = $hasHeaders;
        parent::__construct($csvSourceUri, $idFieldName);
    }

    protected function decodeInput($rawInput, $idFieldName)
    {
        $headers = [];
        $recs    = [];

        $fh = fopen($rawInput, 'r');

        for ($i = 0; $row = fgetcsv($fh); $i++) {
            if ($i == 0 && $this->hasHeaders) {
                $headers = array_values($row);
                continue;
            } elseif ($i == 0 && ! $this->hasHeaders) {
                $headers = array_keys($row);
            }

            // If row is less than the number of columns (valid CSV apparently, but for array_combine)
            if (count($row) < count($headers)) {
                $row = array_merge($row, array_fill(count($row), count($headers) - count($row), ''));
            }

            $recs[$row[$idFieldName]] = array_combine($headers, array_values($row));
        }

        fclose($fh);

        return $recs;
    }
}
