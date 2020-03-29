<?php

namespace DusanKasan\JSON\Doc;

use DusanKasan\JSON\Converter\Date;
use DusanKasan\JSON\Converter\DateTime;
use DusanKasan\JSON\Converter\StringRepresentation;
use DusanKasan\JSON\ConverterInterface;
use ReflectionProperty;

class Property
{
    public bool $omitEmpty = false;
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

            if (strpos($annotationName, "JSON::OmitEmpty") === 0) {
                $this->omitEmpty = true;
                continue;
            }

            if (strpos($annotationName, "JSON::Converter::") !== 0) {
                continue;
            }

            $converterName = substr($annotationName, 17);
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
            'StringRepresentation' => new StringRepresentation(),
            'DateTime' => new DateTime(),
            'Date' => new Date(),
        ];
    }

    public function encode($value)
    {
        if ($this->converter === null) {
            return $value;
        }

        return $this->converter->encode($value, $this->converterParams);
    }

    public function decode($value)
    {
        if ($this->converter === null) {
            return $value;
        }

        return $this->converter->decode($value, $this->property->getType(), $this->converterParams);
    }
}