<?php

namespace Pop\Data\Test;

use Pop\Data\Type\Xml;

class XmlTest extends \PHPUnit_Framework_TestCase
{

    public function testUnserializeAndSerialize()
    {
        $data = Xml::unserialize(file_get_contents(__DIR__ . '/tmp/data.xml'));
        $this->assertEquals('bar', $data['foo']);
        $string = Xml::serialize($data);
        $this->assertContains('<foo>bar</foo>', $string);
    }

}