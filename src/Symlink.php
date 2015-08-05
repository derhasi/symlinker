<?php
/**
 * @file
 * Symlink.php
 */

namespace derhasi\symlinker;

use derhasi\symlinker\Exception\SourceNotFoundException;
use derhasi\symlinker\Exception\SymlinkFailedException;
use derhasi\symlinker\Exception\TargetAlreadyExistsException;
use derhasi\symlinker\Exception\TargetAlreadyLinkedException;
use derhasi\symlinker\Exception\TargetAlreadyLinkedToSourceException;
use Webmozart\PathUtil\Path;

/**
 * Representation of a symlink.
 */
class Symlink {

    /**
     * @var string
     */
    protected $workingDirectory;

    /**
     * @var string
     *   Absolute path of the link to create.
     */
    protected $link;

    /**
     * @var string
     *   Absolute path for the link to point to.
     */
    protected $source;

    /**
     * @var bool
     */
    protected $sourceIsAbsolute;


    /**
     * @param $link
     * @param null $cwd
     */
    public function __construct($link, $cwd = NULL)
    {
        if (!isset($cwd)) {
            $this->workingDirectory = getcwd();
        }
        else {
            $this->workingDirectory = $cwd;
        }

        if (!Path::isAbsolute($link)) {
            $this->link = Path::makeAbsolute($link, $this->workingDirectory);
        }
        else {
            $this->link = $link;
        }
    }

    /**
     * Set source absolute or relative to the link.
     *
     * @param string $source
     */
    public function setSource($source)
    {
        $this->setSourceRelativeTo($source, dirname($this->link));
    }

    /**
     * Set source relative to the initial working directory.
     *
     * @param string $source
     */
    public function setSourceFromWorkingDirectory($source)
    {
        $this->setSourceRelativeTo($source, $this->workingDirectory);
    }

    /**
     * Set source relative to a given path.
     *
     * @param string $source
     * @param string $path
     */
    public function setSourceRelativeTo($source, $path)
    {
        if (!Path::isAbsolute($source)) {
            $this->sourceIsAbsolute = FALSE;
            $this->source = Path::makeAbsolute($source, $path);
        }
        else {
            $this->sourceIsAbsolute = TRUE;
            $this->source = $source;
        }
    }

    /**
     * Creates the symlink.
     *
     * Will create an absolute link, if the source information was absolute.
     * Will create a relative link otherwise.
     *
     * @throws \derhasi\symlinker\SymlinkFailedException
     */
    public function create()
    {
        if ($this->sourceIsAbsolute) {
            $this->createAbsolute();
        }
        else {
            $this->createRelative();
        }
    }

    /**
     * Creates a symlink with an absolute path.
     *
     * @throws \derhasi\symlinker\SourceNotFoundException
     * @throws \derhasi\symlinker\SymlinkFailedException
     * @throws \derhasi\symlinker\TargetAlreadyExistsException
     * @throws \derhasi\symlinker\TargetAlreadyLinkedException
     * @throws \derhasi\symlinker\TargetAlreadyLinkedToSourceException
     */
    public function createAbsolute()
    {
        $this->validate();
        $success = symlink($this->source, $this->link);

        if (!$success) {
            throw new SymlinkFailedException($this->link, $this->source);
        }
    }

    /**
     * Creates a symlink with a relative path.
     *
     * @throws \derhasi\symlinker\SymlinkFailedException
     */
    public function createRelative()
    {
        $this->validate();
        $source = Path::makeRelative($this->source, dirname($this->link));

        $success = symlink($source, $this->link);

        if (!$success) {
            throw new SymlinkFailedException($this->link, $this->source);
        }
    }

    /**
     * Some validation that will lead to a symlink failing.
     *
     * @throws \derhasi\symlinker\SourceNotFoundException
     * @throws \derhasi\symlinker\TargetAlreadyExistsException
     * @throws \derhasi\symlinker\TargetAlreadyLinkedException
     * @throws \derhasi\symlinker\TargetAlreadyLinkedToSourceException
     */
    public function validate()
    {
        // Check if the source exists.
        if (!$this->sourceFileExists()) {
            throw new SourceNotFoundException($this->link, $this->source);
        }

        // In the case the target does not exist, we simply can create the
        // symlink.
        if ($this->targetAlreadyExists()) {
            if ($this->targetLinksToSource()) {
                throw new TargetAlreadyLinkedToSourceException($this->link, $this->source);
            }
            elseif ($this->targetIsLink()) {
                throw new TargetAlreadyLinkedException($this->link, $this->source);
            }
            else {
                throw new TargetAlreadyExistsException($this->link, $this->source);
            }
        }
    }

    /**
     * Check if a source already was assigned.
     *
     * @return bool
     */
    public function sourceGiven()
    {
        return isset($this->source);
    }

    /**
     * Check if the given source path exists.
     *
     * @return bool
     */
    public function sourceFileExists()
    {
        return file_exists($this->source);
    }

    /**
     * Check if the given link path already holds a file or link.
     *
     * @return bool
     */
    public function targetAlreadyExists()
    {
        return file_exists($this->link);
    }

    /**
     * Checks if the given link path is already a link.
     *
     * @return bool
     */
    public function targetIsLink()
    {
        return is_link($this->link);
    }

    /**
     * Check if target is linked and links to the given source.
     *
     * @return bool
     */
    public function targetLinksToSource()
    {
        if (!$this->targetIsLink()) {
            return FALSE;
        }

        $current_source = readlink($this->link);
        if (!Path::isAbsolute($current_source)) {
            $current_source = Path::makeAbsolute($current_source, dirname($this->link));
        }
        return $current_source == $this->source;
    }
}