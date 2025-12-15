<?php

declare(strict_types=1);

namespace jwderoos\Configurable\Trait;

use function Safe\json_decode;
use function Safe\json_encode;

trait ConfigurationPropertyTrait
{
    private string $name;

    private ?string $value = null;

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setValue(null|array|string $value): void
    {
        if (is_array($value)) {
            $value = json_encode($value);
        }

        $this->value = $value;
    }

    /**
     * @return string|mixed[]
     */
    public function getValue(): string|array
    {
        $value = $this->value;
        if (
            is_string($value) &&
            json_validate($value)
        ) {
            /** @var mixed[] $decoded */
            $decoded = json_decode($value, true);
            $value = $decoded;
        }

        if (is_int($value)) {
            $value = (string) $value;
        }

        return $value ?? '';
    }

    public function hasValue(): bool
    {
        return isset($this->value) && !empty($this->value);
    }
}
