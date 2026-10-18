@extends('layouts.profileBase')
@section('title', 'Family Journal & Chronicles — ' . htmlspecialchars($familyCode, ENT_QUOTES, 'UTF-8'))
@section('data-page-id', 'familyJournalPage')

@push('styles')
<style>
    .journal-hero {
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.08) 0%, rgba(79, 70, 229, 0.05) 50%, rgba(16, 185, 129, 0.08) 100%);
        border-radius: 20px;
        border: 1px solid rgba(99, 102, 241, 0.15);
        padding: 2.5rem 1.5rem;
        margin-bottom: 2rem;
    }

    .journal-card {
        border-radius: 18px;
        overflow: hidden;
        border: 1px solid var(--border-color, #e2e8f0);
        background: var(--card-bg, #ffffff);
        transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .journal-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 16px 32px -8px rgba(99, 102, 241, 0.12), 0 4px 12px rgba(0, 0, 0, 0.04);
        border-color: rgba(99, 102, 241, 0.3);
    }

    .journal-cover-frame {
        position: relative;
        width: 100%;
        aspect-ratio: 16 / 9;
        overflow: hidden;
        background: #f1f5f9;
    }

    .journal-cover-frame img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.4s ease;
    }

    .journal-card:hover .journal-cover-frame img {
        transform: scale(1.04);
    }

    .category-badge {
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        padding: 0.3rem 0.75rem;
        border-radius: 2rem;
        background: rgba(99, 102, 241, 0.1);
        color: #4f46e5;
    }

    .read-time-pill {
        font-size: 0.75rem;
        color: #64748b;
        font-weight: 500;
    }

    .btn-create-journal {
        background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
        color: #ffffff;
        font-weight: 600;
        border-radius: 50px;
        padding: 0.65rem 1.4rem;
        border: none;
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .btn-create-journal:hover {
        color: #ffffff;
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(79, 70, 229, 0.35);
    }

    .filter-pill-btn {
        border-radius: 50px;
        padding: 0.35rem 1rem;
        font-size: 0.85rem;
        font-weight: 600;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        color: #475569;
        transition: all 0.2s;
    }

    .filter-pill-btn:hover, .filter-pill-btn.active {
        background: #4f46e5;
        color: #ffffff;
        border-color: #4f46e5;
    }
</style>
@endpush

@section('content')
<div class="container py-4 my-2">
    <!-- Hero Header -->
    <div class="journal-hero text-center position-relative">
        <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-3 py-2 rounded-pill mb-2" style="font-size: 0.82rem; letter-spacing: 0.04em;">
            <i class="bi bi-shield-lock-fill me-1"></i> PRIVATE TO FAMILY NETWORK #{{ htmlspecialchars($familyCode, ENT_QUOTES, 'UTF-8') }}
        </span>
        <h1 class="fw-bold mb-2" style="color: #1e1b4b; font-size: clamp(1.8rem, 3.5vw, 2.5rem); letter-spacing: -0.02em;">
            Family Living Journal & Chronicles
        </h1>
        <p class="text-muted mx-auto mb-3" style="max-width: 650px; font-size: 1rem; line-height: 1.6;">
            A private family sanctuary to record oral histories, milestone memories, heirloom recipes, and generational triumphs away from public social media.
        </p>

        <div class="d-flex justify-content-center gap-2 flex-wrap">
            <a href="/familyJournal/create" class="btn btn-create-journal d-inline-flex align-items-center gap-2">
                <i class="bi bi-pencil-square"></i> Write New Family Chronicle
            </a>
            <a href="/profilePage" class="btn btn-outline-secondary rounded-pill px-3 py-2 fw-semibold" style="font-size: 0.9rem;">
                <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Empty State -->
    @if(empty($journals))
        <div class="card border-0 shadow-sm text-center py-5 px-4 my-4" style="border-radius: 20px; background: #ffffff;">
            <div class="rounded-circle d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px; background: rgba(99, 102, 241, 0.1); color: #4f46e5; font-size: 2.2rem;">
                <i class="bi bi-journal-plus"></i>
            </div>
            <h3 class="fw-bold mb-2" style="color: #1e1b4b;">No Chronicles Written Yet</h3>
            <p class="text-muted mx-auto mb-4" style="max-width: 520px;">
                Start preserving your family heritage today. Chronicle a favorite grandparent memory, a cherished holiday tradition, or an unforgettable milestone.
            </p>
            <div>
                <a href="/familyJournal/create" class="btn btn-create-journal px-4 py-2">
                    <i class="bi bi-magic me-1"></i> Write with AI Story Assistant
                </a>
            </div>
        </div>
    @else
        <!-- Filter Controls -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div class="d-flex align-items-center gap-1 flex-wrap" id="categoryFilters">
                <button type="button" class="filter-pill-btn active" data-filter="all">All Chronicles ({{ count($journals) }})</button>
                <button type="button" class="filter-pill-btn" data-filter="Milestone">Milestones</button>
                <button type="button" class="filter-pill-btn" data-filter="Heritage & Tradition">Heritage</button>
                <button type="button" class="filter-pill-btn" data-filter="Recipe">Recipes</button>
                <button type="button" class="filter-pill-btn" data-filter="Oral History">Oral History</button>
                <button type="button" class="filter-pill-btn" data-filter="Celebration">Celebrations</button>
            </div>
            <span class="text-muted small fw-semibold">
                <i class="bi bi-eye me-1"></i> Visible to Family #{{ htmlspecialchars($familyCode, ENT_QUOTES, 'UTF-8') }}
            </span>
        </div>

        <!-- Journal Grid -->
        <div class="row g-4" id="journalCardsGrid">
            @foreach ($journals as $journal)
                @php
                    $journalCategory = htmlspecialchars((string)($journal['category'] ?? 'Milestone'), ENT_QUOTES, 'UTF-8');
                    $authorName = trim(htmlspecialchars((string)($journal['firstName'] ?? 'Family') . ' ' . (string)($journal['lastName'] ?? 'Member'), ENT_QUOTES, 'UTF-8'));
                    $authorAvatar = !empty($journal['author_avatar']) ? htmlspecialchars((string)$journal['author_avatar'], ENT_QUOTES, 'UTF-8') : 'avatarM.png';
                    $avatarSrc = str_starts_with($authorAvatar, '/') ? $authorAvatar : '/resources/images/profile/' . $authorAvatar;
                    $coverImage = !empty($journal['cover_image']) ? htmlspecialchars((string)$journal['cover_image'], ENT_QUOTES, 'UTF-8') : '/public/img/webImg/family3.jpeg';
                    $words = str_word_count(strip_tags((string)($journal['content'] ?? '')));
                    $readTime = max(1, (int)ceil($words / 200)) . ' min read';
                    $journalDate = !empty($journal['created_at']) ? date('M j, Y', strtotime((string)$journal['created_at'])) : 'Recent';
                @endphp
                <div class="col-md-6 col-lg-4 journal-item-col" data-category="{{ $journalCategory }}">
                    <div class="journal-card">
                        <!-- Cover Image -->
                        <a href="/familyJournal/{{ $journal['id'] }}" class="journal-cover-frame d-block text-decoration-none">
                            <img src="{{ $coverImage }}" alt="{{ htmlspecialchars((string)$journal['title'], ENT_QUOTES, 'UTF-8') }}" loading="lazy">
                        </a>

                        <div class="card-body p-3 p-sm-4 d-flex flex-column flex-grow-1">
                            <!-- Category & Read Time -->
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="category-badge">{{ $journalCategory }}</span>
                                <span class="read-time-pill"><i class="bi bi-clock me-1"></i>{{ $readTime }}</span>
                            </div>

                            <!-- Title -->
                            <h5 class="fw-bold mb-2">
                                <a href="/familyJournal/{{ $journal['id'] }}" class="text-dark text-decoration-none" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.35;">
                                    {{ htmlspecialchars((string)$journal['title'], ENT_QUOTES, 'UTF-8') }}
                                </a>
                            </h5>

                            <!-- Summary Excerpt -->
                            <p class="text-muted small mb-4 flex-grow-1" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.55;">
                                {{ htmlspecialchars((string)($journal['summary'] ?? strip_tags((string)$journal['content'])), ENT_QUOTES, 'UTF-8') }}
                            </p>

                            <!-- Author Footer & Action -->
                            <div class="d-flex align-items-center justify-content-between pt-3 border-top mt-auto">
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $avatarSrc }}" alt="{{ $authorName }}" class="rounded-circle" width="34" height="34" style="object-fit: cover;">
                                    <div>
                                        <div class="fw-bold small text-dark" style="line-height: 1.2;">{{ $authorName }}</div>
                                        <div class="text-muted" style="font-size: 0.72rem;">{{ $journalDate }}</div>
                                    </div>
                                </div>
                                <a href="/familyJournal/{{ $journal['id'] }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold" style="font-size: 0.8rem;">
                                    Read <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterButtons = document.querySelectorAll('#categoryFilters .filter-pill-btn');
    const cols = document.querySelectorAll('.journal-item-col');

    filterButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            filterButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            const filter = this.getAttribute('data-filter');
            cols.forEach(col => {
                if (filter === 'all' || col.getAttribute('data-category') === filter) {
                    col.style.display = 'block';
                } else {
                    col.style.display = 'none';
                }
            });
        });
    });
});
</script>
@endsection
