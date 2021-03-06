<?php

declare(strict_types=1);

namespace Warp\Type\Cast;

use PHPUnit\Framework\TestCase;
use Warp\Type\BuiltinType;

class CasterAggregateTest extends TestCase
{
    public function validInputProvider(): array
    {
        return [
            [1, 1],
            ['1', 1],
            ['  1', 1],
            [1.0, 1],
            [1.1, 1.1],
            ['1.1', 1.1],
            ['string', 'string'],
            [$this->makeStringable('string'), 'string'],
        ];
    }

    public function invalidInputProvider(): array
    {
        return [
            [[]],
            [(object)[]],
            [null],
        ];
    }

    public function testCreation(): void
    {
        $innerCasters = [
            new ScalarCaster(BuiltinType::int()),
            new ScalarCaster(BuiltinType::float()),
            new ScalarCaster(BuiltinType::string()),
        ];

        $caster = new CasterAggregate(...$innerCasters);

        self::assertSame($innerCasters, \iterator_to_array($caster, false));
    }

    /**
     * @dataProvider validInputProvider
     * @param $input
     */
    public function testAccept($input): void
    {
        $caster = new CasterAggregate(
            new ScalarCaster(BuiltinType::int()),
            new ScalarCaster(BuiltinType::float()),
            new ScalarCaster(BuiltinType::string()),
        );

        self::assertTrue($caster->accepts($input));
    }

    /**
     * @dataProvider validInputProvider
     * @param $input
     * @param $expected
     */
    public function testCast($input, $expected): void
    {
        $caster = new CasterAggregate(
            new ScalarCaster(BuiltinType::int()),
            new ScalarCaster(BuiltinType::float()),
            new ScalarCaster(BuiltinType::string()),
        );

        self::assertSame($expected, $caster->cast($input));
    }

    /**
     * @dataProvider invalidInputProvider
     * @param $input
     */
    public function testNotAccept($input): void
    {
        $caster = new CasterAggregate(
            new ScalarCaster(BuiltinType::int()),
            new ScalarCaster(BuiltinType::float()),
            new ScalarCaster(BuiltinType::string()),
        );

        self::assertFalse($caster->accepts($input));
    }

    /**
     * @dataProvider invalidInputProvider
     * @param $input
     */
    public function testCastInvalid($input): void
    {
        $caster = new CasterAggregate(
            new ScalarCaster(BuiltinType::int()),
            new ScalarCaster(BuiltinType::float()),
            new ScalarCaster(BuiltinType::string()),
        );

        $this->expectException(\InvalidArgumentException::class);
        $caster->cast($input);
    }

    private function makeStringable(string $value): object
    {
        return new class($value) {
            private string $value;

            public function __construct(string $value)
            {
                $this->value = $value;
            }

            public function __toString(): string
            {
                return $this->value;
            }
        };
    }
}
