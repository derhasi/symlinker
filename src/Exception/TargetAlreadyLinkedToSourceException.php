<?php

namespace derhasi\symlinker\Exception;

class TargetAlreadyLinkedToSourceException extends \RuntimeException {

    public function __construct($target, $source)
    {
        parent::__construct(sprintf('Target "%s" is already linked to "%s".', $target, $source));
    }
}