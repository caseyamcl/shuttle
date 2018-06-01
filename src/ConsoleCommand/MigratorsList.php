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

use Shuttle\Migrator\MigratorCollection;
use Shuttle\Migrator\MigratorInterface;
use Shuttle\Recorder\RecorderInterface;
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
     * @var RecorderInterface
     */
    private $recorder;

    /**
     * Constructor
     *
     * @param MigratorCollection $migrators
     * @param RecorderInterface  $recorder
     */
    public function __construct(MigratorCollection $migrators, RecorderInterface $recorder)
    {
        parent::__construct();

        $this->migrators = $migrators;
        $this->recorder  = $recorder;
    }

    protected function configure()
    {
        $this->setName('migrators:list');
        $this->setDescription('List available migrators');
        $this->addOption('nostatus', 's', InputOption::VALUE_NONE, 'Omit migrations status (speeds up execution)');
        $this->addOption('format', 'f', InputOption::VALUE_REQUIRED, "Output format ('table', 'cols', or 'json', 'jsonpretty'). Defaults to 'table'", 'table');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $list = array();
        foreach ($this->migrators as $migrator) {
            $row = array(
                'slug'        => $migrator->getSlug(),
                'description' => $migrator->getDescription()
            );

            if ($input->getOption('nostatus') == false) {
                $row['num_source_recs'] = $migrator->getSource()->count();
                $row['num_migrated']    = $this->recorder->getMigratedCount($migrator->getSlug());
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
            'Description'
        ];
        if ($hasStatus) {
            $headers[] = 'Source Record Count';
            $headers[] = 'Migrated Count';
        }

        $table = new Table($output);
        $table->setHeaders($headers);
        $table->addRows($recs);
        $table->render();
    }

    /**
     * @param OutputInterface $output
     * @param array           $recs
     */
    private function outCols(OutputInterface $output, array $recs): void
    {
        (new Table($output))->setStyle('compact')->addRows($recs)->render();
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
