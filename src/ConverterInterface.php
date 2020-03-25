<?php

namespace DusanKasan\JSON;

use ReflectionType;

interface ConverterInterface
{
    public function encode($value, ReflectionType $type, array $params = []);
    public function decode($value, ReflectionType $type, array $params = []);
}