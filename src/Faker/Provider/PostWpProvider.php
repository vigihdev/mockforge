<?php

declare(strict_types=1);

namespace Vigihdev\MockForge\Faker\Provider;

use Moment\Moment;
use Vigihdev\MockForge\DTOs\Wp\PostWpDto;
use Vigihdev\MockForge\Enums\Wp\{PostType, PostStatus, PostCommentStatus};
use Vigihdev\Support\Collection;

final class PostWpProvider extends BaseWpProvider
{

    public function __construct(
        private readonly int $count = 10,
        private readonly int $authorCount = 10,
    ) {
        parent::__construct(count: $count);
    }

    /**
     *
     * @return Collection<PostWpDto>
     */
    public function generatePosts(): Collection
    {

        $faker = $this->faker;

        $posts = [];
        for ($i = 0; $i < $this->count; $i++) {
            $posts[] = new PostWpDto(
                author: (string) $faker->randomElement(range(1, $this->authorCount)),
                date: $faker->dateTimeBetween(self::mapDateNow(), self::mapDateNow(1))->format(Moment::NO_TZ_MYSQL),
                dateGmt: $faker->dateTimeBetween(self::mapDateNow(), self::mapDateNow(1))->format(Moment::NO_TZ_MYSQL),
                content: $faker->text(500),
                title: $this->generatePostTitle(),
                status: $faker->randomElement([
                    PostStatus::PUBLISH->value,
                    PostStatus::DRAFT->value,
                    PostStatus::PRIVATE->value,
                ]),
                commentStatus: $faker->randomElement([
                    PostCommentStatus::OPEN->value,
                    PostCommentStatus::CLOSED->value,
                ]),
                name: $faker->slug(),
                modified: $faker->dateTimeBetween(self::mapDateNow(), self::mapDateNow(1))->format(Moment::NO_TZ_MYSQL),
                modifiedGmt: $faker->dateTimeBetween(self::mapDateNow(), self::mapDateNow(1))->format(Moment::NO_TZ_MYSQL),
                parent: 0,
                type: $faker->randomElement([
                    PostType::POST->value,
                    PostType::PAGE->value,
                ]),
            );
        }

        return new Collection($posts);
    }
}
