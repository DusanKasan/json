<?php

namespace DusanKasan\JSON\Converter;

use DusanKasan\JSON\ConverterInterface;
use Exception;
use ReflectionType;

class StringRepresentation implements ConverterInterface
{
    public function encode($value, array $params = [])
    {
        if ($value === null) {
            return null;
        }

        return (string)$value;
    }

    public function decode($value, ReflectionType $type, array $params = [])
    {
        switch ($type->getName()) {
            case 'int':
                return (int)$value;
            case 'float':
                return (float)$value;
            case 'bool':
                $true = $params['true'] ?? 'true';
                $false = $params['false'] ?? 'false';
                switch ($value) {
                    case $true:
                        return true;
                    case $false:
                        return false;
                    default:
                        throw new Exception("unable to decode value $value as boolean, possible values: $true/$false");
                }
            default:
                throw new Exception("invalid type: {$type->getName()}");
        }
    }
}