<?php

namespace DusanKasan\JSON;

use DateTime;
use DusanKasan\JSON\Doc\Property;
use DusanKasan\JSON\Type\Type;
use Exception;
use ReflectionClass;
use ReflectionProperty;
use stdClass;

class JSON
{
    public static function serialize($value): string
    {
        return json_encode(self::serializeValue($value));
    }

    protected static function serializeValue($value)
    {
        switch ($type = gettype($value)) {
            case 'boolean':
            case 'integer':
            case 'double':
            case 'string':
                return $value;
            case 'array':
                return array_map(fn ($v) => self::serializeValue($v), $value);
            case 'object':
                return self::serializeObject($value);
            default:
                throw new Exception("unable to serialize type: $type");
        }
    }

    protected static function serializeObject(object $value)
    {
        if ($value instanceof \JsonSerializable) {
            return $value->jsonSerialize();
        }

        $result = [];

        $class = new ReflectionClass($value);
        foreach ($class->getProperties(ReflectionProperty::IS_PUBLIC) as $prop) {
            $name = $prop->getName();
            $doc = new Property($prop);
            $serializeled = $doc->serialize($value->$name);
            if ($serializeled == null && $doc->omitEmpty) {
                continue;
            }

            $result[$name] = $serializeled;
        }

        return $result;
    }

    public static function deserialize(string $json, object $object)
    {
        $data = json_decode($json);
        $obj = self::deserializeClass($data, get_class($object));
        foreach ($obj as $prop => $val) {
            $object->$prop = $val;
        }
    }

    protected static function deserializeClass($value, string $className): object
    {
        $class = new ReflectionClass($className);
        $object = $class->newInstanceWithoutConstructor();

        if ($class->implementsInterface(JsonDeserializable::class)) {
            $object->jsonDeserialize($value);
            return $object;
        }

        if (!is_object($value) || !$value instanceof stdClass) {
            throw new Exception("partially deserialized data must be in the form of stdClass, $value given");
        }

        foreach ($class->getProperties(ReflectionProperty::IS_PUBLIC) as $prop) {
            $type = $prop->getType();
            $name = $prop->getName();
            $doc = new Property($prop);
            $val = $doc->deserialize($value->$name ?? null);
            if ($val === null && !$type->allowsNull()) {
                throw new Exception("property $name of class $className does not allow null values");
            }

            $object->$name = self::deserializeType($val, Type::fromReflectionType($type, $doc->var));
        }

        return $object;
    }

    protected static function deserializeType($value, Type $type)
    {
        if ($type === null) {
            return $value;
        }

        if ($value === null) {
            if ($type->allowsNull) {
                return $value;
            }

            throw new Exception("type does not allow null values");
        }

        if (is_object($value) && $type->name === get_class($value)) {
            return $value;
        }

        if ($type->isArray()) {
            return self::deserializeArray($value, $type->arrayItemsType());
        }

        switch ($type->name) {
            case 'resource':
            case 'object':
            case 'callable':
            case 'iterable':
                throw new Exception("non-deserializable type: {$type->name}");
            case 'string':
            case 'bool':
            case 'int':
            case 'float':
                return $value;
            case DateTime::class:
                return DateTime::createFromFormat('Y-m-d H:i:s', $value);
            default:
                return self::deserializeClass($value, $type->name);
        }
    }

    protected static function deserializeArray(array $value, ?Type $itemType)
    {
        if (!is_array($value)) {
            throw new Exception("unable to deserialize $value into array");
        }

        if ($itemType === null) {
            return $value;
        }

        return array_map(function ($item) use ($itemType) {
            return self::deserializeType($item, $itemType);
        }, $value);
    }
}

