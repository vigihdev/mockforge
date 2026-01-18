<?php

declare(strict_types=1);

namespace Vigihdev\MockForge\Faker\Support;

use ReflectionClass;
use ReflectionProperty;
use ReflectionUnionType;
use Faker\Factory;
use Faker\Generator;
use Vigihdev\MockForge\Contracts\Faker\MockGeneratorInterface;

final class DtoMockGenerator implements MockGeneratorInterface
{
    private Generator $faker;
    private array $typeGenerators;

    public function __construct(?Generator $faker = null)
    {
        $this->faker = $faker ?? Factory::create('id_ID');
        $this->initializeGenerators();
    }

    private function initializeGenerators(): void
    {
        $this->typeGenerators = [
            // Integer types
            'int' => fn(string $propertyName) => $this->generateInteger($propertyName),
            'integer' => fn(string $propertyName) => $this->generateInteger($propertyName),

            // String types with property name patterns
            'string' => fn(string $propertyName) => $this->generateString($propertyName),

            // Float types
            'float' => fn(string $propertyName) => $this->faker->randomFloat(2, 0, 1000),
            'double' => fn(string $propertyName) => $this->faker->randomFloat(2, 0, 1000),

            // Boolean types
            'bool' => fn(string $propertyName) => $this->generateBoolean($propertyName),
            'boolean' => fn(string $propertyName) => $this->generateBoolean($propertyName),

            // DateTime types
            'DateTime' => fn(string $propertyName) => $this->faker->dateTime(),
            'DateTimeImmutable' => fn(string $propertyName) => $this->faker->dateTimeImmutable(),
            'DateTimeInterface' => fn(string $propertyName) => $this->faker->dateTime(),

            // Array types
            'array' => fn(string $propertyName) => $this->generateArray($propertyName),

            // Nullable wrapper
            'null' => fn(string $propertyName) => null,
        ];
    }

    public function generate(string $className): array
    {
        $reflection = new ReflectionClass($className);
        $data = [];

        foreach ($reflection->getProperties() as $property) {
            $data[$property->getName()] = $this->generatePropertyValue($property);
        }

        return $data;
    }

    public function generateObject(string $className): object
    {
        $reflection = new ReflectionClass($className);
        $instance = $reflection->newInstanceWithoutConstructor();

        foreach ($reflection->getProperties() as $property) {
            if (!$property->isPublic()) {
                $property->setAccessible(true);
            }

            $value = $this->generatePropertyValue($property);
            $property->setValue($instance, $value);
        }

        return $instance;
    }

    private function generatePropertyValue(ReflectionProperty $property): mixed
    {
        $propertyName = $property->getName();
        $type = $property->getType();

        if (!$type) {
            return $this->guessValueFromName($propertyName);
        }

        // Handle nullable types
        if ($type->allowsNull() && $this->faker->boolean(20)) {
            return null;
        }

        $typeName = $type->getName();

        // Handle union types (ambil tipe pertama yang bukan null)
        if ($type instanceof ReflectionUnionType) {
            $types = $type->getTypes();
            foreach ($types as $unionType) {
                if ($unionType->getName() !== 'null') {
                    $typeName = $unionType->getName();
                    break;
                }
            }
        }

        return $this->generateByType($typeName, $propertyName);
    }

    private function generateByType(string $typeName, string $propertyName): mixed
    {
        // Check custom generator first
        if (isset($this->typeGenerators[$typeName])) {
            return $this->typeGenerators[$typeName]($propertyName);
        }

        // Handle class types (untuk nested DTOs)
        if (class_exists($typeName) || interface_exists($typeName)) {
            return $this->generateObject($typeName);
        }

        // Default fallback
        return $this->guessValueFromName($propertyName);
    }

    private function generateInteger(string $propertyName): int
    {
        $lowerName = strtolower($propertyName);

        return match (true) {
            // ID patterns
            str_ends_with($lowerName, 'id') => $this->faker->numberBetween(1, 10000),
            str_contains($lowerName, 'user_id') => $this->faker->numberBetween(1, 100),
            str_contains($lowerName, 'product_id') => $this->faker->numberBetween(1, 1000),

            // Age patterns
            str_contains($lowerName, 'age') => $this->faker->numberBetween(18, 65),

            // Quantity patterns
            str_contains($lowerName, 'quantity') => $this->faker->numberBetween(0, 100),
            str_contains($lowerName, 'stock') => $this->faker->numberBetween(0, 1000),
            str_contains($lowerName, 'count') => $this->faker->numberBetween(0, 50),

            // Status codes
            str_contains($lowerName, 'status_code') => $this->faker->randomElement([200, 201, 400, 404, 500]),
            str_contains($lowerName, 'code') => $this->faker->randomNumber(4),

            // Year patterns
            str_contains($lowerName, 'year') => $this->faker->numberBetween(2000, 2024),

            // Default
            default => $this->faker->numberBetween(1, 1000)
        };
    }

