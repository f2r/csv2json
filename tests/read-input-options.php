<?php

use f2r\Csv2Json\Input\Options;
use f2r\Csv2Json\Test\UnitTest;

$options = new Options(['file.csv', '--mapping', 'descfile', '--aggregate=name', '--fields', 'f1,f2,f3']);
UnitTest::assert($options->validate(), $options->getLastValidationError());
UnitTest::assert($options->getCsvFile() === 'file.csv');
UnitTest::assert($options->hasMappingFile());
UnitTest::assert($options->getMappingFile() === 'descfile');
UnitTest::assert($options->hasAggregation());
UnitTest::assert($options->getAggregationField() === 'name');
UnitTest::assert($options->getFields() === ['f1', 'f2', 'f3']);
