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
     *
     * @return array<int, mixed>
     */
    public static function getAllPublishedBlogs(): array
    {
        $query = "SELECT blogs.*, personal.firstName, personal.lastName 
                  FROM blogs 
                  LEFT JOIN personal ON blogs.author_id = personal.id 
                  WHERE blogs.status = 'published' 
                  ORDER BY blogs.created_at DESC";
        return parent::selectFn2(query: $query, bind: []);
    }

    /**
     * Retrieve a specific published blog by its slug
     *
     * @return array<string, mixed>
     */
    public static function getPublishedBlogBySlug(string $slug): array
    {
        $query = "SELECT blogs.*, personal.firstName, personal.lastName 
                  FROM blogs 
                  LEFT JOIN personal ON blogs.author_id = personal.id 
                  WHERE blogs.status = 'published' AND blogs.slug = ? 
                  LIMIT 1";
        $result = parent::selectFn2(query: $query, bind: [$slug]);
        return $result[0] ?? [];
    }

    /**
     * Retrieve recent published blogs excluding the current one
     *
     * @param string $excludeSlug
     * @return array<int, mixed>
     */
    public static function getRecentPublishedBlogs(string $excludeSlug): array
    {
        $query = "SELECT blogs.*, personal.firstName, personal.lastName 
                  FROM blogs 
                  LEFT JOIN personal ON blogs.author_id = personal.id 
                  WHERE blogs.status = 'published' AND blogs.slug != ? 
                  ORDER BY blogs.created_at DESC 
                  LIMIT 3";
        return parent::selectFn2(query: $query, bind: [$excludeSlug]);
    }

    /**
     * Retrieve the latest published blogs for the public landing page
     *
     * @param int $limit
     * @return array<int, mixed>
     */
    public static function getLatestPublishedBlogs(int $limit = 3): array
    {
        $limitInt = max(1, min(10, $limit));
        $query = "SELECT blogs.*, personal.firstName, personal.lastName 
                  FROM blogs 
                  LEFT JOIN personal ON blogs.author_id = personal.id 
                  WHERE blogs.status = 'published' 
                  ORDER BY blogs.created_at DESC 
                  LIMIT 3";
        return parent::selectFn2(query: $query, bind: []);
    }

    /**
     * Retrieve a specific blog by its ID (for admin editing)
     *
     * @return array<string, mixed>
     */
    public static function getBlogById(int|string $id): array
    {
        return SelectFn::selectOneRow('blogs', 'id', (string)$id) ?? [];
    }

    /**
     * Retrieve all blogs (drafts and published) for the admin panel
     *
     * @return array<int, mixed>
     */
    public static function getAllBlogsForAdmin(): array
    {
        $query = "SELECT blogs.*, personal.firstName, personal.lastName 
                  FROM blogs 
                  LEFT JOIN personal ON blogs.author_id = personal.id 
                  ORDER BY blogs.created_at DESC";
        return parent::selectFn2(query: $query, bind: []);
    }
}
