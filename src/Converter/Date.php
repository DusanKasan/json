<?php

namespace DusanKasan\JSON\Converter;

use DateTime;
use DusanKasan\JSON\ConverterInterface;
use ReflectionType;

class Date implements ConverterInterface
{
    /**
     * @param DateTime $value
     * @param ReflectionType $type
     * @param array $params
     * @return string
     */
    public function encode($value, ReflectionType $type, array $params = []): string
    {
        return $value->format($params['format'] ?? 'Y-m-d');
    }

    public function decode($value, ReflectionType $type, array $params = []): DateTime
    {
        $d = DateTime::createFromFormat($params['format'] ?? 'Y-m-d', $value);
        $d->setTime(0, 0, 0);
        return $d;
    }
}