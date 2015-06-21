<?php

namespace Pop\Data\Test;

use Pop\Data\Type\Csv;

class CsvTest extends \PHPUnit_Framework_TestCase
{

    public function testUnserializeAndSerialize()
    {
        $data = Csv::unserialize(file_get_contents(__DIR__ . '/tmp/data.csv'));
        $this->assertEquals('testuser1', $data[0]['username']);
        $string = Csv::serialize($data);
        $this->assertContains('testuser1,testuser1@test.com', $string);
    }

}