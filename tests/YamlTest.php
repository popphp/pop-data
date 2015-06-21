<?php

namespace Pop\Data\Test;

use Pop\Data\Type\Yaml;

class YamlTest extends \PHPUnit_Framework_TestCase
{

    public function testUnserializeAndSerialize()
    {
        $data = Yaml::unserialize(file_get_contents(__DIR__ . '/tmp/data.yaml'));
        $this->assertEquals('bar', $data['foo']);
        $string = Yaml::serialize($data);
        $this->assertContains('foo: bar', $string);
    }

}