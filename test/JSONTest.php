<?php

namespace DusanKasan\JSON\Test;

use DateTime;
use DateTimeZone;
use DusanKasan\JSON\JSON;
use DusanKasan\JSON\JsonDeserializable;
use PHPUnit\Framework\TestCase;
use function DusanKasan\JSON\AAA;

class JSONTest extends TestCase
{
    function testDecode()
    {
        $input = '{"a": "asd", "b": {"a": "qwe"}, "c":"1", "e": "123", "f": "2000-01-01", "g": "2000-01-01", "h": [{"a": "qwe1"}]}';
        $a = new A();
        JSON::deserialize($input, $a);
        $this->assertJsonStringEqualsJsonString($input, JSON::serialize($a));
    }
}

class A
{
    public string $a;
    public ?B $b;
    /**
     * @json::convert::string({"true":"1", "false": "0"})
     */
    public bool $c;
    /**
     * @json::omitnull
     */
    public ?bool $d;
    /**
     * @json::convert::string()
     */
    public ?int $e;
    /**
     * @json::convert::DateTime({"format": "Y-m-d"})
     */
    public DateTime $f;
    public Date $g;
    /**
     * @var \DusanKasan\JSON\Test\B[]
     */
    public array $h;
}

class B
{
    public string $a;
}

class Date implements \JsonSerializable, JsonDeserializable
{
    private DateTime $datetime;

    public function __construct(string $date)
    {
        $this->datetime = DateTime::createFromFormat('Y-m-d', $date);
    }

    public function jsonDeserialize($value)
    {
        $this->datetime = DateTime::createFromFormat('Y-m-d', $value);
    }

    public function jsonSerialize()
    {
        return $this->datetime->format('Y-m-d');
    }
}