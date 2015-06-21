<?php

namespace Pop\Data\Test;

use Pop\Data\Type\Sql;

class SqlTest extends \PHPUnit_Framework_TestCase
{

    public function testUnserializeAndSerialize()
    {
        $data = Sql::unserialize(file_get_contents(__DIR__ . '/tmp/data.sql'));
        $this->assertEquals('testuser1', $data['my_table'][0]['username']);
        $string = Sql::serialize($data);
        $this->assertContains("(1, 'testuser1', 'testuser1@test.com'", $string);
    }

}