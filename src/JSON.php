<?php

namespace DusanKasan\JSON;

use DateTime;
use DusanKasan\JSON\Doc\Property;
use Exception;
use ReflectionClass;
use ReflectionProperty;
use ReflectionType;
use stdClass;

class JSON
{
    public static function encode($value): string
    {
        return json_encode(self::encodeValue($value));
    }

    protected static function encodeValue($value)
    {
        switch ($type = gettype($value)) {
            case 'boolean':
            case 'integer':
            case 'double':
            case 'string':
                return $value;
            case 'array':
                // TODO
                return null;
            case 'object':
                return self::encodeObject($value);
            default:
                throw new Exception("unable to encode type: $type");
        }
    }

    protected static function encodeObject(object $value)
    {
        $result = [];

        $class = new ReflectionClass($value);
        foreach ($class->getProperties(ReflectionProperty::IS_PUBLIC) as $prop) {
            $name = $prop->getName();
            $doc = new Property($prop);
            $encoded = $doc->encode($value->$name);
            if ($encoded == null && $doc->omitEmpty) {
                continue;
            }

            $result[$name] = $encoded;
        }

        return $result;
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
            $type = $prop->getType();
            $name = $prop->getName();
            $doc = new Property($prop);
            $val = $doc->decode($value->$name ?? null);
            if ($val === null && !$type->allowsNull()) {
                throw new Exception("property $name of class $className does not allow null values");
            }

            $object->$name = self::decodeType($val, $type);
        }


        return $object;
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

            if ($type->getName() === DateTime::class) {
                if (is_string($value)) {
                    return DateTime::createFromFormat('Y-m-d H:i:s', $value);
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
            case 'bool':
            case 'int':
            case 'float':
                return $value;
            default:
                throw new Exception("unknown type: {$type->getName()}");
        }
    }
}