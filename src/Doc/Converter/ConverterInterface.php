<?php

namespace DusanKasan\JSON\Doc\Converter;

use ReflectionType;

interface ConverterInterface
{
    public function serialize($value, array $params = []);

    public function deserialize($value, ReflectionType $type, array $params = []);
}