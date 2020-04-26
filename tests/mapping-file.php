<?php
use f2r\Csv2Json\File\MappingFile;
use f2r\Csv2Json\Test\UnitTest;
$file = new \f2r\Csv2Json\Test\MemoryFile(
<<<FILE
#####################
id    = integer
name  = string   # comment

value = ?integer
date=date

FILE
);

$mappingFile = new MappingFile($file);
$mapping = $mappingFile->loadMapping();

foreach ($mapping->apply([['id' => '3', 'name' => 'foo', 'date' => '2020-01-30']]) as $item) {
    UnitTest::assert(isset($item['id']) && $item['id'] === 3);
    UnitTest::assert(isset($item['name']) && $item['name'] === 'foo');
    UnitTest::assert(isset($item['date']) && $item['date'] instanceof DateTime);
    UnitTest::assert(array_key_exists('value' , $item) && $item['value'] === null);
}