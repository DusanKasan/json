<?php
namespace DusanKasan\JSON\Type;

use ReflectionType;

class Type
{
    public string $name;
    public bool $allowsNull;

    public static function fromReflectionType(ReflectionType $type, ?string $varAnnotation)
    {
        $t = $type->getName();
        if ($t === 'array' && $varAnnotation !== null) {
            $t = $varAnnotation;
        }

        return new self($t, $type->allowsNull());
    }

    public function __construct(string $name, bool $allowsNull)
    {
        $this->name = $name;
        $this->allowsNull = $allowsNull;
    }

    public function isArray(): bool
    {
        return $this->name === 'array' || substr($this->name, -2) === '[]';
    }

    public function arrayItemsType(): ?self
    {
        if ($this->name === 'array') {
            return null;
        }

        $itemType = substr($this->name, 0, strlen($this->name) - 2);
        return new self($itemType, false);
    }
}