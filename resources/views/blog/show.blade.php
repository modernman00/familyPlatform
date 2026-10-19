@extends('layouts.landing_layout')

@php
    $appBase = rtrim($_ENV['APP_URL'] ?? 'https://myfamilyplatform.com', '/');
    $postSlug = $blog['slug'] ?? $blog['id'];
    $postUrl = $appBase . '/blog/' . $postSlug;
    $rawContent = $blog['content'] ?? '';
    $rawExcerpt = !empty($blog['summary']) ? $blog['summary'] : strip_tags($rawContent);
    $cleanExcerpt = function_exists('mb_substr') ? mb_substr(trim($rawExcerpt), 0, 160) : substr(trim($rawExcerpt), 0, 160);
    $readMinutes = max(1, (int) ceil(str_word_count(strip_tags($rawContent)) / 200));
    $datePublishedStr = !empty($blog['created_at']) ? date('F j, Y', strtotime($blog['created_at'])) : 'Recently Published';
    $publishedIso = !empty($blog['created_at']) ? date('c', strtotime($blog['created_at'])) : date('c');
    $authorName = trim(($blog['firstName'] ?? '') . ' ' . ($blog['lastName'] ?? ''));
    if (empty($authorName)) {
        $authorName = 'FamilyPlatform Editorial Team';
    }
    $coverImage = !empty($blog['cover_image']) ? $blog['cover_image'] : '';
    if (!empty($coverImage) && !str_starts_with($coverImage, 'http')) {
        $imgSrc = $appBase . $coverImage;
    } elseif (!empty($coverImage)) {
        $imgSrc = $coverImage;
    } else {
        $imgSrc = $appBase . '/public/img/og-invite.jpg';
    }
@endphp

@section('title', htmlspecialchars($blog['title'], ENT_QUOTES, 'UTF-8') . ' — FamilyPlatform Journal')
@section('meta_description', htmlspecialchars($cleanExcerpt, ENT_QUOTES, 'UTF-8'))
@section('og_type', 'article')
@section('og_url', $postUrl)
@section('og_title', htmlspecialchars($blog['title'], ENT_QUOTES, 'UTF-8'))
@section('og_description', htmlspecialchars($cleanExcerpt, ENT_QUOTES, 'UTF-8'))
@section('og_image', $imgSrc)
@section('data-page-id', 'blog-show')

@section('extra_schema')
<script type="application/ld+json" nonce="{{ $nonce ?? '' }}">
{
  "@context": "https://schema.org",
  "@type": "BlogPosting",
  "headline": {!! json_encode($blog['title']) !!},
  "description": {!! json_encode($cleanExcerpt) !!},
  "image": [{!! json_encode($imgSrc) !!}],
  "datePublished": {!! json_encode($publishedIso) !!},
  "dateModified": {!! json_encode($publishedIso) !!},
  "mainEntityOfPage": {
    "@type": "WebPage",
    "@id": {!! json_encode($postUrl) !!}
  },
  "author": {
    "@type": "Person",
    "name": {!! json_encode($authorName) !!}
  },
  "publisher": {
    "@type": "Organization",
    "name": "FamilyPlatform",
    "logo": {
      "@type": "ImageObject",
      "url": {!! json_encode($appBase . '/public/img/logo/logo.png') !!}
    }
  }
}
</script>
@endsection

