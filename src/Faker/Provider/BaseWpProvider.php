<?php

declare(strict_types=1);

namespace Vigihdev\MockForge\Faker\Provider;

use Faker\Factory;
use Faker\Generator;
use Moment\Moment;
use Vigihdev\MockForge\Enums\Wp\{PostType, PostStatus, PostCommentStatus};

abstract class BaseWpProvider
{
    protected ?Generator $faker = null;
    protected ?Moment $moment = null;

    public function __construct(
        private readonly int $count = 10,
    ) {

        if ($this->faker === null) {
            $this->faker = Factory::create(locale: 'id_ID');
        }

        if ($this->moment === null) {
            $this->moment = new Moment();
        }
    }

    protected function generatePostTitle(): string
    {
        // Tambahkan variasi title
        $templates = [
            '{sentence}',
            '{word}: {sentence}',
            'Tips {verb} {noun}',
        ];

        $template = $this->faker->randomElement($templates);

        return strtr($template, [
            '{sentence}' => $this->faker->sentence(4, false),
            '{word}' => ucfirst($this->faker->word()),
            '{verb}' => $this->faker->randomElement(['Menggunakan', 'Membuat', 'Memilih']),
            '{noun}' => $this->faker->randomElement(['WordPress', 'Plugin', 'Theme']),
        ]);
    }

    protected function generatePostExcerpt(): string
    {
        return $this->faker->text($this->faker->numberBetween(100, 200));
    }

    protected function generatePostStatus(): string
    {
        // Weighted probabilities
        $weights = [
            PostStatus::PUBLISH->value => 60,
            PostStatus::DRAFT->value => 25,
            PostStatus::PRIVATE->value => 15,
        ];

        return $this->faker->randomElement(array_keys($weights));
    }

    protected function generateCommentStatus(): string
    {
        return $this->faker->boolean(80)
            ? PostCommentStatus::OPEN->value
            : PostCommentStatus::CLOSED->value;
    }

    protected function generateSlug(string $title): string
    {
        // Clean title untuk slug
        $slug = preg_replace('/[^a-z0-9]+/i', '-', strtolower($title));
        $slug = trim($slug, '-');

        // Potong jika terlalu panjang
        if (strlen($slug) > 50) {
            $slug = substr($slug, 0, 50);
            $slug = rtrim($slug, '-');
        }

        return $slug;
    }

    protected function generatePostParentId(int $currentIndex): int
    {
        // 15% chance memiliki parent
        if ($this->faker->boolean(15) && $currentIndex > 0) {
            return $this->faker->numberBetween(1, $currentIndex);
        }

        return 0;
    }

    protected function generatePostType(): string
    {
        // 80% post, 20% page
        return $this->faker->boolean(80)
            ? PostType::POST->value
            : PostType::PAGE->value;
    }

    protected static function mapDateNow(int $addMonth = 0): string
    {
        return $addMonth > 0 ?
            (new Moment())->addMonths($addMonth)->format(Moment::NO_TZ_MYSQL)
            : (new Moment())->format(Moment::NO_TZ_MYSQL);
    }
}
