<?php
declare(strict_types=1);

namespace App\controller;

use App\model\BlogData;
use Src\Utility;

final class BlogController
{
    /**
     * Display a list of all published blogs.
     */
    public function index(): void
    {
        try {
            $blogs = BlogData::getAllPublishedBlogs();
            
            Utility::view('blog/index', [
                'blogs' => $blogs
            ]);
        } catch (\Throwable $th) {
            Utility::showError($th);
        }
    }

    /**
     * Display a single blog by its slug.
     */
    public function show(string $slug): void
    {
        try {
            $blog = BlogData::getPublishedBlogBySlug($slug);

            if (!$blog) {
                // If blog is not found or not published, show 404
                Utility::view('errors/404');
                return;
            }

            // Atomically increment views with session debouncing
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            if (!isset($_SESSION['viewed_blogs']) || !is_array($_SESSION['viewed_blogs'])) {
                $_SESSION['viewed_blogs'] = [];
            }
            $blogIdInt = (int) ($blog['id'] ?? 0);
            if ($blogIdInt > 0 && !in_array($blogIdInt, $_SESSION['viewed_blogs'], true)) {
                $_SESSION['viewed_blogs'][] = $blogIdInt;
                BlogData::incrementViews($blogIdInt);
                $blog['views'] = ((int) ($blog['views'] ?? 0)) + 1;
            }

            $recentBlogs = BlogData::getRecentPublishedBlogs($slug);

            Utility::view('blog/show', [
                'blog' => $blog,
                'recentBlogs' => $recentBlogs
            ]);
        } catch (\Throwable $th) {
            Utility::showError($th);
        }
    }
}
