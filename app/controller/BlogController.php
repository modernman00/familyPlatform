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
            $blog = BlogData::getBlogBySlug($slug);

            if (!$blog) {
                // If blog is not found or not published, show 404
                Utility::view('errors/404');
                return;
            }

            Utility::view('blog/show', [
                'blog' => $blog
            ]);
        } catch (\Throwable $th) {
            Utility::showError($th);
        }
    }
}
