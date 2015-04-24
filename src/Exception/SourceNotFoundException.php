<?php

namespace derhasi\symlinker\Exception;

class SourceNotFoundException extends \RuntimeException {

    public function __construct($target, $source)
    {
        parent::__construct(sprintf('Source "%s" for target "%s does not exist.', $source, $target));
    }
}