<?php

namespace derhasi\symlinker\Exception;


class SymlinkFailedException extends \RuntimeException {

    public function __construct($target, $source)
    {
        parent::__construct(sprintf('Symlink creation failed from "%s" to "%s".', $target, $source));
    }
}