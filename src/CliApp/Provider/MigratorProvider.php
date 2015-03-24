<?php
/**
 * ticketmove
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

namespace ConveyorBelt\Provider;

use Cilex\Application;
use Cilex\ServiceProviderInterface;
use ConveyorBelt\Migrator\StaffMemberMigrator;
use ConveyorBelt\Service\Migrator\MigratorCollection;
use ConveyorBelt\Service\Migrator\MigratorFactory;
use ConveyorBelt\Service\Migrator\MigratorService;

/**
 * Class MigratorProvider
 *
 * Required Params:
 *  - 'migrator.dbs'        - array('otrsdb', 'osticketdb')
 *  - 'migrator.recorder'   - RecorderInterface
 *  - 'migrator.dispatcher' - Symfony EventDispatcherInterface
 *  - 'migrator.params'     - Array of params where key is MigratorClassName (short), and value is array of params
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class MigratorProvider implements ServiceProviderInterface
{
    /**
     * Registers services on the given app.
     *
     * @param Application $app An Application instance
     */
    public function register(Application $app)
    {
        $app['migrator.default_ns'] = $app->share(function() {
            return '\ConveyorBelt\Migrator';
        });

        // ~~~~

        $app['migrator.factory'] = $app->share(function(Application $app) {
            return new MigratorFactory(
                $app['migrator.dbs']['otrsdb'],
                $app['migrator.dbs']['osticketdb'],
                $app['migrator.recorder'],
                $app['migrator.default_ns']
            );
        });

        // ~~~~

        $app['migrator.migrators'] = $app->share(function(Application $app) {

            $migrators = array();
            foreach (['StaffMemberMigrator', 'TicketMigrator'] as $migrator) {

                $params = (array_key_exists($migrator, $app['migrator.params']))
                    ? $app['migrator.params'][$migrator]
                    : [];

                $migrators[] = $app['migrator.factory']->build($migrator, $params);
            }

            return new MigratorCollection($migrators);
        });
    }

    // ---------------------------------------------------------------

//    /**
//     * @param string          $pathToMigratorClasses
//     * @param MigratorFactory $factory
//     * @param array           $params
//     * @return array
//     */
//    private function buildMigratorsFromFs($pathToMigratorClasses, MigratorFactory $factory, array $params)
//    {
//        $out = array();
//
//        foreach (glob(rtrim($pathToMigratorClasses, '/') . '/*.php') as $file) {
//            $clsName = basename($file, '.php');
//            $clsParams = array_key_exists($clsName, $params) ? $params[$clsName] : [];
//            $out[] = $factory->build($clsName, $clsParams);
//        }
//
//        return $out;
//    }
}
