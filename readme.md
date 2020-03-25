# JSON (WIP)

Opinionated JSON (un)marshalling library. Very much a work in progress.

## Example

```
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

$jsonString = `{
    "a": "asd", 
    "b": {
        "a": "qwe"
    }, 
    "c":"1", 
    "e": "123", 
    "f": "2000-01-01"
}`

$a = new A()
JSON::decode($jsonString, $a);
var_dump($a); // all properties will be set up
```
