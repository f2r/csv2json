<?php

declare(strict_types=1);

namespace f2r\Csv2Json\Mapping;

final class Mapping
{
    public const TYPE_NULLABLE = true;
    public const TYPE_NOT_NULLABLE = false;

    public const TYPE_INT = 'int';
    public const TYPE_STRING = 'string';
    public const TYPE_FLOAT = 'float';
    public const TYPE_BOOL = 'bool';
    public const TYPE_DATE = 'date';
    public const TYPE_TIME = 'time';
    public const TYPE_DATETIME = 'datetime';
    public const TYPE_AUTO = 'auto';

    private const TYPE_ALLOWED = [
        self::TYPE_INT => self::TYPE_INT,
        self::TYPE_STRING => self::TYPE_STRING,
        self::TYPE_FLOAT => self::TYPE_FLOAT,
        self::TYPE_BOOL => self::TYPE_BOOL,
        self::TYPE_DATE => self::TYPE_DATE,
        self::TYPE_TIME => self::TYPE_TIME,
        self::TYPE_DATETIME => self::TYPE_DATETIME,
        self::TYPE_AUTO => self::TYPE_AUTO,
    ];

    private const TYPE_ALIAS = [
        'integer' => self::TYPE_INT,
        'boolean' => self::TYPE_BOOL,
        'real' => self::TYPE_FLOAT,
    ];

    private const BOOL_ALIAS = [
        'on' => true,
        'off' => false,
        'yes' => true,
        'no' => false,
        'true' => true,
        'false' => false,
    ];

    /**
     * @var array<string, array{type:string, nullable:bool}>
     */
    private $fields;

    public function addField(string $name, string $type, bool $nullable = self::TYPE_NOT_NULLABLE): void
    {
        $type = strtolower($type);
        $type = self::TYPE_ALIAS[$type] ?? $type;
        if (isset(self::TYPE_ALLOWED[$type]) === false) {
            throw new MappingException('Unknow type %s', $type);
        }

        $this->fields[$name] = ['type' => $type, 'nullable' => $nullable];
    }

    public function apply(iterable $iterable): \Generator
    {
        foreach ($iterable as $index => $item) {
            if (is_array($item) === false) {
                throw new MappingException('Could not apply mapping on %s', gettype($item));
            }
            $mappedItem = [];
            foreach ($this->fields as $name => ['type' => $type, 'nullable' => $nullable]) {
                $value = $item[$name] ?? null;
                if (is_string($value)) {
                    $value = trim($value);
                }
                if ($nullable && is_string($value) && (strtolower($value) === 'null' || $value === '')) {
                    $value = null;
                }
                if ($nullable === false && $value === null) {
                    throw new ValueCouldNotBeNullException($name);
                }
                $mappedItem[$name] = $this->cast($type, $value);
            }
            yield $index => $mappedItem;
        }
    }

    private function cast($type, ?string $value)
    {
        if ($value === null) {
            return null;
        }
        if ($type === self::TYPE_AUTO) {
            $type = $this->detectType($value);
        }
        switch ($type) {
            case self::TYPE_INT:
                return (int) $value;
            case self::TYPE_STRING:
                return (string) $value;
            case self::TYPE_FLOAT:
                return (float) $value;
            case self::TYPE_BOOL:
                return (bool) (self::BOOL_ALIAS[$value] ?? $value);
            case self::TYPE_DATE:
                $date = \DateTime::createFromFormat('Y-m-d', $value);
                if ($date === false) {
                    throw new WrongDatetimeFormatException('Y-m-d', $value);
                }
                return $date;
            case self::TYPE_TIME:
                $date = \DateTime::createFromFormat('H:i:s', $value);
                if ($date === false) {
                    throw new WrongDatetimeFormatException('H:i:s', $value);
                }
                return $date;
            case self::TYPE_DATETIME:
                $date = \DateTime::createFromFormat('Y-m-d H:i:s', $value);
                if ($date === false) {
                    throw new WrongDatetimeFormatException('Y-m-d H:i:s', $value);
                }
                return $date;
        }
        return $value;
    }

    private function detectType(string $value): string
    {
        if (is_numeric($value)) {
            if (preg_match('`^\d+$`', $value) === 1) {
                return self::TYPE_INT;
            }
            return self::TYPE_FLOAT;
        }
        if (isset(self::BOOL_ALIAS[$value])) {
            return self::TYPE_BOOL;
        }
        if (preg_match('`^\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2}$`', $value) === 1) {
            return self::TYPE_DATETIME;
        }
        if (preg_match('`^\d{2}:\d{2}:\d{2}$`', $value) === 1) {
            return self::TYPE_TIME;
        }
        if (preg_match('`^\d{4}-\d{2}-\d{2}$`', $value) === 1) {
            return self::TYPE_DATE;
        }
        return self::TYPE_STRING;
    }
}