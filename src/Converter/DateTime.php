<?php

namespace DusanKasan\JSON\Converter;

use DusanKasan\JSON\ConverterInterface;
use ReflectionType;

class DateTime implements ConverterInterface
{
    /**
     * @param DateTime $value
     * @param ReflectionType $type
     * @param array $params
     * @return string
     */
    public function encode($value, ReflectionType $type, array $params = []): string
    {
        return $value->format($params['format'] ?? 'Y-m-d H:i:s');
    }

    public function decode($value, ReflectionType $type, array $params = []): \DateTime
    {
        return \DateTime::createFromFormat($params['format'] ?? 'Y-m-d H:i:s', $value);
    }
}