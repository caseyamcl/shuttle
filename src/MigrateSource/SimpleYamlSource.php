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

use Symfony\Component\Yaml\Yaml;

/**
 * Simple YAML Source
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class SimpleYamlSource extends SimpleJsonSource
{
    /**
     * @param string $rawData
     * @param string $idFieldName
     */
    public function __construct($rawData, $idFieldName = '')
    {
        parent::__construct($rawData, $idFieldName);
    }

    // ---------------------------------------------------------------

    protected function decodeInput($rawInput, $idFieldName)
    {
        $arr = [];

        foreach (Yaml::parse($rawInput) as $key => $val) {
            $id = $idFieldName ? $val[$idFieldName] : $key;
            $arr[$id] = $val;
        }

        return $arr;
    }

}
