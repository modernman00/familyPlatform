@extends('layouts.profileBase')
@section('title', htmlspecialchars((string)$journal['title'], ENT_QUOTES, 'UTF-8') . ' — Family Chronicle')
@section('data-page-id', 'showFamilyJournalPage')

@push('styles')
<style>
    .story-hero {
        max-width: 820px;
        margin: 0 auto;
    }

    .story-cover-wrapper {
        border-radius: 20px;
        overflow: hidden;
        aspect-ratio: 16 / 9;
        margin-bottom: 2.5rem;
        box-shadow: 0 16px 36px -12px rgba(0, 0, 0, 0.12);
        background: #f1f5f9;
    }

    .story-cover-wrapper img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .story-body {
        font-size: 1.15rem;
        line-height: 1.85;
        color: #1e293b;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Georgia, serif;
    }

    .story-body p {
        margin-bottom: 1.6rem;
    }

    .story-body h2 {
        font-size: 1.6rem;
        font-weight: 700;
        color: #0f172a;
        margin-top: 2.2rem;
        margin-bottom: 1rem;
    }

    .story-body h3 {
        font-size: 1.35rem;
        font-weight: 600;
        color: #1e293b;
        margin-top: 1.8rem;
        margin-bottom: 0.8rem;
    }

    .story-body blockquote {
        border-left: 4px solid #6366f1;
        padding-left: 1.25rem;
        margin: 1.8rem 0;
        font-style: italic;
        color: #4338ca;
    }

    .whatsapp-share-btn {
        background-color: #25d366;
        color: #ffffff;
        font-weight: 700;
        border-radius: 50px;
        padding: 0.65rem 1.4rem;
        border: none;
        transition: transform 0.2s, background-color 0.2s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .whatsapp-share-btn:hover {
        background-color: #1ebe57;
        color: #ffffff;
        transform: translateY(-2px);
    }

    @media print {
        #mainNavbar, .no-print, .story-actions-bar {
            display: none !important;
        }
        .container {
            width: 100% !important;
            max-width: 100% !important;
        }
    }
</style>
@endpush

