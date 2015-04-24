<?php

namespace derhasi\symlinker\Exception;


class SymlinkFailedException extends \RuntimeException {

    public function __construct($target, $source)
    {
        parent::__construct(sprintf('Symlink creation failed from "%s" is to "%s".', $target, $source));
    }
}