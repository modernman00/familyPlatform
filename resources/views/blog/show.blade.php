@extends('layouts.landing_layout')

@section('title', $blog['title'])
@section('data-page-id', 'blog-show')

@section('meta_tags')
    <meta property="og:description" content="{{ htmlspecialchars($blog['summary'] ?? substr(strip_tags($blog['content']), 0, 150)) }}">
    <meta property="og:type" content="article">
    <meta property="article:published_time" content="{{ date('c', strtotime($blog['created_at'])) }}">
    <meta property="article:author" content="{{ htmlspecialchars($blog['firstName'] . ' ' . $blog['lastName']) }}">
    @if(!empty($blog['cover_image']))
        <meta property="og:image" content="{{ htmlspecialchars($_ENV['APP_URL'] ?? '') }}{{ htmlspecialchars($blog['cover_image']) }}">
        <meta name="twitter:card" content="summary_large_image">
    @else
        <meta name="twitter:card" content="summary">
    @endif
    <meta name="twitter:title" content="{{ htmlspecialchars($blog['title']) }}">
    <meta name="twitter:description" content="{{ htmlspecialchars($blog['summary'] ?? substr(strip_tags($blog['content']), 0, 150)) }}">
@endsection

@section('content')
<style>
.blog-header {
    padding: 100px 0 60px;
    background: #f8f9fa;
    text-align: center;
    border-bottom: 1px solid #eaeaea;
}
.blog-header h1 {
    font-size: 3rem;
    font-weight: 800;
    color: #1a202c;
    margin-bottom: 24px;
    line-height: 1.2;
}
.blog-meta-info {
    font-size: 1rem;
    color: #718096;
    display: flex;
    justify-content: center;
    gap: 20px;
    align-items: center;
}
.blog-cover {
    width: 100%;
    max-height: 500px;
    object-fit: cover;
    border-radius: 16px;
    margin-top: -40px;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}
.blog-body {
    padding: 60px 0 100px;
    font-size: 1.125rem;
    line-height: 1.8;
    color: #2d3748;
    max-width: 800px;
    margin: 0 auto;
}
.blog-body h2, .blog-body h3 {
    color: #1a202c;
    margin-top: 2em;
    margin-bottom: 1em;
    font-weight: 700;
}
.blog-body p {
    margin-bottom: 1.5em;
}
.blog-body img {
    max-width: 100%;
    border-radius: 8px;
    height: auto;
    margin: 2em 0;
}
.share-buttons {
    display: flex;
    gap: 12px;
    margin-top: 40px;
    padding-top: 40px;
    border-top: 1px solid #edf2f7;
}
.share-btn {
    padding: 10px 20px;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 600;
    transition: transform 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.share-btn:hover {
    transform: translateY(-2px);
}
.share-twitter { background: #1DA1F2; color: white; }
.share-facebook { background: #4267B2; color: white; }
.share-linkedin { background: #0077B5; color: white; }
</style>

<div class="blog-header">
    <div class="container" style="max-width: 900px;">
        <h1>{{ htmlspecialchars($blog['title']) }}</h1>
        <div class="blog-meta-info">
            <span><i class="far fa-user"></i> By {{ htmlspecialchars($blog['firstName'] . ' ' . $blog['lastName']) }}</span>
            <span><i class="far fa-calendar-alt"></i> {{ date('F j, Y', strtotime($blog['created_at'])) }}</span>
        </div>
    </div>
</div>

<div class="container">
    @if(!empty($blog['cover_image']))
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <img src="{{ htmlspecialchars($blog['cover_image']) }}" alt="Cover Image" class="blog-cover img-fluid position-relative z-index-1">
            </div>
        </div>
    @endif
    
    <div class="blog-body ql-editor">
        {{-- Outputting raw HTML from the Quill editor. It is sanitized on backend. --}}
        {!! $blog['content'] !!}
        
        <div class="share-buttons">
            <strong class="d-flex align-items-center me-3">Share this article:</strong>
            <a href="https://twitter.com/intent/tweet?text={{ urlencode($blog['title']) }}&url={{ urlencode($_ENV['APP_URL'] ?? '' . '/blog/' . $blog['slug']) }}" target="_blank" class="share-btn share-twitter">
                <i class="fab fa-twitter"></i> Twitter
            </a>
            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($_ENV['APP_URL'] ?? '' . '/blog/' . $blog['slug']) }}" target="_blank" class="share-btn share-facebook">
                <i class="fab fa-facebook-f"></i> Facebook
            </a>
            <a href="https://www.linkedin.com/shareArticle?mini=true&url={{ urlencode($_ENV['APP_URL'] ?? '' . '/blog/' . $blog['slug']) }}&title={{ urlencode($blog['title']) }}" target="_blank" class="share-btn share-linkedin">
                <i class="fab fa-linkedin-in"></i> LinkedIn
            </a>
        </div>
    </div>
</div>
@endsection
