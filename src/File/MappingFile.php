<?php

declare(strict_types=1);

namespace f2r\Csv2Json\File;

use f2r\Csv2Json\Mapping\Mapping;
use SplFileObject;

final class MappingFile
{
    private SplFileObject $fileObject;

    public function __construct($file)
    {
        assert(is_string($file) || $file instanceof SplFileObject, __METHOD__.' parameter must be a string or a SplFileObject');
        if (is_string($file)) {
            try {
                $this->fileObject = new SplFileObject($file, 'r');
            } catch (\RuntimeException $exception) {
                throw new ReadFileException($exception->getMessage());
            }
        } else {
            $this->fileObject = $file;
        }
    }

    public function loadMapping(): Mapping
    {
        $mapping = new Mapping();
        foreach ($this->fileObject as $line) {
            foreach (parse_ini_string($line) as $name => $value) {
                $value = trim(preg_replace('`#.*`', '', $value));
                $nullable = Mapping::TYPE_NOT_NULLABLE;
                if ($value[0] === '?') {
                    $value = substr($value, 1);
                    $nullable = Mapping::TYPE_NULLABLE;
                }
                $mapping->addField($name, $value, $nullable);
            }
        }

        return $mapping;
    }


}