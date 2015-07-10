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

    public function testUnserializeWithCdata()
    {
        $xml = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<data>
  <row>
    <first_name><![CDATA[Bob]]></first_name>
    <last_name>Smith</last_name>
  </row>
  <row>
    <first_name>Jane</first_name>
    <last_name>Smith</last_name>
  </row>
</data>
XML;

        $data = Xml::unserialize($xml);
        $this->assertEquals('Bob', $data['row'][0]['first_name']);
    }

    public function testSerializeForPma()
    {
        $data = [
            'my_users' => [
                [
                    'first_name' => 'Bob',
                    'last_name'  => 'Smith'
                ],
                [
                    'first_name' => 'Jane',
                    'last_name'  => 'Smith'
                ]
            ]
        ];
        $xml = Xml::serialize($data, ['pma' => true]);
        $this->assertContains('<table name="my_users">', $xml);
        $this->assertContains('<column name="first_name">Bob</column>', $xml);
    }

}