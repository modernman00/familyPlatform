<?php
declare(strict_types=1);

namespace App\model;

use Src\Select;
use Src\SelectFn;
use Src\Update;

final class BlogData extends Select
{
    /**
     * Retrieve all published blogs
     */
    public static function getAllPublishedBlogs(): array
    {
        $query = "SELECT blogs.*, account.firstName, account.lastName 
                  FROM blogs 
                  LEFT JOIN account ON blogs.author_id = account.id 
                  WHERE blogs.status = 'published' 
                  ORDER BY blogs.created_at DESC";
        return parent::selectFn2(query: $query, bind: []);
    }

    /**
     * Retrieve a specific published blog by its slug
     */
    public static function getPublishedBlogBySlug(string $slug): array
    {
        $query = "SELECT blogs.*, account.firstName, account.lastName 
                  FROM blogs 
                  LEFT JOIN account ON blogs.author_id = account.id 
                  WHERE blogs.status = 'published' AND blogs.slug = ? 
                  LIMIT 1";
        $result = parent::selectFn2(query: $query, bind: [$slug]);
        return $result[0] ?? [];
    }

    /**
     * Retrieve a specific blog by its ID (for admin editing)
     */
    public static function getBlogById(int|string $id): array
    {
        return SelectFn::selectOneRow('blogs', 'id', (string)$id) ?? [];
    }

    /**
     * Retrieve all blogs (drafts and published) for the admin panel
     */
    public static function getAllBlogsForAdmin(): array
    {
        $query = "SELECT blogs.*, account.firstName, account.lastName 
                  FROM blogs 
                  LEFT JOIN account ON blogs.author_id = account.id 
                  ORDER BY blogs.created_at DESC";
        return parent::selectFn2(query: $query, bind: []);
    }
}
