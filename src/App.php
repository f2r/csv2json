<?php

declare(strict_types=1);

namespace f2r\Csv2Json;

use f2r\Csv2Json\File\CsvFileReader;
use f2r\Csv2Json\File\MappingFile;
use f2r\Csv2Json\Input\Options;
use f2r\Csv2Json\Mapping\Mapping;

final class App
{
    private function registerAutoload(): void
    {
        \spl_autoload_register(function ($classname) {
            if (strpos($classname, 'f2r\Csv2Json') !== 0) {
                throw new \RuntimeException("Unknow namespace for class {$classname}");
            }
            $file = __DIR__.'/'.str_replace('\\', '/', substr($classname, 13)).'.php';
            if (is_readable($file) === false) {
                throw new \RuntimeException("Could not found class {$classname} ({$file})");
            }

            require $file;
        });
    }

    private function registerErrorHandlers(): void
    {
        error_reporting(-1);
        set_error_handler(function (int $errno, string $message, string $file, int $line): bool {
            throw new \ErrorException($message, 0, $errno, $file, $line);
        });

        set_exception_handler(function(\Throwable $throwable) {
            fprintf(\STDERR, "Exception %s: %s\n", get_class($throwable), $throwable->getMessage());
            if ($throwable instanceof \ErrorException || $throwable instanceof \Error) {
                fwrite(\STDERR, $throwable->getTraceAsString()."\n");
            }
            exit(1);
        });
    }

    public static function getInstance(): self
    {
        $app = new self();
        $app->registerAutoload();
        $app->registerErrorHandlers();
        return $app;
    }

    public function execute(array $argv, $output = null): void
    {
        if ($output === null) {
            $output = \STDOUT;
        }
        array_shift($argv);
        $options = new Options($argv);

        if ($options->validate() === false) {
            fwrite(\STDERR, $options->getUsage()."\n");
            exit(1);
        }

        if ($options->needHelp()) {
            fwrite($output, $options->getUsage());
            exit(0);
        }

        if ($options->hasMappingFile() && $options->hasFields()) {
            fwrite(\STDERR, "Could not use --fields and --mapping at the same time\n\n");
            fwrite(\STDERR, $options->getUsage()."\n");
            exit(1);
        }

        $csvFile = new CsvFileReader($options->getCsvFile());

        $jsonOption = \JSON_UNESCAPED_UNICODE + \JSON_UNESCAPED_SLASHES;
        if ($options->isPrettyPrint()) {
            $jsonOption += \JSON_PRETTY_PRINT;
        }

        $mapping = $this->getMapping($options, $csvFile);

        if ($options->hasAggregation() === false) {
            $this->streamResult($output, $mapping->apply($csvFile), $jsonOption, $options->isPrettyPrint());
            return;
        }

        foreach ($mapping->apply($csvFile) as $item) {
            if ($options->hasAggregation()) {
                $name = $item[$options->getAggregationField()];
                unset($item[$options->getAggregationField()]);
                $result[$name] = $item;
            } else {
                $result[] = $item;
            }
        }
        fwrite($output, \json_encode($result, $jsonOption));
    }

    private function getMapping(Options $options, CsvFileReader $csvFile): Mapping
    {
        $mapping = null;

        if ($options->hasMappingFile()) {
            $mappingFile = new MappingFile($options->getMappingFile());
            $mapping = $mappingFile->loadMapping();
        } elseif ($options->hasFields()) {
            $mapping = new Mapping();
            foreach ($options->getFields() as $name) {
                $mapping->addField($name, Mapping::TYPE_AUTO);
            }
        }

        if ($mapping === null) {
            $mapping = new Mapping();
            foreach ($csvFile->getHeader() as $name) {
                $mapping->addField($name, Mapping::TYPE_AUTO);
            }
        }

        return $mapping;
    }

    private function streamResult($output, iterable $iterator, int $jsonOptions, bool $isPretty): void
    {
        fwrite($output, '[');
        $indent = '';
        if ($isPretty) {
            fwrite($output, "\n");
            $indent = '    ';
        }
        $jsonSeparator = '';
        foreach ($iterator as $item) {
            $json = \json_encode($item, $jsonOptions);
            if ($indent !== '') {
                $json = preg_replace('`^`m', $indent, $json);
            }
            fwrite($output, $jsonSeparator.$json);
            if ($jsonSeparator === '') {
                $jsonSeparator = ',';
                if ($isPretty) {
                    $jsonSeparator.= "\n";
                }
            }
        }
        if ($isPretty) {
            fwrite($output, "\n]\n");
        } else {
            fwrite($output, ']');
        }
    }
}