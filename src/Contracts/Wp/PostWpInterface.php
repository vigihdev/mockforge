<?php

declare(strict_types=1);

namespace Vigihdev\MockForge\Contracts\Wp;

interface PostWpInterface
{

    /**
     * Get author
     * @return string
     */
    public function getAuthor(): string;

    /**
     * Get date
     * @return string
     */
    public function getDate(): string;

    /**
     * Get date GMT
     * @return string
     */
    public function getDateGmt(): string;

    /**
     * Get content
     * @return string
     */
    public function getContent(): string;

    /**
     * Get title
     * @return string
     */
    public function getTitle(): string;

    /**
     * Get excerpt
     * @return string
     */
    public function getExcerpt(): string;

    /**
     * Get status
     * @return string
     */
    public function getStatus(): string;

    /**
     * Get comment status
     * @return string
     */
    public function getCommentStatus(): string;

    /**
     * Get ping status
     * @return string
     */
    public function getPingStatus(): string;

    /**
     * Get password
     * @return string
     */
    public function getPassword(): string;

    /**
     * Get name
     * @return string
     */
    public function getName(): string;

    /**
     * Get to ping
     * @return string
     */
    public function getToPing(): string;

    /**
     * Get pinged
     * @return string
     */
    public function getPinged(): string;

    /**
     * Get modified
     * @return string
     */
    public function getModified(): string;

    /**
     * Get modified GMT
     * @return string
     */
    public function getModifiedGmt(): string;

    /**
     * Get content filtered
     * @return string
     */
    public function getContentFiltered(): string;

    /**
     * Get parent
     * @return int
     */
    public function getParent(): int;

    /**
     * Get GUID
     * @return string
     */
    public function getGuid(): string;

    /**
     * Get menu order
     * @return int
     */
    public function getMenuOrder(): int;

    /**
     * Get type
     * @return string
     */
    public function getType(): string;

    /**
     * Get mime type
     * @return string
     */
    public function getMimeType(): string;

    /**
     * Get comment count
     * @return int
     */
    public function getCommentCount(): int;

    /**
     * Get filter
     * @return string
     */
    public function getFilter(): string;
}
