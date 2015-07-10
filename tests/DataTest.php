<?php

namespace Pop\Data\Test;

use Pop\Data\Data;

class DataTest extends \PHPUnit_Framework_TestCase
{

    public function testConstructor()
    {
        $data = new Data([
            'foo' => 'bar'
        ]);
        $this->assertInstanceOf('Pop\Data\Data', $data);
        $this->assertEquals('bar', $data->getData()['foo']);
    }

    public function testConstructorString()
    {
        $data = new Data('{"foo" : "bar"}');
        $this->assertInstanceOf('Pop\Data\Data', $data);
        $this->assertEquals('{"foo" : "bar"}', $data->getString());
    }

    public function testConstructorFile()
    {
        $data = new Data(__DIR__ . '/tmp/data.json');
        $this->assertInstanceOf('Pop\Data\Data', $data);
        $this->assertEquals('{"foo" : "bar"}', $data->getString());
    }

    public function testSerialize()
    {
        $data = new Data([
            'foo' => 'bar'
        ]);
        $data->serialize('json');
        $this->assertContains('"foo": "bar"', $data->getString());
    }

    public function testSerializeYaml()
    {
        $data = new Data([
            'foo' => 'bar'
        ]);
        $data->serialize('yml');
        $this->assertContains('foo: bar', $data->getString());
    }

    public function testSerializeException()
    {
        $this->setExpectedException('Pop\Data\Exception');
        $data = new Data([
            'foo' => 'bar'
        ]);
        $data->serialize('bad');
    }

    public function testUnserialize()
    {
        $data = new Data(__DIR__ . '/tmp/data.json');
        $data->unserialize();
        $this->assertEquals('bar', $data->getData()['foo']);
    }

    public function testConvert()
    {
        $data = new Data(__DIR__ . '/tmp/data.xml');
        $this->assertContains('foo', $data->convert('json'));
    }

    public function testWriteToFile()
    {
        $data = new Data([
            'foo' => 'bar'
        ]);
        $data->serialize('json');
        $data->writeToFile(__DIR__ . '/tmp/test.json');
        $this->assertFileExists(__DIR__ . '/tmp/test.json');
        unlink(__DIR__ . '/tmp/test.json');
    }

    public function testWriteToFileException()
    {
        $this->setExpectedException('Pop\Data\Exception');
        $data = new Data([
            'foo' => 'bar'
        ]);
        $data->writeToFile(__DIR__ . '/tmp/test.json');
    }

    /**
     * @runInSeparateProcess
     */
    public function testOutputToHttp1()
    {
        $data = new Data([
            'foo' => 'bar'
        ]);
        $data->serialize('json');

        ob_start();
        $data->outputToHttp('test.json', false);
        $result = ob_get_clean();

        $this->assertContains('"foo": "bar"', $result);
    }

    /**
     * @runInSeparateProcess
     */
    public function testOutputToHttp2()
    {
        $data = new Data([
            'foo' => 'bar'
        ]);
        $data->serialize('json');

        ob_start();
        $data->outputToHttp();
        $result = ob_get_clean();

        $this->assertContains('"foo": "bar"', $result);
    }

    public function testOutputToHttpException()
    {
        $this->setExpectedException('Pop\Data\Exception');
        $data = new Data([
            'foo' => 'bar'
        ]);
        $data->outputToHttp('test.json', false);
    }

    public function testAutoDetectJson()
    {
        $data = new Data(__DIR__ . '/tmp/data.json');
        $this->assertEquals('json', $data->getType());
    }

    public function testAutoDetectXml()
    {
        $data = new Data(__DIR__ . '/tmp/data.xml');
        $this->assertEquals('xml', $data->getType());
    }

    public function testAutoDetectSql()
    {
        $data = new Data(__DIR__ . '/tmp/data.sql');
        $this->assertEquals('sql', $data->getType());
    }

    public function testAutoDetectYaml()
    {
        $data = new Data(__DIR__ . '/tmp/data.yaml');
        $this->assertEquals('yaml', $data->getType());
    }

    public function testAutoDetectCsv()
    {
        $data = new Data(__DIR__ . '/tmp/data.csv');
        $this->assertEquals('csv', $data->getType());
    }

}