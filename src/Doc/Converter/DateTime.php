<?php

namespace DusanKasan\JSON\Doc\Converter;

use ReflectionType;

class DateTime implements ConverterInterface
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

        return $value->format($params['format'] ?? 'Y-m-d H:i:s');
    }

    public function deserialize($value, ReflectionType $type, array $params = []): \DateTime
    {
        return \DateTime::createFromFormat($params['format'] ?? 'Y-m-d H:i:s', $value);
    }
}