<?php

namespace DusanKasan\JSON\Converter;

use DusanKasan\JSON\ConverterInterface;
use ReflectionType;

class DateTime implements ConverterInterface
{
    /**
     * @param DateTime $value
     * @param array $params
     * @return string
     */
    public function encode($value, array $params = [])
    {
        if ($value === null) {
            return null;
        }

        return $value->format($params['format'] ?? 'Y-m-d H:i:s');
    }

    public function decode($value, ReflectionType $type, array $params = []): \DateTime
    {
        return \DateTime::createFromFormat($params['format'] ?? 'Y-m-d H:i:s', $value);
    }
}