@section('content')
<div class="container py-4 my-2">
    <div class="story-hero">
        <!-- Breadcrumb & Back -->
        <div class="d-flex justify-content-between align-items-center mb-3 no-print flex-wrap gap-2">
            <a href="/familyJournal" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1">
                <i class="bi bi-arrow-left me-1"></i> Family Chronicles
            </a>
            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-semibold small">
                <i class="bi bi-shield-lock-fill me-1"></i> Family #{{ htmlspecialchars($familyCode, ENT_QUOTES, 'UTF-8') }} Sanctuary
            </span>
        </div>

        @php
            $authorName = trim(htmlspecialchars((string)($journal['firstName'] ?? 'Family') . ' ' . (string)($journal['lastName'] ?? 'Member'), ENT_QUOTES, 'UTF-8'));
            $authorAvatar = !empty($journal['author_avatar']) ? htmlspecialchars((string)$journal['author_avatar'], ENT_QUOTES, 'UTF-8') : 'avatarM.png';
            $avatarSrc = str_starts_with($authorAvatar, '/') ? $authorAvatar : '/resources/images/profile/' . $authorAvatar;
            $words = str_word_count(strip_tags((string)($journal['content'] ?? '')));
            $readTime = max(1, (int)ceil($words / 200)) . ' min read';
            $journalDate = !empty($journal['created_at']) ? date('F j, Y', strtotime((string)$journal['created_at'])) : 'Recent';
            $isAuthor = ($journal['author_id'] === ($_SESSION['id'] ?? '')) || (($_SESSION['type'] ?? '') === 'admin');
            $appUrl = rtrim((string)($_ENV['APP_URL'] ?? getenv('APP_URL') ?: 'https://myfamilyplatform.com'), '/');
            $currentStoryUrl = $appUrl . '/familyJournal/' . $journal['id'];
            $whatsappShareUrl = 'https://api.whatsapp.com/send?text=' . rawurlencode('Family Chronicle: "' . (string)$journal['title'] . '" by ' . $authorName . "\n\n" . $currentStoryUrl);
        @endphp

        <!-- Category & Read Time -->
        <div class="d-flex align-items-center gap-2 mb-2">
            <span class="badge bg-primary bg-opacity-10 text-primary text-uppercase px-3 py-2 rounded-pill fw-bold" style="font-size: 0.75rem; letter-spacing: 0.04em;">
                {{ htmlspecialchars((string)($journal['category'] ?? 'Milestone'), ENT_QUOTES, 'UTF-8') }}
            </span>
            <span class="text-muted small fw-semibold">
                <i class="bi bi-clock me-1"></i> {{ $readTime }}
            </span>
        </div>

        <!-- Title -->
        <h1 class="fw-bold mb-3" style="color: #0f172a; font-size: clamp(2rem, 4vw, 2.75rem); line-height: 1.25; letter-spacing: -0.02em;">
            {{ htmlspecialchars((string)$journal['title'], ENT_QUOTES, 'UTF-8') }}
        </h1>

        <!-- Author Byline -->
        <div class="d-flex align-items-center justify-content-between py-3 mb-4 border-top border-bottom flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <img src="{{ $avatarSrc }}" alt="{{ $authorName }}" class="rounded-circle shadow-sm" width="48" height="48" style="object-fit: cover;">
                <div>
                    <div class="fw-bold text-dark">{{ $authorName }}</div>
                    <div class="text-muted small">Preserved on {{ $journalDate }}</div>
                </div>
            </div>

            <!-- Share & Action Buttons -->
            <div class="d-flex align-items-center gap-2 no-print">
                <a href="{{ $whatsappShareUrl }}" target="_blank" rel="noopener noreferrer" class="whatsapp-share-btn btn-sm">
                    <i class="bi bi-whatsapp"></i> WhatsApp Family
                </a>
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-2" onclick="window.print()" title="Print or save as PDF">
                    <i class="bi bi-printer"></i>
                </button>
                @if($isAuthor)
                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-2" id="btnDeleteJournal" onclick="deleteFamilyJournal({{ (int)$journal['id'] }})">
                        <i class="bi bi-trash"></i>
                    </button>
                @endif
            </div>
        </div>

        <!-- Cover Photo (if exists) -->
        @if(!empty($journal['cover_image']))
            <div class="story-cover-wrapper">
                <img src="{{ htmlspecialchars((string)$journal['cover_image'], ENT_QUOTES, 'UTF-8') }}" alt="{{ htmlspecialchars((string)$journal['title'], ENT_QUOTES, 'UTF-8') }}">
            </div>
        @endif

        <!-- Story Content -->
        <article class="story-body mb-5">
            {!! $journal['content'] !!}
        </article>

        <!-- Bottom Family Keepsake Card -->
        <div class="card p-4 rounded-4 border-0 shadow-sm text-center my-4 no-print" style="background: linear-gradient(135deg, rgba(99, 102, 241, 0.08) 0%, rgba(16, 185, 129, 0.08) 100%);">
            <h5 class="fw-bold mb-2 text-dark">This story is preserved for future generations</h5>
            <p class="text-muted small mx-auto mb-3" style="max-width: 520px;">
                Only registered members of Family Network #{{ htmlspecialchars($familyCode, ENT_QUOTES, 'UTF-8') }} have access to this private chronicle.
            </p>
            <div class="d-flex justify-content-center gap-2 flex-wrap">
                <a href="/familyJournal" class="btn btn-outline-primary rounded-pill px-4 fw-semibold">
                    <i class="bi bi-journal-bookmark me-1"></i> More Family Chronicles
                </a>
                <a href="/familyJournal/create" class="btn btn-primary rounded-pill px-4 fw-semibold" style="background: #4f46e5; border-color: #4f46e5;">
                    <i class="bi bi-pencil-square me-1"></i> Write Another Chronicle
                </a>
            </div>
        </div>
    </div>
</div>

@if($isAuthor)
<script>
function deleteFamilyJournal(id) {
    if (!confirm('Are you sure you want to remove this family chronicle? This cannot be undone.')) {
        return;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
        || document.querySelector('input[name="token"]')?.value 
        || '{{ $_SESSION['token'] ?? '' }}';

    fetch('/familyJournal/' + id + '/delete', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-XSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ token: csrfToken })
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            alert('Chronicle deleted.');
            window.location.href = '/familyJournal';
        } else {
            alert(data.message || 'Unable to delete chronicle.');
        }
    })
    .catch(err => {
        console.error(err);
        alert('Network error occurred.');
    });
}
</script>
@endif
@endsection
