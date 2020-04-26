<?php

use f2r\Csv2Json\Input\Options;
use f2r\Csv2Json\Test\UnitTest;

$options = new Options([]);
UnitTest::assert($options->validate() === false);