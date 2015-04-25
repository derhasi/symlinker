<?php

namespace derhasi\symlinker;

use derhasi\symlinker\Exception\SourceNotFoundException;
use derhasi\symlinker\Exception\SymlinkFailedException;
use derhasi\symlinker\Exception\TargetAlreadyExistsException;
use derhasi\symlinker\Exception\TargetAlreadyLinkedException;
use derhasi\symlinker\Exception\TargetAlreadyLinkedToSourceException;
use Symfony\Component\Filesystem\Filesystem;
use Webmozart\PathUtil\Path;

class Symlinker {

    /**
     * Create a symlink.
     *
     * @param $target
     * @param $source
     */
    public static function createSymlink($target, $source)
    {
        // Check if the source exists.
        if (!file_exists($source)) {
            throw new SourceNotFoundException($target, $source);
        }

        // In the case the target does not exist, we simply can create the
        // symlink.
        if (!file_exists($target)) {
            static::symlink($target, $source);
        }
        // If the target exists but is no link ...
        elseif (!is_link($target)) {
            throw new TargetAlreadyExistsException($target, $source);
        }
        // If target is a symlink and points to source, we do not have to
        // do anything.
        elseif (static::linkedToSource($target, $source)) {
            throw new TargetAlreadyLinkedToSourceException($target, $source);
        }
        // If the target points to a different source ...
        else {
            throw new TargetAlreadyLinkedException($target, $source);
        }
    }

    /**
     * Ensure
     *
     * @param string $target
     * @param string $source
     * @param bool $createDirectory
     */
    public static function ensureSymlink($target, $source, $createDirectory = false)
    {
        $dir = dirname($target);
        if ($createDirectory && !file_exists($dir)) {
            mkdir($dir, 0777, true);
        }

        try {
            static::createSymlink($target, $source);
        }
        catch (TargetAlreadyLinkedToSourceException $e) {
            // Do nothing if symlink already points to the correct target.
        }
    }

    /**
     * Helper to create the symlink.
     *
     * @param string $target
     * @param string $source
     */
    protected static function symlink($target, $source)
    {
        if (!Path::isAbsolute($source)) {
            $source = Path::makeRelative($source, dirname($target));
        }

        $success = symlink($source, $target);

        if (!$success) {
            throw new SymlinkFailedException($target, $source);
        }
    }

    /**
     * Checks if the target is already linked to source.
     *
     * @param $target
     * @param $source
     * @param $link
     * @return bool
     */
    protected static function linkedToSource($target, $source)
    {
        $link = readlink($target);

        if (Path::isAbsolute($link)) {
            if (Path::isAbsolute($source)) {
                return $source == $link;
            }

            return $link == Path::makeAbsolute($source, getcwd());
        }
        else {
            return $link == Path::makeRelative($source, dirname($target));
        }

    }



}