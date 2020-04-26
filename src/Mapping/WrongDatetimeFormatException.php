<?php

declare(strict_types=1);

namespace f2r\Csv2Json\Mapping;

final class WrongDatetimeFormatException extends MappingException
{

    public function __construct(string $format, string $value)
    {
        parent::__construct('Wrong DateTime format "%s" for value "%s"', $format, $value);
    }
}