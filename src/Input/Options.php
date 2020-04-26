<?php

declare(strict_types=1);

namespace f2r\Csv2Json\Input;

final class Options
{
    private array $argv;
    private ?array $fields;
    private ?string $aggregationField;
    private ?string $mappingFile;
    private ?string $csvFile;
    private bool $prettyPrint;
    private ?string $lastValidationError;
    private bool $help;

    public function __construct(array $argv)
    {
        $this->argv = $argv;
        $this->fields = null;
        $this->aggregationField = null;
        $this->mappingFile = null;
        $this->csvFile = null;
        $this->prettyPrint = false;
        $this->lastValidationError = null;
        $this->help = false;
    }

    public function getCsvFile(): string
    {
        return $this->csvFile;
    }

    public function getMappingFile(): string
    {
        return $this->mappingFile;
    }

    public function hasAggregation(): bool
    {
        return $this->aggregationField !== null;
    }

    public function getAggregationField(): string
    {
        return $this->aggregationField;
    }

    public function hasFields(): bool
    {
        return $this->fields !== null && $this->fields !== [];
    }
    public function getFields(): ?array
    {
        return $this->fields;
    }

    public function validate(): bool
    {
        $len = count($this->argv);
        if ($len === 0) {
            $this->lastValidationError = 'No parameter found';
            return false;
        }

        for ($index = 0; $index < $len; $index++) {
            $value = $this->argv[$index];
            if (substr($value, 0, 2) === '--') {
                $value = substr($value, 2);
                $equals = strpos($value, '=');
                if ($equals === false) {
                    $key = $value;
                    $value = null;
                    if (in_array($key, ['pretty', 'help']) === false) {
                        $index++;
                        $value = trim($this->argv[$index]);
                    }
                } else {
                    $key = trim(substr($value, 0, $equals));
                    $value = trim(substr($value, $equals + 1));
                }
                if ($this->setOption($key, $value) === false) {
                    return false;
                }
            } elseif ($this->csvFile === null) {
                $this->csvFile = $value;
            } else {
                $this->lastValidationError = 'parameter already set: '.$this->argv[$index];
                return false;
            }
        }

        if ($this->csvFile === null) {
            $this->lastValidationError = 'No CSV file name provided';
            return false;
        }
        return true;
    }

    private function setOption(string $key, ?string $value): bool
    {
        switch (strtolower($key)) {
            case 'fields':
                if ($this->fields === null) {
                    $this->fields = [];
                }
                $this->fields = array_merge($this->fields, preg_split('`\s*\W\s*`', $value));
                break;
            case 'aggregate':
                $this->aggregationField = trim($value);
                break;
            case 'mapping':
                $this->mappingFile = trim($value);
                break;
            case 'pretty':
                $this->prettyPrint = true;
                break;
            case 'help':
                $this->help = true;
                break;
            default:
                $this->lastValidationError = 'unknown options: '.$key;
                return false;
        }
        return true;
    }

    public function hasMappingFile(): bool
    {
        return $this->mappingFile !== null;
    }

    /**
     * @return string|null
     */
    public function getLastValidationError(): ?string
    {
        return $this->lastValidationError;
    }

    /**
     * @return bool
     */
    public function isPrettyPrint(): bool
    {
        return $this->prettyPrint;
    }

    public function getUsage(): string
    {
        return <<<END
        usage: csv2json [--mapping=<path>] [--aggregate=<field>] [--fields=<fields>] [--pretty] [--help] <csv-file>
        
        Options:
            --help              This help
            --pretty            Return human readable JSON
            --fields=<path>     Return only these fields in JSON output (not compatible with mapping)
                                ex: --fields=id,name,date
            --aggregate=<field> Aggregate a field in JSON output
            --mapping=<path>    Use a file to specify fields and type

        Mapping file is composed of <name>=<type>
            <name>: name of field
            <type>: field type: int, string, bool, float, date, time and datetime
                    If type is prefixed by a "?", that's mean the field is nullable 
        END;

    }

    public function needHelp(): bool
    {
        return $this->help;
    }

}