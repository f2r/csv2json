<?php
declare(strict_types=1);

namespace f2r\Csv2Json\File;

final class ReadFileException extends \Exception
{
    public function __construct(string $message, ...$parameters)
    {
        parent::__construct(sprintf($message, ...$parameters));
    }
}