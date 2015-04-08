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

namespace Shuttle\Service\Recorder;

/**
 * Recorder Interface
 *
 * Tracks migration state for records
 *
 * @package Shuttle\Service\Recorder
 */
interface RecorderInterface
{
    /**
     * @parma string $type
     * @return int
     */
    public function getMigratedCount($type);

    /**
     * @param string $type
     * @return string[]  Array of new IDs
     */
    public function getNewIds($type);

    /**
     * @param string $type
     * @param string $oldId
     * @return boolean
     */
    public function isMigrated($type, $oldId);

    /**
     * @param string $type
     * @param string $oldId
     * @return string
     */
    public function getNewId($type, $oldId);

    /**
     * @param string $type
     * @param string $newId
     * @return string
     */
    public function getOldId($type, $newId);

    /**
     * @param string $type
     * @param string $oldId
     * @param string $newId
     */
    public function markMigrated($type, $oldId, $newId);

    /**
     * @param string $type
     * @param string $newId
     */
    public function removeMigratedMark($type, $newId);
}
