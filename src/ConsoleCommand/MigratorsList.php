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

namespace Shuttle\ConsoleCommand;

use Shuttle\Migrator\MigratorInterface;
use Shuttle\MigratorCollection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Migrators List Command
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class MigratorsList extends Command
{
    /**
     * @var MigratorCollection|MigratorInterface[]
     */
    private $migrators;

    /**
     * Constructor
     *
     * @param MigratorCollection $migrators
     */
    public function __construct(MigratorCollection $migrators)
    {
        $this->migrators = $migrators;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('migrators:list');
        $this->setDescription('List available migrators');

        $this->addOption(
            'nostatus',
            's',
            InputOption::VALUE_NONE,
            'Omit migrations status (speeds up execution)'
        );

        $this->addOption(
            'format',
            'f',
            InputOption::VALUE_REQUIRED,
            "Output format ('table', 'cols', or 'json', 'jsonpretty'). Defaults to 'table'",
            'table'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $list = array();
        foreach ($this->migrators as $migrator) {
            $row = array(
                'slug'        => $migrator->getName(),
                'description' => $migrator->getDescription(),
                'depends_on'  => $migrator->getDependsOn()
            );

            if ($input->getOption('nostatus') == false) {
                $row['num_source_recs'] = $migrator->countSourceItems();
                $row['num_migrated']    = count(iterator_to_array($migrator->getReport()));
            }

            $list[] = $row;
        }


        switch ($input->getOption('format')) {
            case 'table':
                $this->outTable($output, $list, $input->getOption('nostatus') == false);
                break;
            case 'cols':
                $this->outCols($output, $list);
                break;
            case 'json':
                $this->outJson($output, $list);
                break;
            case 'jsonpretty':
                $this->outJson($output, $list, true);
                break;
            default:
                throw new \RuntimeException("Invalid format.  Allowed values: ('table', 'cols', 'json'");
        }
    }

    /**
     * @param OutputInterface $output
     * @param array           $recs
     * @param bool            $hasStatus
     */
    private function outTable(OutputInterface $output, array $recs, $hasStatus = false)
    {
        $headers = [
            'Name (slug)',
            'Description',
            'Depends On'
        ];
        if ($hasStatus) {
            $headers[] = 'Source Record Count';
            $headers[] = 'Migrated Count';
        }

        $table = new Table($output);
        $table->setHeaders($headers);
        $table->addRows(array_map(function(array $row) {
            $row['depends_on'] = implode(', ', $row['depends_on']);
            return $row;
        }, $recs));
        $table->render();
    }

    /**
     * @param OutputInterface $output
     * @param array           $recs
     */
    private function outCols(OutputInterface $output, array $recs): void
    {
        (new Table($output))->setStyle('compact')->addRows(array_map(function(array $row) {
            $row['depends_on'] = implode(', ', $row['depends_on']);
            return $row;
        }, $recs))->render();
    }

    /**
     * @param OutputInterface $output
     * @param array           $recs
     * @param bool            $pretty
     */
    private function outJson(OutputInterface $output, array $recs, $pretty = false): void
    {
        $jsonString = ($pretty) ? json_encode($recs, JSON_PRETTY_PRINT) : json_encode($recs);
        $output->writeln($jsonString);
    }
}
