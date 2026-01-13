<?php

declare(strict_types=1);

namespace Vigihdev\MockForge\Enums\Wp;

enum PostType: string
{
    case POST = 'post';
    case PAGE = 'page';
    case ATTACHMENT = 'attachment';
    case NAV_MENU_ITEM = 'nav_menu_item';
    case REVISION = 'revision';


    public function label(): string
    {
        return match ($this) {
            self::POST => 'Post',
            self::PAGE => 'Page',
            default => ucfirst($this->value)
        };
    }

    public function isBuiltIn(): bool
    {
        return in_array($this, [
            self::POST,
            self::PAGE,
            self::ATTACHMENT,
            self::REVISION,
            self::NAV_MENU_ITEM,
        ]);
    }
}
