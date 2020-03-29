<?php

namespace DusanKasan\JSON\Converter;

use DateTime;
use DusanKasan\JSON\ConverterInterface;
use ReflectionType;

class Date implements ConverterInterface
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

        return $value->format($params['format'] ?? 'Y-m-d');
    }

    public function decode($value, ReflectionType $type, array $params = []): DateTime
    {
        $d = DateTime::createFromFormat($params['format'] ?? 'Y-m-d', $value);
        $d->setTime(0, 0, 0);
        return $d;
    }
}