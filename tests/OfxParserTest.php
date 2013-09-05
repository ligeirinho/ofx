<?php

use Sinergia\Ofx\OfxParser;

class Proxy
{
    protected $__object;

    public function __construct($obj)
    {
        $this->__object = $obj;
    }

    public function __call($methodName, $args)
    {
        $reflection = new ReflectionObject($this->__object);
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($this->__object, $args);
    }
}

class OfxParserTest extends PHPUnit_Framework_Testcase
{
    /**
     * @var OfxParser
     */
    protected $parser;

    protected function setUp()
    {
        $this->parser = new Proxy( new OfxParser() );
    }

    protected function expose($object)
    {
        $reflection = new ReflectionObject($object);
        foreach ($reflection->getMethods() as $method) {
            $method->setAccessible(true);
        }
        foreach ($reflection->getProperties() as $property) {
            $property->setAccessible(true);
        }
    }

    protected function getFixtureDir()
    {
        return __DIR__.'/fixtures';
    }

    public function testGetHeaderBody()
    {
        $ofx = <<<S
OFXHEADER:100

<OFX></OFX>

S;
        list($header, $body) = $this->parser->splitHeaderBody($ofx);
        $this->assertEquals("OFXHEADER:100", $header);
        $this->assertEquals("<OFX></OFX>", $body);
    }

    public function testParseHeader()
    {
        $header = <<<S
OFXHEADER:100
ENCODING:USASCII
COMPRESSION:NONE
S;
        $headers = $this->parser->parseHeader($header);
        $expected = array(
            "OFXHEADER" => "100",
            "ENCODING" => "USASCII",
            "COMPRESSION" => "NONE"
        );
        $this->assertEquals($expected, $headers);
    }

    public function testFixBody()
    {
        $body = <<<S
<STATUS>
<CODE>0
<SEVERITY>INFO
S;
        $expected = <<<S
<STATUS>
<CODE>0</CODE>
<SEVERITY>INFO</SEVERITY>
S;
        $fixed = $this->parser->fixBody($body);
        $this->assertEquals($expected, $fixed);
    }

    public function testBodyAsXml()
    {
        $body = <<<S
<STATUS>
<CODE>0
<SEVERITY>INFO
</STATUS>
S;
        $body = $this->parser->fixBody($body);
        $xml = $this->parser->bodyAsXml($body);
        $this->assertEquals("0", $xml->CODE);
        $this->assertEquals("INFO", $xml->SEVERITY);

        $expected_xml = <<<S
<?xml version="1.0"?>
<STATUS>
<CODE>0</CODE>
<SEVERITY>INFO</SEVERITY>
</STATUS>

S;
        $this->assertEquals($expected_xml, $xml->asXML());
    }
}
