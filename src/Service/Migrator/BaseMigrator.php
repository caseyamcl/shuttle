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

namespace ConveyorBelt\Service\Migrator;

use ConveyorBelt\Service\Migrator\Destination\DestinationInterface;
use ConveyorBelt\Service\Migrator\Source\SourceInterface;

/**
 * Base Migrator
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
abstract class BaseMigrator implements MigratorInterface
{
    /**
     * @var string
     */
    private $slug;

    /**
     * @var string
     */
    private $description;

    /**
     * @var SourceInterface
     */
    private $source;

    /**
     * @var DestinationInterface
     */
    private $destination;

    // ---------------------------------------------------------------

    /**
     * Constructor
     *
     * @param string               $slug
     * @param SourceInterface      $source
     * @param DestinationInterface $destination
     * @param string               $description
     */
    public function __construct($slug, SourceInterface $source, DestinationInterface $destination, $description = '')
    {
        $this->slug        = $slug;
        $this->source      = $source;
        $this->destination = $destination;
        $this->setDescription($description);
    }

    // ---------------------------------------------------------------

    /**
     * @return string  A unique identifier for the type of record being migrated
     */
    public function getSlug()
    {
        return $this->slug;
    }

    // ---------------------------------------------------------------

    /**
     * @return string  A description of the records being migrated
     */
    public function getDescription()
    {
        return $this->description;
    }

    // ---------------------------------------------------------------

    /**
     * Set Description
     *
     * @param string $description
     */
    protected function setDescription($description)
    {
        $this->description = (string) $description;
    }

    // ---------------------------------------------------------------

    /**
     * @return SourceInterface
     */
    public function getSource()
    {
        return $this->source;
    }

    // ---------------------------------------------------------------

    /**
     * @return DestinationInterface
     */
    public function getDestination()
    {
        return $this->destination;
    }

    // ---------------------------------------------------------------

    /**
     * @return int  Number of records in the source
     */
    public function getNumRecords()
    {
        return $this->source->count();
    }

    // ---------------------------------------------------------------

    /**
     * Migrate a single record
     *
     * @param string $oldRecId Record ID in the old system
     * @return MigrateResult
     */
    public function migrate($oldRecId)
    {
        $oldRec = $this->source->getRecord($oldRecId);
        return (string) $this->destination->saveRecord($this->prepare($oldRec));
    }

    // ---------------------------------------------------------------

    /**
     * Revert a single record
     *
     * @param $newRecId
     */
    public function revert($newRecId)
    {
        $this->destination->deleteRecord($newRecId);
    }

    // ---------------------------------------------------------------

    /**
     * Transform/validate record
     *
     * @param array $record
     * @return array
     */
    protected function prepare(array $record)
    {
        // By default, do nothing to the record..
        return $record;
    }
}
