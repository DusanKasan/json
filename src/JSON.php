<?php

namespace DusanKasan\JSON;

use DusanKasan\JSON\Converter\Date;
use DusanKasan\JSON\Converter\DateTime;
use DusanKasan\JSON\Converter\StringRepresentation;
use Exception;
use ReflectionClass;
use ReflectionProperty;
use ReflectionType;
use stdClass;

class JSON
{
    /**
     * @return ConverterInterface[]
     */
    protected static function converters(): array
    {
        return [
            'StringRepresentation' => new StringRepresentation(),
            'DateTime' => new DateTime(),
            'Date' => new Date(),
        ];
    }

    public static function encode($value): string
    {
    }

    public static function decode(string $json, object $object)
    {
        $data = json_decode($json);
        $obj = self::decodeClass($data, get_class($object));
        foreach ($obj as $prop => $val) {
            $object->$prop = $val;
        }
    }

    protected static function decodeClass(stdClass $value, string $className): object
    {
        $class = new ReflectionClass($className);
        $object = $class->newInstanceWithoutConstructor();
        foreach ($class->getProperties(ReflectionProperty::IS_PUBLIC) as $prop) {
            $name = $prop->getName();
            $type = $prop->getType();
            $val = self::decodeUsingDoc($value->$name ?? null, $type, $prop->getDocComment());
            if ($val === null && !$type->allowsNull()) {
                throw new Exception("property $name of class $className does not allow null values");
            }

            $object->$name = self::decodeType($val, $type);
        }


        return $object;
    }

    protected static function decodeUsingDoc($value, ReflectionType $type, $doc)
    {
        $docParts = explode('* @', $doc);
        $annotations = array_map(fn ($p) => trim(explode("\n", $p)[0]), $docParts);

        foreach ($annotations as $annotation) {
            $annotationParts = explode('(', $annotation);
            $annotationName = $annotationParts[0];



            if (strpos($annotationName, "JSON::Converter::") !== 0) {
                continue;
            }

            $converterName = substr($annotationName, 17);
            $converterParams = rtrim(join('(', array_slice($annotationParts, 1)), ')');
            $converterParams = json_decode($converterParams === '' ? '[]' : $converterParams, true);

            $converters = self::converters();
            if (!array_key_exists($converterName, $converters)) {
                continue;
            }

            $value = $converters[$converterName]->decode($value, $type, $converterParams);
        }

        return $value;
    }

    protected static function decodeType($value, ReflectionType $type)
    {
        if ($type === null) {
            return $value;
        }

        if ($value === null) {
            if ($type->allowsNull()) {
                return $value;
            }

            throw new Exception("type does not allow null values");
        }

        if (!$type->isBuiltin()) {
            if ($type->getName() === get_class($value)) {
                return $value;
            }

            if ($type->getName() === \DateTime::class) {
                if (is_string($value)) {
                    return \DateTime::createFromFormat('Y-m-d H:i:s', $value);
                }

                throw new Exception("unable to create datetime from " . gettype($value) . " provided");
            }

            if (!$value instanceof stdClass) {
                throw new Exception("type accepts stdClass, " . gettype($value) . " provided");
            }

            return self::decodeClass($value, $type->getName());
        }

        switch ($type->getName()) {
            case 'string':
                if (!is_string($value)) {
                    throw new Exception("value is not string: $value");
                }
                return $value;
            case 'bool':
                if (!is_bool($value)) {
                    throw new Exception("value is not bool: $value");
                }
                return $value;
            case 'int':
                if (!is_int($value)) {
                    throw new Exception("value is not int: $value");
                }
                return $value;
            case 'float':
                if (!is_float($value)) {
                    throw new Exception("value is not float: $value");
                }
                return $value;
            default:
                throw new Exception("unknown type: {$type->getName()}");
        }
    }
}