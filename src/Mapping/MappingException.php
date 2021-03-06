<?php

declare(strict_types=1);

namespace f2r\Csv2Json\Mapping;

class MappingException extends \Exception
{
    public function __construct(string $message, ...$parameters)
    {
        parent::__construct(sprintf($message, ...$parameters));
    }

}