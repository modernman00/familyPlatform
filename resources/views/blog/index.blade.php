@extends('layouts.landing_layout')

@section('title', 'FamilyPlatform Blog - Insights & Stories')
@section('data-page-id', 'blog-index')

@section('content')
<style>
.blog-hero {
    padding: 100px 0 60px;
    background: linear-gradient(135deg, var(--brand-color, #4A90E2) 0%, #0056b3 100%);
    color: white;
    text-align: center;
    position: relative;
    overflow: hidden;
}
.blog-hero h1 {
    font-size: 3.5rem;
    font-weight: 800;
    letter-spacing: -1px;
    margin-bottom: 20px;
}
.blog-hero p {
    font-size: 1.25rem;
    opacity: 0.9;
    max-width: 600px;
    margin: 0 auto;
}
.blog-grid {
    padding: 60px 0;
}
.blog-card {
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.5);
    border-radius: 16px;
    overflow: hidden;
    transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    height: 100%;
    display: flex;
    flex-direction: column;
}
.blog-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 25px rgba(0,0,0,0.1);
    border-color: rgba(255, 255, 255, 0.8);
}
.blog-image {
    width: 100%;
    height: 240px;
    object-fit: cover;
    background: #f0f4f8;
}
.blog-content {
    padding: 24px;
    flex: 1;
    display: flex;
    flex-direction: column;
}
.blog-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #2d3748;
    text-decoration: none;
    margin-bottom: 12px;
    display: block;
    line-height: 1.3;
}
.blog-title:hover {
    color: var(--brand-color, #4A90E2);
}
.blog-summary {
    color: #718096;
    margin-bottom: 20px;
    flex: 1;
}
.blog-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    color: #a0aec0;
    font-size: 0.875rem;
    border-top: 1px solid #edf2f7;
    padding-top: 16px;
}
</style>

<section class="blog-hero">
    <div class="container">
        <h1>FamilyPlatform Insights</h1>
        <p>Discover stories, tips, and best practices for strengthening your family bonds and preserving memories.</p>
    </div>
</section>

<section class="blog-grid bg-light">
    <div class="container">
        @if(empty($blogs))
            <div class="text-center py-5">
                <h3>No articles published yet.</h3>
                <p class="text-muted">Check back soon for exciting updates and stories!</p>
            </div>
        @else
            <div class="row g-4">
                @foreach($blogs as $blog)
                <div class="col-md-6 col-lg-4">
                    <div class="blog-card">
                        @if(!empty($blog['cover_image']))
                            <img src="{{ htmlspecialchars($blog['cover_image']) }}" class="blog-image" alt="Cover Image">
                        @else
                            <div class="blog-image d-flex align-items-center justify-content-center text-muted">
                                <i class="fas fa-image fa-3x"></i>
                            </div>
                        @endif
                        
                        <div class="blog-content">
                            <a href="/blog/{{ htmlspecialchars($blog['slug']) }}" class="blog-title">
                                {{ htmlspecialchars($blog['title']) }}
                            </a>
                            <p class="blog-summary">
                                {{ htmlspecialchars($blog['summary'] ?? substr(strip_tags($blog['content']), 0, 100) . '...') }}
                            </p>
                            <div class="blog-meta">
                                <span><i class="far fa-user me-1"></i> {{ htmlspecialchars($blog['firstName'] . ' ' . $blog['lastName']) }}</span>
                                <span><i class="far fa-calendar-alt me-1"></i> {{ date('M j, Y', strtotime($blog['created_at'])) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
@endsection
