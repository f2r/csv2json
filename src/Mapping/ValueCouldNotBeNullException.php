<?php

declare(strict_types=1);

namespace f2r\Csv2Json\Mapping;

final class ValueCouldNotBeNullException extends MappingException
{
    public function __construct(string $fieldName)
    {
        parent::__construct('Field "%s" could not be null', $fieldName);
    }

}