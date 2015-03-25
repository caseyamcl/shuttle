#!/usr/bin/env php
<?php

/**
 * This is run as part of the Box PHP build process, and copies directories
 * to the 'dist' folder
 */

$thisDir = __DIR__;

recursiveCopy($thisDir . '/migrators', 'dist/migrators');

copy($thisDir . '/config.yml.dist', 'dist/config.yml.dist');
copy($thisDir . '/README.md',       'dist/README.md');
copy($thisDir . '/LICENSE',         'LICENSE');

// --------------

/**
 * Recursive copy
 *
 * @param string $source
 * @param string $dest
 */
function recursiveCopy($source, $dest)
{
    mkdir($dest, 0755);

    foreach (
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST) as $item
    ) {
        if ($item->isDir()) {
            mkdir($dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
        } else {
            copy($item, $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
        }
    }
}


