<?php

use f2r\Csv2Json\Mapping\Mapping;
use f2r\Csv2Json\Test\UnitTest;
use f2r\Csv2Json\Mapping\ValueCouldNotBeNullException;

$mapping = new Mapping();
$mapping->addField('id', 'integer');
$mapping->addField('name', 'string');
$mapping->addField('date', 'date');

function iterator() {
    yield ['id' => '   1', 'name' => 'foo', 'date' => '2020-03-10', 'category' => 'cat1'];
    yield ['id' => '3   ', 'name' => 'bar ', 'date' => '2020-03-11', 'category' => 'cat2'];
    yield ['id' => '  7 ', 'name' => 'baz', 'date' => '2020-03-12', 'category' => 'cat3'];
}

foreach ($mapping->apply(iterator()) as $i => $item) {
    switch ($i) {
        case 0:
            UnitTest::assert(isset($item['id']) && $item['id'] === 1);
            UnitTest::assert(isset($item['name']) && $item['name'] === 'foo');
            UnitTest::assert(isset($item['date']) && $item['date'] == DateTime::createFromFormat('Y-m-d', '2020-03-10'));
            UnitTest::assert(isset($item['category']) === false);
            break;
        case 1:
            UnitTest::assert(isset($item['id']) && $item['id'] === 3);
            UnitTest::assert(isset($item['name']) && $item['name'] === 'bar');
            UnitTest::assert(isset($item['date']) && $item['date'] == DateTime::createFromFormat('Y-m-d', '2020-03-11'));
            UnitTest::assert(isset($item['category']) === false);
            break;
        case 2:
            UnitTest::assert(isset($item['id']) && $item['id'] === 7);
            UnitTest::assert(isset($item['name']) && $item['name'] === 'baz');
            UnitTest::assert(isset($item['date']) && $item['date'] == DateTime::createFromFormat('Y-m-d', '2020-03-12'));
            UnitTest::assert(isset($item['category']) === false);
            break;
        default:
            UnitTest::assert(false);
    }
}

UnitTest::assertThatThrowException(ValueCouldNotBeNullException::class, function() use ($mapping) {
    foreach ($mapping->apply([['x' => 'y']]) as $item);
});

$mapping->addField('unknow', 'string', Mapping::TYPE_NULLABLE);
foreach ($mapping->apply([['id' => '11','name' => 'NAME', 'date' => '2020-03-10']]) as $item) {
    UnitTest::assert(isset($item['id']) && $item['id'] === 11);
    UnitTest::assert(isset($item['name']) && $item['name'] === 'NAME');
    UnitTest::assert(isset($item['date']) && $item['date'] == DateTime::createFromFormat('Y-m-d', '2020-03-10'));
    UnitTest::assert(array_key_exists('unknow', $item) === true && $item['unknow'] === null);

}
