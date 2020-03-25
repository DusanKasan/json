<?php

namespace DusanKasan\JSON;


use DateTime;
use PHPUnit\Framework\TestCase;

class JSONTest extends TestCase
{
    function testDecode()
    {
        $a =  new A();
        JSON::decode('{"a": "asd", "b": {"a": "qwe"}, "c":"1", "e": "123", "f": "2000-01-01"}', $a);
        $this->assertEquals('asd', $a->a);
        $this->assertEquals('qwe', $a->b->a);
        $this->assertEquals(true, $a->c);
        $this->assertEquals(null, $a->d);
        $this->assertEquals(123, $a->e);
        $this->assertEquals('2000-01-01', $a->f->format('Y-m-d'));
    }
}

class A
{
    public string $a;
    public ?B $b;
    /**
     * @JSON::Converter::StringRepresentation({"true":"1", "false":"0"})
     */
    public bool $c;
    public ?bool $d;
    /**
     * @JSON::Converter::StringRepresentation()
     */
    public ?int $e;
    /**
     * @JSON::Converter::DateTime({"format": "Y-m-d"})
     */
    public DateTime $f;
}

class B
{
    public string $a;
}