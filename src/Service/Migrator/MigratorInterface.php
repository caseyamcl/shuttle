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

namespace Shuttle\Service\Migrator;

/**
 * Migrator Interface
 *
 * @package Shuttle\Service\Migrator
 */
interface MigratorInterface
{
    /**
     * @return string  A unique identifier for the type of record being migrated
     */
    function getSlug();

    /**
     * @return string  A description of the records being migrated
     */
    function getDescription();

    /**
     * @return SourceInterface
     */
    function getSource();

    /**
     * @return DestinationInterface
     */
    function getDestination();

    /**
     * @return int  Number of records in the source
     */
    function getNumRecords();

    /**
     * Migrate a single record
     *
     * @param string $oldRecId  Record ID in the old system
     * @return string  The new Record ID
     */
    function migrate($oldRecId);

    /**
     * Revert a single record
     *
     * @param $newRecId
     */
    function revert($newRecId);
}