    private function generateString(string $propertyName): string
    {
        $lowerName = strtolower($propertyName);

        // Priority-based pattern matching
        $patterns = [
            // Email patterns
            '/email/' => fn() => $this->faker->email(),
            '/_email/' => fn() => $this->faker->email(),

            // Name patterns
            '/name/' => fn() => $this->faker->name(),
            '/fullname/' => fn() => $this->faker->name(),
            '/username/' => fn() => $this->faker->userName(),

            // Title patterns
            '/title/' => fn() => $this->faker->sentence(3),
            '/_title/' => fn() => $this->faker->sentence(3),

            // Content patterns
            '/content/' => fn() => $this->faker->paragraphs(2, true),
            '/description/' => fn() => $this->faker->paragraph(),
            '/body/' => fn() => $this->faker->text(300),

            // Address patterns
            '/address/' => fn() => $this->faker->address(),
            '/street/' => fn() => $this->faker->streetAddress(),
            '/city/' => fn() => $this->faker->city(),
            '/country/' => fn() => $this->faker->country(),

            // Phone patterns
            '/phone/' => fn() => $this->faker->phoneNumber(),
            '/tel/' => fn() => $this->faker->phoneNumber(),
            '/mobile/' => fn() => $this->faker->phoneNumber(),

            // URL patterns
            '/url/' => fn() => $this->faker->url(),
            '/website/' => fn() => $this->faker->url(),
            '/avatar/' => fn() => $this->faker->imageUrl(100, 100),
            '/image/' => fn() => $this->faker->imageUrl(),

            // Date patterns
            '/date/' => fn() => $this->faker->date('Y-m-d'),
            '/_at$/' => fn() => $this->faker->dateTime()->format('Y-m-d H:i:s'),
            '/_on$/' => fn() => $this->faker->date('Y-m-d'),

            // Code patterns
            '/code/' => fn() => strtoupper($this->faker->bothify('??###')),
            '/token/' => fn() => bin2hex(random_bytes(16)),
            '/uuid/' => fn() => $this->faker->uuid(),

            // Status patterns
            '/status/' => fn() => $this->faker->randomElement(['active', 'inactive', 'pending']),
            '/state/' => fn() => $this->faker->randomElement(['draft', 'published', 'archived']),
            '/type/' => fn() => $this->faker->randomElement(['admin', 'user', 'guest']),
            '/role/' => fn() => $this->faker->randomElement(['admin', 'editor', 'viewer']),

            // Password patterns
            '/password/' => fn() => password_hash('password123', PASSWORD_DEFAULT),

            // Color patterns
            '/color/' => fn() => $this->faker->hexColor(),

            // Currency patterns
            '/currency$/' => fn() => $this->faker->currencyCode(),
        ];

        foreach ($patterns as $pattern => $generator) {
            if (preg_match($pattern, $lowerName)) {
                return $generator();
            }
        }

        // Fallback untuk string lainnya
        if (str_starts_with($lowerName, 'is_') || str_starts_with($lowerName, 'has_')) {
            return $this->faker->boolean() ? 'true' : 'false';
        }

        return $this->faker->text(50);
    }

    private function generateBoolean(string $propertyName): bool
    {
        $lowerName = strtolower($propertyName);

        return match (true) {
            str_starts_with($lowerName, 'is_') => $this->faker->boolean(),
            str_starts_with($lowerName, 'has_') => $this->faker->boolean(),
            str_starts_with($lowerName, 'can_') => $this->faker->boolean(),
            str_contains($lowerName, 'active') => $this->faker->boolean(80), // 80% true
            str_contains($lowerName, 'verified') => $this->faker->boolean(70),
            str_contains($lowerName, 'enabled') => $this->faker->boolean(90),
            default => $this->faker->boolean()
        };
    }

    private function generateArray(string $propertyName): array
    {
        $lowerName = strtolower($propertyName);

        return match (true) {
            str_contains($lowerName, 'tags') => $this->faker->words(3),
            str_contains($lowerName, 'categories') => $this->faker->words(2),
            str_contains($lowerName, 'images') => array_map(
                fn() => $this->faker->imageUrl(),
                range(1, $this->faker->numberBetween(1, 3))
            ),
            str_contains($lowerName, 'items') => array_fill(0, $this->faker->numberBetween(1, 5), [
                'id' => $this->faker->numberBetween(1, 100),
                'name' => $this->faker->word(),
            ]),
            default => []
        };
    }

    private function guessValueFromName(string $propertyName): mixed
    {
        $lowerName = strtolower($propertyName);

        // Simple heuristic based on property name
        if (str_ends_with($lowerName, '_id') || $lowerName === 'id') {
            return $this->faker->numberBetween(1, 1000);
        }

        if (
            str_starts_with($lowerName, 'is_') ||
            str_starts_with($lowerName, 'has_') ||
            str_starts_with($lowerName, 'can_')
        ) {
            return $this->faker->boolean();
        }

        if (str_contains($lowerName, 'date') || str_contains($lowerName, 'time')) {
            return $this->faker->date('Y-m-d');
        }

        return $this->faker->text(30);
    }

    // Method untuk custom generator
    public function addTypeGenerator(string $type, callable $generator): void
    {
        $this->typeGenerators[$type] = $generator;
    }

    public function setFaker(Generator $faker): void
    {
        $this->faker = $faker;
    }
}
