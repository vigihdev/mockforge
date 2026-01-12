<?php

declare(strict_types=1);

namespace Vigihdev\MockForge\DTOs\Wp;

use Vigihdev\MockForge\Contracts\Able\ArrayAbleInterface;
use Vigihdev\MockForge\Contracts\Wp\PostWpInterface;

final class PostWpDto implements ArrayAbleInterface, PostWpInterface
{

    public function __construct(
        private readonly string $author = '0',
        private readonly string $date = '0000-00-00 00:00:00',
        private readonly string $dateGmt = '0000-00-00 00:00:00',
        private readonly string $content = '',
        private readonly string $title = '',
        private readonly string $excerpt = '',
        private readonly string $status = 'publish',
        private readonly string $commentStatus = 'open',
        private readonly string $pingStatus = 'open',
        private readonly string $password = '',
        private readonly string $name = '',
        private readonly string $toPing = '',
        private readonly string $pinged = '',
        private readonly string $modified = '0000-00-00 00:00:00',
        private readonly string $modifiedGmt = '0000-00-00 00:00:00',
        private readonly string $contentFiltered = '',
        private readonly int $parent = 0,
        private readonly string $guid = '',
        private readonly int $menuOrder = 0,
        private readonly string $type = 'post',
        private readonly string $mimeType = '',
        private readonly int $commentCount = 0,
        private readonly string $filter = ''
    ) {}

    public function getAuthor(): string
    {
        return $this->author;
    }

    public function getDate(): string
    {
        return $this->date;
    }

    public function getDateGmt(): string
    {
        return $this->dateGmt;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getExcerpt(): string
    {
        return $this->excerpt;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getCommentStatus(): string
    {
        return $this->commentStatus;
    }

    public function getPingStatus(): string
    {
        return $this->pingStatus;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getToPing(): string
    {
        return $this->toPing;
    }

    public function getPinged(): string
    {
        return $this->pinged;
    }

    public function getModified(): string
    {
        return $this->modified;
    }

    public function getModifiedGmt(): string
    {
        return $this->modifiedGmt;
    }

    public function getContentFiltered(): string
    {
        return $this->contentFiltered;
    }

    public function getParent(): int
    {
        return $this->parent;
    }

    public function getGuid(): string
    {
        return $this->guid;
    }

    public function getMenuOrder(): int
    {
        return $this->menuOrder;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    public function getCommentCount(): int
    {
        return $this->commentCount;
    }

    public function getFilter(): string
    {
        return $this->filter;
    }

    public function toArray(): array
    {
        return array_filter([
            'author' => $this->author,
            'date' => $this->date,
            'dateGmt' => $this->dateGmt,
            'content' => $this->content,
            'title' => $this->title,
            'excerpt' => $this->excerpt,
            'status' => $this->status,
            'commentStatus' => $this->commentStatus,
            'pingStatus' => $this->pingStatus,
            'password' => $this->password,
            'name' => $this->name,
            'toPing' => $this->toPing,
            'pinged' => $this->pinged,
            'modified' => $this->modified,
            'modifiedGmt' => $this->modifiedGmt,
            'contentFiltered' => $this->contentFiltered,
            'parent' => $this->parent,
            'guid' => $this->guid,
            'menuOrder' => $this->menuOrder,
            'type' => $this->type,
            'mimeType' => $this->mimeType,
            'commentCount' => $this->commentCount,
            'filter' => $this->filter,
        ], function ($value) {
            return $value !== null && $value !== '';
        });
    }
}
