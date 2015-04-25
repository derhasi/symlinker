<?php

namespace derhasi\symlinker\Exception;

class TargetAlreadyExistsException extends \RuntimeException {

    public function __construct($target, $source)
    {
        parent::__construct(sprintf('Target "%s" is already set, but no symlink. (source "%s")', $target, $source));
    }
}