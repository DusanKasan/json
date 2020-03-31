<?php

namespace DusanKasan\JSON\Doc\Converter;

use DateTime;
use ReflectionType;

class Date implements ConverterInterface
{
    /**
     * @param DateTime $value
     * @param array $params
     * @return StringRepresentation
     */
    public function serialize($value, array $params = [])
    {
        if ($value === null) {
            return null;
        }

        return $value->format($params['format'] ?? 'Y-m-d');
    }

    public function deserialize($value, ReflectionType $type, array $params = []): DateTime
    {
        $d = DateTime::createFromFormat($params['format'] ?? 'Y-m-d', $value);
        $d->setTime(0, 0, 0);
        return $d;
    }
}