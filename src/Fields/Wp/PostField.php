<?php

declare(strict_types=1);

namespace Vigihdev\MockForge\Fields\Wp;

final class PostField
{

    public static function transformFromDto(array $data): array
    {
        $transformed = [];
        foreach (self::dtoDataFields() as $key => $value) {
            if (isset($data[$key])) {
                $transformed[$value] = $data[$key];
            }
        }
        return $transformed;
    }

    private static function dtoDataFields(): array
    {

        $attributes = [
            'title' => 'post_title',
            'name' => 'post_name',
            'status' => 'post_status',
            'author' => 'post_author',
            'type' => 'post_type',
            'date' => 'post_date',
            'dateGmt' => 'post_date_gmt',
            'modified' => 'post_modified',
            'modifiedGmt' => 'post_modified_gmt',
            'commentStatus' => 'comment_status',
            'pingStatus' => 'ping_status',
            'password' => 'post_password',
            'toPing' => 'to_ping',
            'pinged' => 'pinged',
            'parent' => 'post_parent',
            'menuOrder' => 'menu_order',
            'mimeType' => 'post_mime_type',
            'guid' => 'guid',
            'category' => 'post_category',
            'tagsInput' => 'tags_input',
            'taxInput' => 'tax_input',
            'metaInput' => 'meta_input',
        ];

        return $attributes;
    }
}