@section('content')
<style nonce="{{ $nonce ?? '' }}">
    .article-container {
        max-width: 860px;
        margin: 0 auto;
    }

    .article-breadcrumb {
        font-size: 0.88rem;
        color: #64748b;
        margin-bottom: 1.5rem;
    }

    .article-breadcrumb a {
        color: #64748b;
        text-decoration: none;
        transition: color 0.2s ease;
    }

    .article-breadcrumb a:hover {
        color: var(--brand-secondary, #004182);
    }

    .article-hero-title {
        font-size: clamp(2.2rem, 4vw, 3.25rem);
        font-weight: 800;
        line-height: 1.25;
        color: #1a202c;
        letter-spacing: -0.5px;
        margin-bottom: 1.5rem;
    }

    .article-meta-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1rem;
        padding-bottom: 2rem;
        border-bottom: 1px solid rgba(0, 0, 0, 0.08);
        margin-bottom: 2.5rem;
    }

    .author-badge {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .author-avatar {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: rgba(0, 191, 165, 0.15);
        color: var(--brand-primary, #00bfa5);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.1rem;
    }

    .article-cover-wrap {
        position: relative;
        width: 100%;
        aspect-ratio: 16 / 9;
        border-radius: 1.5rem;
        overflow: hidden;
        box-shadow: 0 16px 36px -8px rgba(0, 65, 130, 0.12);
        margin-bottom: 3rem;
        background: #f1f5f9;
    }

    .article-cover-wrap img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .article-body {
        font-size: 1.18rem;
        line-height: 1.9;
        color: #2d3748;
    }

    .article-body p {
        margin-bottom: 1.75rem;
    }

    .article-body h2, .article-body h3, .article-body h4 {
        margin-top: 3rem;
        margin-bottom: 1.25rem;
        font-weight: 800;
        color: #1a202c;
        line-height: 1.35;
    }

    .article-body img {
        max-width: 100%;
        height: auto;
        border-radius: 1.25rem;
        margin: 2.5rem 0;
        box-shadow: 0 8px 24px rgba(0,0,0,0.06);
    }

    .article-body blockquote {
        border-left: 4px solid var(--brand-primary, #00bfa5);
        padding: 1.25rem 1.75rem;
        background: rgba(0, 191, 165, 0.05);
        border-radius: 0 1rem 1rem 0;
        font-style: italic;
        color: #334155;
        margin: 2.5rem 0;
    }

    .share-callout-card {
        background: linear-gradient(135deg, rgba(0, 191, 165, 0.08) 0%, rgba(0, 65, 130, 0.05) 100%);
        border: 1px solid rgba(0, 191, 165, 0.25);
        border-radius: 1.5rem;
        padding: 2.25rem;
        margin: 4rem 0 3rem;
    }

    .btn-whatsapp-hero {
        background: #25d366;
        color: #ffffff;
        font-weight: 700;
        border-radius: 2rem;
        padding: 0.75rem 1.75rem;
        display: inline-flex;
        align-items: center;
        gap: 0.6rem;
        border: none;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        text-decoration: none;
        box-shadow: 0 4px 14px rgba(37, 211, 102, 0.3);
    }

    .btn-whatsapp-hero:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(37, 211, 102, 0.4);
        color: #ffffff;
    }

    .recent-article-card {
        border-radius: 1.25rem;
        border: 1px solid rgba(0,0,0,0.08);
        background: #ffffff;
        overflow: hidden;
        height: 100%;
        display: flex;
        flex-direction: column;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        box-shadow: 0 4px 12px rgba(0,0,0,0.03);
    }

    .recent-article-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px -6px rgba(0, 65, 130, 0.12);
    }

    .recent-img-wrap {
        width: 100%;
        padding-top: 56.25%;
        position: relative;
        overflow: hidden;
        background: #f1f5f9;
    }

    .recent-img-wrap img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
</style>

<div class="container my-5">
    <div class="article-container">
        <!-- Breadcrumbs -->
        <nav aria-label="breadcrumb" class="article-breadcrumb">
            <a href="/"><i class="fas fa-home me-1"></i> Home</a> 
            <span class="mx-2 text-muted">/</span> 
            <a href="/blog">Journal</a> 
            <span class="mx-2 text-muted">/</span> 
            <span class="text-truncate">{{ htmlspecialchars($blog['title'], ENT_QUOTES, 'UTF-8') }}</span>
        </nav>

        <!-- Topic Pill -->
        <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-3" style="background: rgba(0, 191, 165, 0.12); color: #00796b; font-size: 0.82rem; font-weight: 700;">
            <i class="fas fa-heart"></i> Family Heritage & Stories
        </div>

        <!-- Article Headline -->
        <h1 class="article-hero-title">
            {{ htmlspecialchars($blog['title'], ENT_QUOTES, 'UTF-8') }}
        </h1>

        <!-- Metadata Bar -->
        <div class="article-meta-bar">
            <div class="author-badge">
                <div class="author-avatar">
                    {{ strtoupper(substr($authorName, 0, 1)) }}
                </div>
                <div>
                    <div class="fw-bold" style="font-size: 0.95rem; color: #1a202c;">{{ htmlspecialchars($authorName, ENT_QUOTES, 'UTF-8') }}</div>
                    <div class="text-muted small">
                        <i class="far fa-calendar-alt me-1"></i> {{ $datePublishedStr }}
                        <span class="mx-1">•</span>
                        <i class="far fa-clock me-1"></i> {{ $readMinutes }} min read
                    </div>
                </div>
            </div>

            <button type="button" 
                    class="btn btn-outline-secondary rounded-pill px-3 py-2 small fw-semibold js-share-trigger"
                    data-url="{{ htmlspecialchars($postUrl, ENT_QUOTES, 'UTF-8') }}"
                    data-title="{{ htmlspecialchars($blog['title'], ENT_QUOTES, 'UTF-8') }}"
                    data-excerpt="{{ htmlspecialchars($cleanExcerpt, ENT_QUOTES, 'UTF-8') }}"
                    data-img="{{ htmlspecialchars($imgSrc, ENT_QUOTES, 'UTF-8') }}">
                <i class="fas fa-share-alt me-1"></i> Share Story
            </button>
        </div>

        <!-- Hero Cover Image -->
        @if(!empty($coverImage))
            <div class="article-cover-wrap">
                <img src="{{ htmlspecialchars($imgSrc, ENT_QUOTES, 'UTF-8') }}" 
                     alt="{{ htmlspecialchars($blog['title'], ENT_QUOTES, 'UTF-8') }}"
                     onerror="this.onerror=null; this.src='{{ $appBase }}/public/img/og-invite.jpg';">
            </div>
        @endif

        <!-- Article Body -->
        <div class="article-body">
            {!! $blog['content'] !!}
        </div>

        <!-- Share Callout Card -->
        <div class="share-callout-card text-center">
            <div class="d-inline-flex p-3 rounded-circle mb-3" style="background: rgba(0, 191, 165, 0.15); color: var(--brand-primary, #00bfa5); font-size: 1.5rem;">
                <i class="fas fa-heart"></i>
            </div>
            <h3 class="fw-bold mb-2" style="color: #1a202c;">Cherished memories grow when shared</h3>
            <p class="text-muted mx-auto mb-4" style="max-width: 540px; font-size: 1rem;">
                Send this article directly to your family members or share it across your social networks.
            </p>

            <div class="d-flex flex-wrap justify-content-center gap-3">
                <a href="https://api.whatsapp.com/send?text={{ urlencode($blog['title'] . "\n\n" . $postUrl) }}" 
                   target="_blank" 
                   rel="noopener noreferrer" 
                   class="btn-whatsapp-hero">
                    <i class="fab fa-whatsapp fs-5"></i> Send to Family WhatsApp Group
                </a>

                <button type="button" 
                        class="btn btn-outline-secondary rounded-pill px-4 py-2 fw-semibold js-share-trigger"
                        data-url="{{ htmlspecialchars($postUrl, ENT_QUOTES, 'UTF-8') }}"
                        data-title="{{ htmlspecialchars($blog['title'], ENT_QUOTES, 'UTF-8') }}"
                        data-excerpt="{{ htmlspecialchars($cleanExcerpt, ENT_QUOTES, 'UTF-8') }}"
                        data-img="{{ htmlspecialchars($imgSrc, ENT_QUOTES, 'UTF-8') }}">
                    <i class="fas fa-share-alt me-1"></i> More Share Options
                </button>
            </div>
        </div>

        <!-- Navigation back -->
        <div class="text-center my-5">
            <a href="/blog" class="btn btn-outline-primary rounded-pill px-4 py-2 fw-semibold" style="color: var(--brand-secondary, #004182); border-color: rgba(0,65,130,0.3);">
                <i class="fas fa-arrow-left me-2"></i> Back to all stories
            </a>
        </div>
    </div>

    <!-- Related Articles Section -->
    @if(!empty($recentBlogs))
        <div class="mt-5 pt-5 border-top">
            <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
                <div>
                    <h3 class="fw-bold mb-1" style="color: #1a202c;">More from the FamilyPlatform Journal</h3>
                    <p class="text-muted small mb-0">Continue reading stories and insights from our community.</p>
                </div>
                <a href="/blog" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                    View All Stories <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>

            <div class="row g-4">
                @foreach($recentBlogs as $recent)
                    @php
                        $recentSlug = $recent['slug'] ?? $recent['id'];
                        $recentUrl = $appBase . '/blog/' . $recentSlug;
                        $recentDate = !empty($recent['created_at']) ? date('M j, Y', strtotime($recent['created_at'])) : 'Recent';
                        $recentRaw = $recent['content'] ?? '';
                        $recentExcerpt = !empty($recent['summary']) ? $recent['summary'] : strip_tags($recentRaw);
                        $recentClean = function_exists('mb_substr') ? mb_substr(trim($recentExcerpt), 0, 95) : substr(trim($recentExcerpt), 0, 95);
                        $recentCover = !empty($recent['cover_image']) ? $recent['cover_image'] : '';
                        if (!empty($recentCover) && !str_starts_with($recentCover, 'http')) {
                            $recentImg = $appBase . $recentCover;
                        } elseif (!empty($recentCover)) {
                            $recentImg = $recentCover;
                        } else {
                            $recentImg = $appBase . '/public/img/og-invite.jpg';
                        }
                    @endphp
                    <div class="col-12 col-md-4">
                        <article class="recent-article-card">
                            <div class="recent-img-wrap">
                                <a href="/blog/{{ htmlspecialchars($recentSlug, ENT_QUOTES, 'UTF-8') }}" aria-label="{{ htmlspecialchars($recent['title'], ENT_QUOTES, 'UTF-8') }}">
                                    <img src="{{ htmlspecialchars($recentImg, ENT_QUOTES, 'UTF-8') }}" 
                                         alt="{{ htmlspecialchars($recent['title'], ENT_QUOTES, 'UTF-8') }}" 
                                         loading="lazy"
                                         onerror="this.onerror=null; this.src='{{ $appBase }}/public/img/og-invite.jpg';">
                                </a>
                            </div>
                            <div class="p-3 d-flex flex-column flex-grow-1">
                                <span class="text-muted small mb-1" style="font-size: 0.75rem;">
                                    <i class="far fa-calendar-alt me-1"></i> {{ $recentDate }}
                                </span>
                                <h5 class="fw-bold mb-2 text-truncate" style="font-size: 1.05rem;">
                                    <a href="/blog/{{ htmlspecialchars($recentSlug, ENT_QUOTES, 'UTF-8') }}" class="text-dark text-decoration-none">
                                        {{ htmlspecialchars($recent['title'], ENT_QUOTES, 'UTF-8') }}
                                    </a>
                                </h5>
                                <p class="text-muted small mb-3 flex-grow-1" style="font-size: 0.85rem; line-height: 1.5;">
                                    {{ htmlspecialchars($recentClean, ENT_QUOTES, 'UTF-8') }}...
                                </p>
                                <a href="/blog/{{ htmlspecialchars($recentSlug, ENT_QUOTES, 'UTF-8') }}" class="btn-read-more" style="font-size: 0.82rem;">
                                    Read Article <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </article>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

<!-- Reusable Rich Share Modal -->
<div class="modal fade" id="blogShareModal" tabindex="-1" aria-labelledby="blogShareModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background: rgba(0, 191, 165, 0.15); color: var(--brand-primary, #00bfa5);">
                        <i class="fas fa-share-alt"></i>
                    </div>
                    <h5 class="modal-title fw-bold" id="blogShareModalLabel" style="color: #1a202c;">Share Article with Family</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <!-- Rich Preview Card Inside Modal -->
                <div class="p-3 mb-3 rounded-3 border bg-light d-flex gap-3 align-items-center">
                    <img id="modalShareImg" src="" alt="Thumbnail" class="rounded-3 object-fit-cover flex-shrink-0" style="width: 70px; height: 70px; background: #e2e8f0;">
                    <div class="overflow-hidden">
                        <h6 id="modalShareTitle" class="fw-bold mb-1 text-truncate" style="font-size: 0.95rem; color: #1a202c;"></h6>
                        <p id="modalShareExcerpt" class="text-muted small mb-0 text-truncate" style="font-size: 0.8rem;"></p>
                    </div>
                </div>

                <!-- Web Share API Button (Mobile & Supported Desktops) -->
                <div id="modalNativeShareWrap" class="mb-3 d-none">
                    <button type="button" id="btnModalNativeShare" class="btn w-100 py-2 rounded-3 fw-bold d-flex align-items-center justify-content-center gap-2 text-white shadow-sm" style="background-color: var(--brand-secondary, #004182); border: none;">
                        <i class="fas fa-share-alt"></i> Share via Device (System Sheet)
                    </button>
                    <div class="text-center text-muted small my-2">or send directly to:</div>
                </div>

                <!-- Direct Social Share Buttons Grid -->
                <div class="row g-2 text-center mb-3">
                    <div class="col-4 col-sm-2">
                        <a id="shareWhatsapp" href="#" target="_blank" rel="noopener noreferrer" class="btn w-100 py-2 rounded-3 d-flex flex-column align-items-center justify-content-center text-white text-decoration-none shadow-sm" style="background: #25d366; min-height: 60px;" aria-label="Share on WhatsApp">
                            <i class="fab fa-whatsapp fs-5 mb-1"></i>
                            <span style="font-size: 0.68rem; font-weight: 700;">WhatsApp</span>
                        </a>
                    </div>
                    <div class="col-4 col-sm-2">
                        <a id="shareX" href="#" target="_blank" rel="noopener noreferrer" class="btn w-100 py-2 rounded-3 d-flex flex-column align-items-center justify-content-center text-white text-decoration-none shadow-sm" style="background: #000000; min-height: 60px;" aria-label="Share on X / Twitter">
                            <i class="fab fa-twitter fs-5 mb-1"></i>
                            <span style="font-size: 0.68rem; font-weight: 700;">X (Twitter)</span>
                        </a>
                    </div>
                    <div class="col-4 col-sm-2">
                        <a id="shareFacebook" href="#" target="_blank" rel="noopener noreferrer" class="btn w-100 py-2 rounded-3 d-flex flex-column align-items-center justify-content-center text-white text-decoration-none shadow-sm" style="background: #1877f2; min-height: 60px;" aria-label="Share on Facebook">
                            <i class="fab fa-facebook-f fs-5 mb-1"></i>
                            <span style="font-size: 0.68rem; font-weight: 700;">Facebook</span>
                        </a>
                    </div>
                    <div class="col-4 col-sm-2">
                        <a id="shareLinkedin" href="#" target="_blank" rel="noopener noreferrer" class="btn w-100 py-2 rounded-3 d-flex flex-column align-items-center justify-content-center text-white text-decoration-none shadow-sm" style="background: #0a66c2; min-height: 60px;" aria-label="Share on LinkedIn">
                            <i class="fab fa-linkedin-in fs-5 mb-1"></i>
                            <span style="font-size: 0.68rem; font-weight: 700;">LinkedIn</span>
                        </a>
                    </div>
                    <div class="col-4 col-sm-2">
                        <a id="shareTelegram" href="#" target="_blank" rel="noopener noreferrer" class="btn w-100 py-2 rounded-3 d-flex flex-column align-items-center justify-content-center text-white text-decoration-none shadow-sm" style="background: #229ed9; min-height: 60px;" aria-label="Share on Telegram">
                            <i class="fab fa-telegram-plane fs-5 mb-1"></i>
                            <span style="font-size: 0.68rem; font-weight: 700;">Telegram</span>
                        </a>
                    </div>
                    <div class="col-4 col-sm-2">
                        <button type="button" id="btnModalCopyLink" class="btn w-100 py-2 rounded-3 d-flex flex-column align-items-center justify-content-center text-white shadow-sm" style="background: #475569; min-height: 60px; border: none;" aria-label="Copy Article Link">
                            <i class="far fa-copy fs-5 mb-1" id="btnModalCopyIcon"></i>
                            <span id="btnModalCopyText" style="font-size: 0.68rem; font-weight: 700;">Copy Link</span>
                        </button>
                    </div>
                </div>

                <!-- Input Copy Bar -->
                <div class="input-group">
                    <input type="text" id="modalShareUrlInput" class="form-control form-control-sm bg-light" readonly>
                    <button class="btn btn-outline-secondary btn-sm fw-bold" type="button" id="btnInputCopy">
                        <i class="far fa-copy me-1"></i> Copy
                    </button>
                </div>

                <!-- Feedback Toast / Alert -->
                <div id="blogShareToast" class="alert alert-success d-none mt-3 mb-0 py-2 text-center small rounded-3" role="alert" style="background: rgba(0, 191, 165, 0.15); border: 1px solid #00bfa5; color: #00796b;">
                    <i class="fas fa-check-circle me-1"></i> Story link copied to clipboard!
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra_js')
<script nonce="{{ $nonce ?? '' }}">
document.addEventListener('DOMContentLoaded', function () {
    const shareModalEl = document.getElementById('blogShareModal');
    let shareModal = null;
    if (shareModalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        shareModal = new bootstrap.Modal(shareModalEl);
    }

    const modalTitle = document.getElementById('modalShareTitle');
    const modalExcerpt = document.getElementById('modalShareExcerpt');
    const modalImg = document.getElementById('modalShareImg');
    const modalInput = document.getElementById('modalShareUrlInput');
    const nativeShareWrap = document.getElementById('modalNativeShareWrap');
    const btnNativeShare = document.getElementById('btnModalNativeShare');
    const btnCopyLink = document.getElementById('btnModalCopyLink');
    const btnInputCopy = document.getElementById('btnInputCopy');
    const copyText = document.getElementById('btnModalCopyText');
    const copyIcon = document.getElementById('btnModalCopyIcon');
    const shareToast = document.getElementById('blogShareToast');

    const shareWhatsapp = document.getElementById('shareWhatsapp');
    const shareX = document.getElementById('shareX');
    const shareFacebook = document.getElementById('shareFacebook');
    const shareLinkedin = document.getElementById('shareLinkedin');
    const shareTelegram = document.getElementById('shareTelegram');

    let currentShareData = { url: '', title: '', text: '' };

    if (navigator.share && nativeShareWrap) {
        nativeShareWrap.classList.remove('d-none');
    }

    document.querySelectorAll('.js-share-trigger').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const url = this.getAttribute('data-url') || window.location.href;
            const title = this.getAttribute('data-title') || document.title;
            const excerpt = this.getAttribute('data-excerpt') || '';
            const img = this.getAttribute('data-img') || '';

            currentShareData = {
                url: url,
                title: title,
                text: excerpt ? (title + ' — ' + excerpt) : title
            };

            if (modalTitle) modalTitle.textContent = title;
            if (modalExcerpt) modalExcerpt.textContent = excerpt;
            if (modalImg) {
                if (img) {
                    modalImg.src = img;
                    modalImg.classList.remove('d-none');
                } else {
                    modalImg.classList.add('d-none');
                }
            }
            if (modalInput) modalInput.value = url;
            if (shareToast) shareToast.classList.add('d-none');
            if (copyText) copyText.textContent = 'Copy Link';
            if (copyIcon) copyIcon.className = 'far fa-copy fs-5 mb-1';

            const encodedUrl = encodeURIComponent(url);
            const encodedTitle = encodeURIComponent(title);
            const whatsappText = encodeURIComponent(title + '\n\n' + url);

            if (shareWhatsapp) shareWhatsapp.href = 'https://api.whatsapp.com/send?text=' + whatsappText;
            if (shareX) shareX.href = 'https://twitter.com/intent/tweet?url=' + encodedUrl + '&text=' + encodedTitle;
            if (shareFacebook) shareFacebook.href = 'https://www.facebook.com/sharer/sharer.php?u=' + encodedUrl;
            if (shareLinkedin) shareLinkedin.href = 'https://www.linkedin.com/sharing/share-offsite/?url=' + encodedUrl;
            if (shareTelegram) shareTelegram.href = 'https://t.me/share/url?url=' + encodedUrl + '&text=' + encodedTitle;

            if (shareModal) {
                shareModal.show();
            }
        });
    });

    if (btnNativeShare) {
        btnNativeShare.addEventListener('click', async function () {
            if (navigator.share) {
                try {
                    await navigator.share({
                        title: currentShareData.title,
                        text: currentShareData.text,
                        url: currentShareData.url
                    });
                } catch (err) {
                    if (err.name !== 'AbortError') {
                        console.warn('Native share failed:', err);
                    }
                }
            }
        });
    }

    function copyToClipboard(text) {
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(showCopiedFeedback).catch(fallbackCopy);
        } else {
            fallbackCopy();
        }

        function fallbackCopy() {
            if (modalInput) {
                modalInput.select();
                modalInput.setSelectionRange(0, 99999);
                try {
                    document.execCommand('copy');
                    showCopiedFeedback();
                } catch (err) {
                    alert('Please copy the URL manually: ' + text);
                }
            }
        }
    }

    function showCopiedFeedback() {
        if (shareToast) {
            shareToast.classList.remove('d-none');
            setTimeout(function () {
                shareToast.classList.add('d-none');
            }, 3500);
        }
        if (copyText) copyText.textContent = 'Copied!';
        if (copyIcon) copyIcon.className = 'fas fa-check fs-5 mb-1 text-success';
        setTimeout(function () {
            if (copyText) copyText.textContent = 'Copy Link';
            if (copyIcon) copyIcon.className = 'far fa-copy fs-5 mb-1';
        }, 3000);
    }

    if (btnModalCopyLink) {
        btnModalCopyLink.addEventListener('click', function () {
            copyToClipboard(currentShareData.url || (modalInput ? modalInput.value : window.location.href));
        });
    }

    if (btnInputCopy) {
        btnInputCopy.addEventListener('click', function () {
            copyToClipboard(modalInput ? modalInput.value : window.location.href);
        });
    }
});
</script>
@endsection
