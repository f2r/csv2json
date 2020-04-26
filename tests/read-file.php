<?php

use f2r\Csv2Json\File\CsvFileReader;
use f2r\Csv2Json\Test\MemoryFile;
use f2r\Csv2Json\Test\UnitTest;


$mock = new MemoryFile(<<<END
"na,me"#  id  #date#entête#"ça ira, aussi"
foo#5#2020-05-03#"quelque chose"#rien
foo#9#2020-05-03#"quelque#chose"#rien
bar#1#2020-03-21#"quelque chose"#rien
boo#4#2020-03-14#"quelque chose"#rien
foo#12#2020-05-07#"quelque chose"#rien
boo#5#2020-02-19#"quelque chose"#rien
far#10#2020-04-30#"quelque chose"#rien


END
);

$file = new CsvFileReader($mock);
$result = iterator_to_array($file);

UnitTest::assert(is_array($result));
UnitTest::assert(count($result) === 7);
UnitTest::assert(array_keys($result[0]) === ['na,me', 'id', 'date', 'entête', 'ça ira, aussi']);
UnitTest::assert($result[0] === ['na,me' => 'foo','id' => '5','date' => '2020-05-03','entête' => 'quelque chose', 'ça ira, aussi' => 'rien']);