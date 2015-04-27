<?php

namespace derhasi\symlinker\Exception;

class SourceNotGiven extends \RuntimeException {

    public function __construct($target)
    {
        parent::__construct(sprintf('No source given for link "%s".', $target));
    }
}