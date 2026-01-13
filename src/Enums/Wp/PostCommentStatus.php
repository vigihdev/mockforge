<?php

declare(strict_types=1);

namespace Vigihdev\MockForge\Enums\Wp;

enum PostCommentStatus: string
{
    case OPEN = 'open';
    case CLOSED = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::OPEN => 'Open',
            self::CLOSED => 'Closed'
        };
    }
}
