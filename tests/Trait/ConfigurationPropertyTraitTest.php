<?php

declare(strict_types=1);

namespace jwderoos\Configurable\tests\Trait;

use jwderoos\Configurable\tests\Entity\Property;
use PHPUnit\Framework\TestCase;

final class ConfigurationPropertyTraitTest extends TestCase
{
    public function testPropertyValueSetGetEmptyString(): void
    {
        $property = new Property();

        $value = '';

        $this->assertFalse($property->hasValue());
        $property->setValue($value);
        $this->assertFalse($property->hasValue());
    }

    public function testPropertyValueSetGetString(): void
    {
        $property = new Property();

        $value = 'testValue';

        $this->assertFalse($property->hasValue());
        $property->setValue($value);
        $this->assertTrue($property->hasValue());
        $this->assertSame($value, $property->getValue());
    }

    public function testPropertyValueSetGetArray(): void
    {
        $property = new Property();

        $value = [
            'testKey' => 'testValue',
            'testKey2' => 238449,
        ];

        $property->setValue($value);
        $this->assertSame($value, $property->getValue());
    }
}
