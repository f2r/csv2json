<?php
declare(strict_types=1);

namespace f2r\Csv2Json\File;

use f2r\Csv2Json\Mapping;
use IteratorAggregate;
use SplFileObject;

final class CsvFileReader implements IteratorAggregate
{
    private const AUTO_DETECT_SEPARATOR_LINES_NUMBER = 30;

    private ?SplFileObject $fileObject;
    private ?array $header;

    /**
     * @param string|SplFileObject $file
     * @throws ReadFileException
     */
    public function __construct($file)
    {
        assert(is_string($file) || $file instanceof SplFileObject, __METHOD__.' parameter must be a string or a SplFileObject');
        if (is_string($file)) {
            $this->fileObject = new SplFileObject($file, 'r');
        } else {
            $this->fileObject = $file;
        }
        $this->header = $this->autoDetectSeparatorAndGetFirstLine();
    }

    public function getHeader(): array
    {
        return $this->header;
    }

    public function getIterator(): \Generator
    {
        $isFirstLine = true;
        foreach ($this->fileObject as $items) {
            // skip empty lines
            if ($items === false || (count($items) === 1 && $items[0] === null) ) {
                continue;
            }
            if ($isFirstLine) {
                $isFirstLine = false;
                continue;
            }
            $items = array_map('trim', $items);

            yield array_combine($this->header, $items);
        }
    }

    /**
     * @throws ReadFileException
     */
    private function autoDetectSeparatorAndGetFirstLine(): array
    {
        $flags = SplFileObject::DROP_NEW_LINE + SplFileObject::SKIP_EMPTY;
        $this->fileObject->setFlags($flags);
        // Read the first line (header)
        $firstLine = trim($this->fileObject->fgets());
        // Found all non-word characters
        preg_match_all('`[^\w"\']`u', $firstLine, $matches);
        // count and order characters. Normally, the most frequent character should be the separating character.
        $characters = array_count_values($matches[0]);
        arsort($characters);

        // Read file as a CSV file
        $this->fileObject->setFlags($flags + SplFileObject::READ_CSV);
        // Try to read the file with different separator. If the number of fields is the same in the header and in the
        // whole body lines, we found it
        foreach (array_keys($characters) as $separator) {
            $this->fileObject->fseek(0);
            $this->fileObject->setCsvControl($separator, '"');
            $previousCount = $count = null;
            $lineNumber = 0;
            $firstLine = null;
            foreach ($this->fileObject as $items) {
                if (++$lineNumber > self::AUTO_DETECT_SEPARATOR_LINES_NUMBER || $items === false) {
                    break;
                }

                $count = count($items);
                $items = array_map('trim', $items);
                if ($lineNumber === 1) {
                    $firstLine = $items;
                    $previousCount = $count;
                    continue;
                }

                if ($count !== $previousCount) {
                    // No match !
                    break;
                }
                $previousCount = $count;
            }
            if ($previousCount === $count) {
                $this->fileObject->fseek(0);
                return $firstLine;
            }
        }
        throw new ReadFileException("Could not read CSV file \"{$this->fileObject->getFilename()}\": no suitable separator found");
    }
}