<?php

namespace derhasi\symlinker\Exception;

class TargetAlreadyLinkedException extends \RuntimeException {

    public function __construct($target, $source)
    {
        parent::__construct(sprintf('Target "%s" is already linked to another source than "%s".', $target, $source));
    }
}