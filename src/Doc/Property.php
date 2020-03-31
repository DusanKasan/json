<?php

namespace DusanKasan\JSON\Doc;

use DusanKasan\JSON\Doc\Converter\ConverterInterface;
use DusanKasan\JSON\Doc\Converter\Date;
use DusanKasan\JSON\Doc\Converter\DateTime;
use DusanKasan\JSON\Doc\Converter\StringRepresentation;
use ReflectionProperty;

class Property
{
    public bool $omitEmpty = false;
    public ?string $var = null;
    private ?ConverterInterface $converter = null;
    private array $converterParams;
    private ReflectionProperty $property;

    public function __construct(ReflectionProperty $prop)
    {
        $this->property = $prop;

        $doc = $prop->getDocComment();
        $docParts = explode('* @', $doc);
        $annotations = array_map(fn ($p) => trim(explode("\n", $p)[0]), $docParts);

        foreach ($annotations as $annotation) {
            $annotationParts = explode('(', $annotation);
            $annotationName = $annotationParts[0];

            if (strpos($annotationName, "json::omitnull") === 0) {
                $this->omitEmpty = true;
                continue;
            }

            if (strpos($annotationName, "var ") === 0) {
                $this->var = explode(' ', $annotationName)[1];
            }

            if (strpos($annotationName, "json::convert::") !== 0) {
                continue;
            }

            $converterName = substr($annotationName, 15);
            $converterParams = rtrim(join('(', array_slice($annotationParts, 1)), ')');
            $converterParams = json_decode($converterParams === '' ? '[]' : $converterParams, true);


            $converters = self::availableConverters();
            if (!array_key_exists($converterName, $converters)) {
                continue;
            }

            $this->converter = $converters[$converterName];
            $this->converterParams = $converterParams;
        }
    }

    /**
     * @return ConverterInterface[]
     */
    protected static function availableConverters(): array
    {
        return [
            'string' => new StringRepresentation(),
            'DateTime' => new DateTime(),
            'Date' => new Date(),
        ];
    }

    public function serialize($value)
    {
        if ($this->converter === null) {
            return $value;
        }

        return $this->converter->serialize($value, $this->converterParams);
    }

    public function deserialize($value)
    {
        if ($this->converter === null) {
            return $value;
        }

        return $this->converter->deserialize($value, $this->property->getType(), $this->converterParams);
    }
}