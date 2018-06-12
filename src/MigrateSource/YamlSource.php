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

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Yaml;
use Traversable;

/**
 * Simple YAML Source
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class YamlSource extends JsonSource
{
    /**
     * @var Parser
     */
    private $parser;

    /**
     * @param string $rawYamlData
     * @param string $idFieldName
     */
    public function __construct($rawYamlData, $idFieldName = '')
    {
        if (! class_exists('Symfony\Component\Yaml\Parser')) {
            throw new \RuntimeException(
                "Yaml source requires Symfony YAML dependency (`composer require symfony/yaml`)"
            );
        }

        $this->parser = new Parser();
        parent::__construct($rawYamlData, $idFieldName);
    }

    protected function decodeInput($rawInput, $idFieldName)
    {
        $arr = [];

        $parsed = Yaml::parse($rawInput, true);

        if (! is_array($parsed)) {
            throw new ParseException("Invalid YAML: " . $parsed);
        }

        foreach ($parsed as $key => $val) {
            $id = $idFieldName ? $val[$idFieldName] : $key;
            $arr[$id] = $val;
        }

        return $arr;
    }
}
