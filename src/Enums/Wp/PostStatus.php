<?php

declare(strict_types=1);

namespace Vigihdev\MockForge\Enums\Wp;

enum PostStatus: string
{
    case PUBLISH = 'publish';
    case DRAFT = 'draft';
    case PENDING = 'pending';
    case PRIVATE = 'private';
    case TRASH = 'trash';
    case AUTO_DRAFT = 'auto-draft';
    case INHERIT = 'inherit';

    public function label(): string
    {
        return match ($this) {
            self::PUBLISH => 'Published',
            self::DRAFT => 'Draft',
            self::PENDING => 'Pending Review',
            self::PRIVATE => 'Private',
            self::TRASH => 'Trash',
            self::AUTO_DRAFT => 'Auto Draft',
            self::INHERIT => 'Inherit',
        };
    }

    public function isPublic(): bool
    {
        return $this === self::PUBLISH;
    }

    public function canEdit(): bool
    {
        return !in_array($this, [self::TRASH, self::INHERIT]);
    }
}
