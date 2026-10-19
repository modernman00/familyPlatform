@extends('layouts.landing_layout')

@php
    $appBase = rtrim($_ENV['APP_URL'] ?? 'https://myfamilyplatform.com', '/');
    $canonicalUrl = $appBase . '/blog';
@endphp

@section('title', 'FamilyPlatform Journal — Stories, Heritage & Family Insights')
@section('meta_description', 'Discover inspiring family stories, memory preservation guides, parenting insights, and tips for celebrating milestones across generations.')
@section('og_title', 'FamilyPlatform Journal — Stories, Heritage & Family Insights')
@section('og_description', 'Discover inspiring family stories, memory preservation guides, parenting insights, and tips for celebrating milestones across generations.')
@section('og_type', 'website')
@section('og_url', $canonicalUrl)
@section('data-page-id', 'blog-index')

@section('extra_schema')
<script type="application/ld+json" nonce="{{ $nonce ?? '' }}">
{
  "@context": "https://schema.org",
  "@type": "Blog",
  "name": "FamilyPlatform Journal",
  "description": "Inspiring family stories, memory preservation guides, and heritage insights.",
  "url": {!! json_encode($canonicalUrl) !!},
  "publisher": {
    "@type": "Organization",
    "name": "FamilyPlatform",
    "url": {!! json_encode($appBase) !!},
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
    .blog-hero {
        background: radial-gradient(circle at 10% 20%, rgba(0, 191, 165, 0.12) 0%, rgba(0, 65, 130, 0.07) 90%);
        border-radius: 1.5rem;
        padding: 3.5rem 1.5rem;
        margin-bottom: 3rem;
        border: 1px solid rgba(0, 191, 165, 0.2);
    }

    .blog-pill-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.35rem 1rem;
        border-radius: 2rem;
        background: rgba(0, 191, 165, 0.12);
        color: #00796b;
        font-size: 0.85rem;
        font-weight: 700;
        letter-spacing: 0.02em;
        margin-bottom: 1rem;
    }

    .blog-card {
        border-radius: 1.25rem;
        border: 1px solid rgba(0, 0, 0, 0.08);
        background: #ffffff;
        transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        height: 100%;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
    }

    .blog-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 16px 32px -8px rgba(0, 65, 130, 0.14);
        border-color: rgba(0, 191, 165, 0.4);
    }

    .blog-img-wrap {
        position: relative;
        width: 100%;
        padding-top: 56.25%; /* 16:9 Aspect Ratio */
        background: #f1f5f9;
        overflow: hidden;
    }

    .blog-img-wrap img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.4s ease;
    }

    .blog-card:hover .blog-img-wrap img {
        transform: scale(1.05);
    }

    .blog-badge-date {
        position: absolute;
        bottom: 0.85rem;
        left: 0.85rem;
        background: rgba(15, 23, 42, 0.78);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        color: #f8fafc;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.35rem 0.75rem;
        border-radius: 2rem;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.25);
    }

    .blog-card-body {
        padding: 1.5rem;
        display: flex;
        flex-direction: column;
        flex-grow: 1;
    }

    .blog-card-title {
        font-size: 1.25rem;
        font-weight: 700;
        line-height: 1.4;
        margin-bottom: 0.75rem;
        color: #1a202c;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .blog-card-title a {
        color: inherit;
        text-decoration: none;
        transition: color 0.2s ease;
    }

    .blog-card-title a:hover {
        color: var(--brand-secondary, #004182);
    }

    .blog-card-excerpt {
        font-size: 0.92rem;
        line-height: 1.6;
        color: #64748b;
        margin-bottom: 1.25rem;
        flex-grow: 1;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .blog-card-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-top: 1rem;
        border-top: 1px solid rgba(0, 0, 0, 0.06);
        margin-top: auto;
    }

    .btn-read-more {
        font-weight: 700;
        font-size: 0.88rem;
        color: var(--brand-secondary, #004182);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        transition: gap 0.2s ease, color 0.2s ease;
    }

    .btn-read-more:hover {
        gap: 0.65rem;
        color: var(--brand-primary, #00bfa5);
    }

    .share-btn-pill {
        border-radius: 2rem;
        padding: 0.4rem 0.9rem;
        font-size: 0.82rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        transition: all 0.2s ease;
        border-color: rgba(0, 65, 130, 0.2);
        color: var(--brand-secondary, #004182);
    }

    .share-btn-pill:hover {
        background: var(--brand-secondary, #004182);
        color: #ffffff;
        border-color: var(--brand-secondary, #004182);
    }

    .topic-pill {
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0.2rem 0.6rem;
        border-radius: 1rem;
        background: rgba(0, 191, 165, 0.1);
        color: #00796b;
    }

    .read-time-meta {
        font-size: 0.75rem;
        color: #64748b;
        font-weight: 500;
    }
</style>

<div class="container my-5">
    <!-- Hero Header -->
    <div class="blog-hero text-center">
        <div class="blog-pill-badge">
            <i class="fas fa-heart"></i> Family Heritage & Living Insights
        </div>
        <h1 class="fw-bold mb-3" style="font-size: clamp(2.2rem, 4.5vw, 3.25rem); color: var(--brand-secondary, #004182); letter-spacing: -0.5px;">
            The FamilyPlatform Journal
        </h1>
        <p class="text-muted mx-auto mb-0" style="max-width: 680px; font-size: 1.1rem; line-height: 1.65;">
            Cherished stories, parenting guides, family tree traditions, and private digital archiving insights to keep your loved ones bonded across generations.
        </p>
    </div>

    <!-- Active Articles Grid -->
    @if(empty($blogs))
        <div class="text-center py-5 my-5">
            <div class="mb-3 text-muted" style="font-size: 3.5rem; opacity: 0.6;">
                <i class="far fa-newspaper"></i>
            </div>
            <h3 class="fw-bold" style="color: #1a202c;">No journal articles published yet</h3>
            <p class="text-muted">Check back soon for heartwarming stories, family tips, and heritage features!</p>
            <a href="/" class="btn btn-primary rounded-pill px-4 mt-2" style="background-color: var(--brand-primary, #00bfa5); border: none;">
                <i class="fas fa-home me-2"></i>Return Home
            </a>
        </div>
    @else
        <!-- Results count bar -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div class="text-muted small fw-semibold">
                <i class="fas fa-book-open me-1" style="color: var(--brand-primary, #00bfa5);"></i> 
                Showing {{ count($blogs) }} {{ count($blogs) === 1 ? 'article' : 'articles' }}
            </div>
            <div class="badge rounded-pill text-bg-light border px-3 py-2 small fw-semibold" style="color: #475569;">
                <i class="fas fa-clock me-1" style="color: var(--brand-primary, #00bfa5);"></i> Latest Published
            </div>
        </div>

        <div class="row g-4 mb-5">
            @foreach ($blogs as $blog)
                @php
                    $postSlug = $blog['slug'] ?? $blog['id'];
                    $postUrl = $appBase . '/blog/' . $postSlug;
                    $dateStr = !empty($blog['created_at']) ? date('M j, Y', strtotime($blog['created_at'])) : 'Recently Added';
                    $rawContent = $blog['content'] ?? '';
                    $rawExcerpt = !empty($blog['summary']) ? $blog['summary'] : strip_tags($rawContent);
                    $excerpt = function_exists('mb_substr') ? mb_substr(trim($rawExcerpt), 0, 130) : substr(trim($rawExcerpt), 0, 130);
                    $readTime = max(1, (int) ceil(str_word_count(strip_tags($rawContent)) / 200));
                    $authorName = trim(($blog['firstName'] ?? '') . ' ' . ($blog['lastName'] ?? ''));
                    if (empty($authorName)) {
                        $authorName = 'FamilyPlatform Team';
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

                <div class="col-12 col-md-6 col-lg-4" id="blog-{{ $blog['id'] }}">
                    <article class="blog-card">
                        <div class="blog-img-wrap">
                            <a href="/blog/{{ htmlspecialchars($postSlug, ENT_QUOTES, 'UTF-8') }}" aria-label="{{ htmlspecialchars($blog['title'], ENT_QUOTES, 'UTF-8') }}">
                                <img src="{{ htmlspecialchars($imgSrc, ENT_QUOTES, 'UTF-8') }}" 
                                     alt="{{ htmlspecialchars($blog['title'], ENT_QUOTES, 'UTF-8') }}" 
                                     loading="lazy"
                                     onerror="this.onerror=null; this.src='{{ $appBase }}/public/img/og-invite.jpg';">
                            </a>
                            <span class="blog-badge-date">
                                <i class="far fa-calendar-alt"></i> {{ $dateStr }}
                            </span>
                        </div>

                        <div class="blog-card-body">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="topic-pill">
                                    <i class="fas fa-bookmark me-1"></i> Family Stories
                                </span>
                                <span class="read-time-meta">
                                    <i class="far fa-clock me-1"></i> {{ $readTime }} min read
                                </span>
                            </div>

                            <h2 class="blog-card-title">
                                <a href="/blog/{{ htmlspecialchars($postSlug, ENT_QUOTES, 'UTF-8') }}">
                                    {{ htmlspecialchars($blog['title'], ENT_QUOTES, 'UTF-8') }}
                                </a>
                            </h2>

                            <p class="blog-card-excerpt">
                                {{ htmlspecialchars($excerpt, ENT_QUOTES, 'UTF-8') }}{{ strlen($rawExcerpt) > 130 ? '...' : '' }}
                            </p>

                            <div class="blog-card-footer">
                                <a href="/blog/{{ htmlspecialchars($postSlug, ENT_QUOTES, 'UTF-8') }}" class="btn-read-more" aria-label="Read full article: {{ htmlspecialchars($blog['title'], ENT_QUOTES, 'UTF-8') }}">
                                    Read Story <i class="fas fa-arrow-right"></i>
                                </a>

                                <button type="button" 
                                        class="btn btn-sm btn-outline-secondary share-btn-pill js-share-trigger"
                                        data-url="{{ htmlspecialchars($postUrl, ENT_QUOTES, 'UTF-8') }}"
                                        data-title="{{ htmlspecialchars($blog['title'], ENT_QUOTES, 'UTF-8') }}"
                                        data-excerpt="{{ htmlspecialchars($excerpt, ENT_QUOTES, 'UTF-8') }}"
                                        data-img="{{ htmlspecialchars($imgSrc, ENT_QUOTES, 'UTF-8') }}"
                                        aria-label="Share story: {{ htmlspecialchars($blog['title'], ENT_QUOTES, 'UTF-8') }}">
                                    <i class="fas fa-share-alt"></i> Share
                                </button>
                            </div>
                        </div>
                    </article>
                </div>
            @endforeach
        </div>
    @endif

    <div class="text-center my-4">
        <a href="/" class="btn btn-light rounded-pill px-4 border text-muted shadow-sm">
            <i class="fas fa-arrow-left me-2"></i> Return to Homepage
        </a>
    </div>
</div>

<!-- Reusable Rich Share Modal (WhatsApp & Multi-Platform) -->
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
                    (window.swalToast || alert)('Please copy the URL manually: ' + text, 'info');
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
