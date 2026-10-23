@extends('layouts.profileBase')
@section('title', 'Write Family Chronicle — FamilyPlatform')
@section('data-page-id', 'createFamilyJournalPage')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/quill@1.3.6/dist/quill.snow.css" rel="stylesheet">
<style>
    .ai-assistant-card {
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.08) 0%, rgba(139, 92, 246, 0.06) 100%);
        border: 1.5px dashed rgba(99, 102, 241, 0.35);
        border-radius: 16px;
    }

    .form-card {
        border-radius: 20px;
        background: var(--card-bg, #ffffff);
        border: 1px solid var(--border-color, #e2e8f0);
    }

    .ql-toolbar.ql-snow {
        border-top-left-radius: 12px;
        border-top-right-radius: 12px;
        border-color: #cbd5e1;
        background: #f8fafc;
    }

    .ql-container.ql-snow {
        border-bottom-left-radius: 12px;
        border-bottom-right-radius: 12px;
        border-color: #cbd5e1;
        font-size: 1.05rem;
        min-height: 280px;
        font-family: inherit;
    }

    .preview-box {
        width: 100%;
        max-width: 320px;
        aspect-ratio: 16 / 9;
        border-radius: 14px;
        border: 2px dashed #cbd5e1;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        background: #f8fafc;
        position: relative;
        transition: border-color 0.2s ease, background 0.2s ease;
    }

    .preview-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
</style>
@endpush

@section('content')
@php
    $isEdit = !empty($journal);
    $dec = static fn ($v): string => htmlspecialchars(html_entity_decode((string)($v ?? ''), ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8');
    $contributorIds = $contributorIds ?? [];
    $familyMembers = $familyMembers ?? [];
    $canManageContributors = $canManageContributors ?? true;
    $existingContentJson = $isEdit ? json_encode((string)$journal['content'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) : 'null';
@endphp
<div class="container py-4 my-2" style="max-width: 900px;">
    <!-- Top Action Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <a href="/familyJournal" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 mb-2">
                <i class="bi bi-arrow-left me-1"></i> Back to Family Shelf
            </a>
            <h2 class="fw-bold mb-1" style="color: #1e1b4b; letter-spacing: -0.02em;">
                {{ $isEdit ? 'Edit Family Chronicle' : 'Preserve a Family Chronicle' }}
            </h2>
            <p class="text-muted small mb-0">
                Private to Family Network #{{ htmlspecialchars($familyCode, ENT_QUOTES, 'UTF-8') }}
            </p>
        </div>
        <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill fw-semibold">
            <i class="bi bi-lock-fill me-1"></i> Private &amp; Secure
        </span>
    </div>

    <!-- AI Story Assistant Card -->
    <div class="card ai-assistant-card mb-4 p-4 shadow-sm">
        <div class="d-flex align-items-center gap-2 mb-2">
            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background: #6366f1; color: white;">
                <i class="bi bi-magic"></i>
            </div>
            <div>
                <h6 class="fw-bold mb-0" style="color: #4338ca;">AI Story Assistant & Chronicler</h6>
                <small class="text-muted">Turn brief prompts or scattered memories into a beautiful, heartfelt story.</small>
            </div>
        </div>

        <div class="row g-2 mt-2">
            <div class="col-md-7">
                <input type="text" id="aiPromptInput" class="form-control rounded-pill px-3" placeholder="e.g. How Grandma learned baking in Yorkshire, or our 2022 family beach camping trip">
            </div>
            <div class="col-md-3">
                <select id="aiToneSelect" class="form-select rounded-pill px-3">
                    <option value="Heartwarming">Tone: Heartwarming</option>
                    <option value="Heritage & Tradition" {{ $isEdit && ($journal['category'] ?? '') === 'Heritage & Tradition' ? 'selected' : '' }}>Tone: Heritage</option>
                    <option value="Celebratory">Tone: Celebratory</option>
                    <option value="Humorous">Tone: Humorous</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="button" id="btnGenerateAiDraft" class="btn btn-primary rounded-pill w-100 fw-semibold" style="background: #4f46e5; border-color: #4f46e5;">
                    <i class="bi bi-sparkles me-1"></i> Draft
                </button>
            </div>
        </div>
        <div id="aiStatusAlert" class="small text-muted mt-2" style="display: none;"></div>
    </div>

    <!-- Main Creation Form -->
    <div class="card form-card shadow-sm p-4 p-sm-5">
        <form id="journalForm" enctype="multipart/form-data" data-action="{{ $isEdit ? '/familyJournal/' . (int)$journal['id'] . '/update' : '/familyJournal/store' }}" data-edit="{{ $isEdit ? '1' : '0' }}">
            @include('csrf')

            <!-- Title -->
            <div class="mb-4">
                <label for="titleInput" class="form-label fw-bold" style="color: #1e1b4b;">Story Title <span class="text-danger">*</span></label>
                <input type="text" class="form-control form-control-lg rounded-3" id="titleInput" name="title" value="{{ $isEdit ? $dec($journal['title']) : '' }}" placeholder="e.g. The Secret Ingredient in Grandmother's Sunday Bread" required style="font-size: 1.25rem; font-weight: 600;">
            </div>

            <!-- Category & Mood -->
            <div class="row g-3 mb-4">
                <div class="col-sm-6">
                    <label for="categorySelect" class="form-label fw-semibold small text-muted">Category</label>
                    <select class="form-select rounded-3" id="categorySelect" name="category">
                        <option value="Milestone" {{ $isEdit && ($journal['category'] ?? '') === 'Milestone' ? 'selected' : '' }}>Milestone & Life Event</option>
                        <option value="Heritage & Tradition">Heritage & Family Tradition</option>
                        <option value="Recipe" {{ $isEdit && ($journal['category'] ?? '') === 'Recipe' ? 'selected' : '' }}>Heirloom Recipe & Kitchen Story</option>
                        <option value="Oral History" {{ $isEdit && ($journal['category'] ?? '') === 'Oral History' ? 'selected' : '' }}>Oral History / Grandparent Memories</option>
                        <option value="Celebration" {{ $isEdit && ($journal['category'] ?? '') === 'Celebration' ? 'selected' : '' }}>Reunion & Celebration</option>
                        <option value="Memorial" {{ $isEdit && ($journal['category'] ?? '') === 'Memorial' ? 'selected' : '' }}>Memorial & Tribute</option>
                    </select>
                </div>
                <div class="col-sm-6">
                    <label for="moodSelect" class="form-label fw-semibold small text-muted">Story Mood</label>
                    <select class="form-select rounded-3" id="moodSelect" name="mood">
                        <option value="heartwarming" {{ $isEdit && ($journal['mood'] ?? '') === 'heartwarming' ? 'selected' : '' }}>Heartwarming</option>
                        <option value="reflective" {{ $isEdit && ($journal['mood'] ?? '') === 'reflective' ? 'selected' : '' }}>Reflective & Nostalgic</option>
                        <option value="inspiring" {{ $isEdit && ($journal['mood'] ?? '') === 'inspiring' ? 'selected' : '' }}>Inspiring & Triumphant</option>
                        <option value="humorous" {{ $isEdit && ($journal['mood'] ?? '') === 'humorous' ? 'selected' : '' }}>Humorous & Playful</option>
                    </select>
                </div>
            </div>

            <!-- Summary / Excerpt -->
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label for="summaryInput" class="form-label fw-semibold small text-muted mb-0">Story Summary (Preview Excerpt)</label>
                    <button type="button" id="btnAutoSummarize" class="btn btn-link btn-sm p-0 text-decoration-none fw-semibold" style="font-size: 0.8rem; color: #4f46e5;">
                        <i class="bi bi-magic me-1"></i> Auto-Summarise Content
                    </button>
                </div>
                <textarea class="form-control rounded-3" id="summaryInput" name="summary" rows="2" placeholder="A brief teaser to show in the family journal shelf...">{{ $isEdit ? $dec($journal['summary']) : '' }}</textarea>
            </div>

            <!-- Rich Content Editor -->
            <div class="mb-4">
                <label class="form-label fw-bold" style="color: #1e1b4b;">Story Content <span class="text-danger">*</span></label>
                <div id="quillEditor" style="min-height: 280px; background: #ffffff;"></div>
                <textarea id="quillFallback" class="form-control rounded-3" style="display: none; min-height: 280px; font-size: 1rem;" placeholder="Write your family memory, stories, traditions, or recipes here..."></textarea>
                <input type="hidden" name="content" id="contentInput">
                <input type="hidden" name="remove_cover" id="removeCoverInput" value="0">
            </div>

            <!-- Cover Photo Upload -->
            <div class="mb-4">
                <label for="coverImageInput" class="form-label fw-semibold small text-muted">Cover Photo (Optional)</label>
                <div class="d-flex flex-column flex-sm-row align-items-start gap-3">
                    <div class="preview-box shadow-sm" id="previewContainer">
                        <div class="text-center text-muted p-3" id="previewPlaceholder" style="display: {{ $isEdit && !empty($journal['cover_image']) ? 'none' : 'block' }};">
                            <i class="bi bi-image fs-1 opacity-50"></i>
                            <div class="small mt-1 fw-semibold">Photo Preview</div>
                            <div class="text-muted" style="font-size: 0.72rem;">No cover selected yet</div>
                        </div>
                        <img id="imagePreview" src="{{ $isEdit && !empty($journal['cover_image']) ? htmlspecialchars((string)$journal['cover_image'], ENT_QUOTES, 'UTF-8') : '' }}" alt="Cover Photo Preview" style="display: {{ $isEdit && !empty($journal['cover_image']) ? 'block' : 'none' }}; width: 100%; height: 100%; object-fit: cover;">
                        <button type="button" id="btnRemoveCover" class="btn btn-sm btn-dark position-absolute top-0 end-0 m-2 rounded-circle shadow" style="display: {{ $isEdit && !empty($journal['cover_image']) ? 'inline-flex' : 'none' }}; width: 32px; height: 32px; padding: 0; align-items: center; justify-content: center; opacity: 0.88;" title="Remove cover photo" aria-label="Remove cover photo">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <div class="flex-grow-1">
                        <input type="file" class="form-control rounded-pill mb-2" id="coverImageInput" name="cover_image" accept="image/jpeg,image/png,image/webp">
                        <small class="text-muted d-block">Recommended 16:9 ratio (e.g. 1200x675). Max 10MB JPEG, PNG, or WebP.</small>
                    </div>
                </div>
            </div>

            <!-- Co-contributors -->
            @if($canManageContributors)
            <div class="mb-4">
                <label class="form-label fw-semibold small text-muted">Co-contributors <span class="fw-normal">(can edit this chronicle)</span></label>
                @php $authorKey = (string)($isEdit ? $journal['author_id'] : ($_SESSION['id'] ?? '')); @endphp
                @if(count(array_filter($familyMembers, static fn ($m) => (string)$m['id'] !== $authorKey)) === 0)
                    <div class="small text-muted">No other family members have joined yet. Invite relatives to write together.</div>
                @else
                <div class="border rounded-3 p-2" style="max-height: 200px; overflow-y: auto; background: #f8fafc;">
                    @foreach($familyMembers as $member)
                        @if((string)$member['id'] !== (string)($isEdit ? $journal['author_id'] : ($_SESSION['id'] ?? '')))
                        <div class="form-check d-flex align-items-center gap-2" style="min-height: 44px;">
                            <input class="form-check-input mt-0" type="checkbox" name="contributors[]" id="contrib_{{ htmlspecialchars((string)$member['id'], ENT_QUOTES, 'UTF-8') }}" value="{{ htmlspecialchars((string)$member['id'], ENT_QUOTES, 'UTF-8') }}" {{ in_array((string)$member['id'], $contributorIds, true) ? 'checked' : '' }}>
                            <label class="form-check-label small fw-semibold text-dark" for="contrib_{{ htmlspecialchars((string)$member['id'], ENT_QUOTES, 'UTF-8') }}" style="cursor: pointer;">
                                {{ htmlspecialchars(trim((string)($member['firstName'] ?? '') . ' ' . (string)($member['lastName'] ?? '')), ENT_QUOTES, 'UTF-8') }}
                            </label>
                        </div>
                        @endif
                    @endforeach
                </div>
                @endif
            </div>
            @endif

            <!-- Status & Submit -->
            <div class="d-flex align-items-center justify-content-between pt-4 border-top flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <div class="d-flex align-items-center gap-2">
                        <label for="statusSelect" class="form-label small fw-semibold text-muted mb-0">Status:</label>
                        <select class="form-select form-select-sm rounded-pill" id="statusSelect" name="status" style="width: 140px;">
                            <option value="published">Published</option>
                            <option value="draft" {{ $isEdit && ($journal['status'] ?? '') === 'draft' ? 'selected' : '' }}>Save as Draft</option>
                        </select>
                    </div>

                    <!-- Share to Family Feed Checkbox -->
                    @if(!$isEdit)
                    <div class="form-check form-switch mb-0 d-flex align-items-center gap-2" style="min-height: 44px;">
                        <input class="form-check-input" type="checkbox" id="shareToFeed" name="share_to_feed" value="1" checked style="cursor: pointer; width: 2.2em; height: 1.2em;">
                        <label class="form-check-label small fw-semibold text-dark mb-0" for="shareToFeed" style="cursor: pointer;">
                            <i class="bi bi-broadcast text-primary me-1"></i> Share to Family Feed
                        </label>
                    </div>
                    @endif
                </div>

                <div class="d-flex align-items-center gap-2">
                    <a href="{{ $isEdit ? '/familyJournal/' . (int)$journal['id'] : '/familyJournal' }}" class="btn btn-light rounded-pill px-4" style="min-height: 44px; display: inline-flex; align-items: center;">Cancel</a>
                    <button type="submit" id="btnSubmitJournal" class="btn btn-primary rounded-pill px-4 fw-bold btn-spin-on-click" style="background: #4f46e5; border-color: #4f46e5; min-height: 44px;">
                        <i class="bi bi-cloud-arrow-up-fill me-1"></i> {{ $isEdit ? 'Save Changes' : 'Preserve Chronicle' }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@include('component.submitSpinner')

@push('scripts')
<script nonce="{{ $nonce ?? '' }}" src="https://cdn.jsdelivr.net/npm/quill@1.3.6/dist/quill.min.js"></script>
<script nonce="{{ $nonce ?? '' }}">
document.addEventListener('DOMContentLoaded', function() {
    let quill = null;
    const quillContainer = document.getElementById('quillEditor');
    const fallbackTextarea = document.getElementById('quillFallback');

    if (typeof Quill !== 'undefined' && quillContainer) {
        try {
            quill = new Quill('#quillEditor', {
                theme: 'snow',
                placeholder: 'Write your family memory, stories, traditions, or recipes here...',
                modules: {
                    toolbar: [
                        [{ 'header': [2, 3, false] }],
                        ['bold', 'italic', 'underline', 'blockquote'],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        ['link', 'clean']
                    ]
                }
            });
        } catch (err) {
            console.warn('Quill initialization fallback:', err);
            quillContainer.style.display = 'none';
            fallbackTextarea.style.display = 'block';
        }
    } else {
        if (quillContainer) quillContainer.style.display = 'none';
        if (fallbackTextarea) fallbackTextarea.style.display = 'block';
    }

    const isEdit = document.getElementById('journalForm')?.dataset.edit === '1';
    @if($isEdit)
    const existingContent = {!! $existingContentJson !!};
    if (quill) {
        quill.clipboard.dangerouslyPasteHTML(existingContent);
    } else if (fallbackTextarea) {
        fallbackTextarea.value = existingContent.replace(/<\/p>\s*<p>/g, '\n\n').replace(/<br\s*\/?>/g, '\n').replace(/<[^>]*>/g, '');
    }
    @endif

    // Image preview handler
    const coverInput = document.getElementById('coverImageInput');
    const imagePreview = document.getElementById('imagePreview');
    const previewPlaceholder = document.getElementById('previewPlaceholder');
    const btnRemoveCover = document.getElementById('btnRemoveCover');

    function resetImagePreview() {
        if (imagePreview) {
            imagePreview.src = '';
            imagePreview.style.display = 'none';
        }
        if (previewPlaceholder) {
            previewPlaceholder.style.display = 'block';
        }
        if (btnRemoveCover) {
            btnRemoveCover.style.display = 'none';
        }
    }

    if (coverInput) {
        coverInput.addEventListener('change', function() {
            const file = this.files && this.files[0];
            if (file) {
                const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Please select a valid image file (JPEG, PNG, or WebP).');
                    coverInput.value = '';
                    resetImagePreview();
                    return;
                }
                if (file.size > 10 * 1024 * 1024) {
                    alert('Image exceeds the 10MB limit. Please choose a smaller file.');
                    coverInput.value = '';
                    resetImagePreview();
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    if (imagePreview) {
                        imagePreview.src = e.target.result;
                        imagePreview.style.display = 'block';
                    }
                    if (previewPlaceholder) {
                        previewPlaceholder.style.display = 'none';
                    }
                    if (btnRemoveCover) {
                        btnRemoveCover.style.display = 'inline-flex';
                    }
                };
                reader.readAsDataURL(file);
            } else {
                resetImagePreview();
            }
        });
    }

    if (btnRemoveCover) {
        btnRemoveCover.addEventListener('click', function(e) {
            e.preventDefault();
            if (coverInput) coverInput.value = '';
            const rc = document.getElementById('removeCoverInput');
            if (rc) rc.value = '1';
            resetImagePreview();
        });
    }

    // AI Story Drafter
    const btnAiDraft = document.getElementById('btnGenerateAiDraft');
    const aiPromptInput = document.getElementById('aiPromptInput');
    const aiToneSelect = document.getElementById('aiToneSelect');
    const aiStatusAlert = document.getElementById('aiStatusAlert');

    if (btnAiDraft) {
        btnAiDraft.addEventListener('click', function() {
            const prompt = aiPromptInput ? aiPromptInput.value.trim() : '';
            if (!prompt) {
                alert('Please enter a brief story prompt or family memory topic.');
                return;
            }

            btnAiDraft.disabled = true;
            btnAiDraft.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Drafting...';
            if (aiStatusAlert) {
                aiStatusAlert.style.display = 'block';
                aiStatusAlert.innerHTML = '<span class="text-primary"><i class="bi bi-cpu me-1"></i> AI is crafting your family story...</span>';
            }

            const csrfToken = document.querySelector('input[name="token"]')?.value || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            fetch('/familyJournal/ai-draft', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-XSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    prompt: prompt,
                    tone: aiToneSelect ? aiToneSelect.value : 'Heartwarming',
                    token: csrfToken
                })
            })
            .then(res => res.json())
            .then(res => {
                btnAiDraft.disabled = false;
                btnAiDraft.innerHTML = '<i class="bi bi-sparkles me-1"></i> Draft';

                if (res.status === 'success' && res.data) {
                    if (document.getElementById('titleInput')) {
                        document.getElementById('titleInput').value = res.data.title || '';
                    }
                    if (document.getElementById('summaryInput')) {
                        document.getElementById('summaryInput').value = res.data.summary || '';
                    }
                    if (quill) {
                        quill.root.innerHTML = res.data.content || '';
                    } else if (fallbackTextarea) {
                        fallbackTextarea.value = (res.data.content || '').replace(/<[^>]*>?/gm, '');
                    }
                    if (aiStatusAlert) {
                        aiStatusAlert.innerHTML = '<span class="text-success fw-bold"><i class="bi bi-check-circle-fill me-1"></i> Story draft generated! Edit and customise it below.</span>';
                    }
                } else {
                    if (aiStatusAlert) {
                        aiStatusAlert.innerHTML = '<span class="text-danger">' + (res.message || 'Failed to draft story.') + '</span>';
                    }
                }
            })
            .catch(err => {
                console.error(err);
                btnAiDraft.disabled = false;
                btnAiDraft.innerHTML = '<i class="bi bi-sparkles me-1"></i> Draft';
                if (aiStatusAlert) {
                    aiStatusAlert.innerHTML = '<span class="text-danger">A network error occurred while contacting AI.</span>';
                }
            });
        });
    }

    // Auto-Summarise Content
    const btnAutoSummarize = document.getElementById('btnAutoSummarize');
    if (btnAutoSummarize) {
        btnAutoSummarize.addEventListener('click', function() {
            let text = '';
            if (quill) {
                text = quill.getText().trim();
            } else if (fallbackTextarea) {
                text = fallbackTextarea.value.trim();
            }

            if (!text || text.length < 20) {
                alert('Please write some story content first to auto-summarise.');
                return;
            }

            const words = text.split(/\s+/).slice(0, 35).join(' ');
            const summaryField = document.getElementById('summaryInput');
            if (summaryField) {
                summaryField.value = words + (text.split(/\s+/).length > 35 ? '...' : '');
            }
        });
    }

    // Draft auto-save to prevent user data loss
    function saveLocalDraft() {
        try {
            const title = document.getElementById('titleInput')?.value || '';
            const summary = document.getElementById('summaryInput')?.value || '';
            const content = quill ? quill.root.innerHTML : (fallbackTextarea?.value || '');
            const hasText = title || summary || (quill && quill.getText().trim()) || (fallbackTextarea && fallbackTextarea.value.trim());
            if (hasText) {
                localStorage.setItem('fp_journal_draft', JSON.stringify({
                    title: title,
                    summary: summary,
                    content: content,
                    savedAt: Date.now()
                }));
            }
        } catch (e) {}
    }

    function restoreLocalDraft() {
        try {
            const saved = localStorage.getItem('fp_journal_draft');
            if (!saved) return;
            const draft = JSON.parse(saved);
            const titleEl = document.getElementById('titleInput');
            const summaryEl = document.getElementById('summaryInput');
            if (titleEl && !titleEl.value && draft.title) titleEl.value = draft.title;
            if (summaryEl && !summaryEl.value && draft.summary) summaryEl.value = draft.summary;
            if (draft.content) {
                if (quill && !quill.getText().trim()) {
                    quill.root.innerHTML = draft.content;
                } else if (fallbackTextarea && !fallbackTextarea.value.trim()) {
                    fallbackTextarea.value = draft.content.replace(/<[^>]*>?/gm, '');
                }
            }
        } catch (e) {}
    }

    if (!isEdit) { restoreLocalDraft(); }
    const debouncedSave = (function() {
        let timer = null;
        return function() {
            clearTimeout(timer);
            if (!isEdit) { timer = setTimeout(saveLocalDraft, 1000); }
        };
    })();

    document.getElementById('titleInput')?.addEventListener('input', debouncedSave);
    document.getElementById('summaryInput')?.addEventListener('input', debouncedSave);
    if (quill) {
        quill.on('text-change', debouncedSave);
    } else if (fallbackTextarea) {
        fallbackTextarea.addEventListener('input', debouncedSave);
    }

    // Form Submit
    const form = document.getElementById('journalForm');
    if (form) {
        let isSubmitting = false;
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            if (isSubmitting) { return; }

            let html = '';
            let text = '';
            if (quill) {
                html = quill.root.innerHTML;
                text = quill.getText().trim();
            } else if (fallbackTextarea) {
                text = fallbackTextarea.value.trim();
                html = '<p>' + text.replace(/\n\n+/g, '</p><p>').replace(/\n/g, '<br>') + '</p>';
            }

            // Defensive conversion safeguard: if story content was entered into the summary excerpt, preserve it seamlessly
            if (!text) {
                const summaryVal = document.getElementById('summaryInput')?.value?.trim() || '';
                if (summaryVal.length > 0) {
                    text = summaryVal;
                    html = '<p>' + text.replace(/\n\n+/g, '</p><p>').replace(/\n/g, '<br>') + '</p>';
                } else {
                    alert('Please write some content for your story.');
                    return;
                }
            }

            const contentInput = document.getElementById('contentInput');
            if (contentInput) {
                contentInput.value = html;
            }

            const formData = new FormData(form);
            const submitBtn = document.getElementById('btnSubmitJournal');
            isSubmitting = true;
            if (window.FamilyPlatformSpinner) {
                window.FamilyPlatformSpinner.start(submitBtn, ' Preserving...');
            } else if (submitBtn) {
                submitBtn.disabled = true;
            }

            fetch(form.dataset.action || '/familyJournal/store', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    try { localStorage.removeItem('fp_journal_draft'); } catch(e) {}
                    window.location.href = data.redirect || '/familyJournal';
                } else {
                    isSubmitting = false;
                    alert(data.message || 'Failed to preserve chronicle.');
                    if (window.FamilyPlatformSpinner) {
                        window.FamilyPlatformSpinner.stop(submitBtn);
                    } else if (submitBtn) {
                        submitBtn.disabled = false;
                    }
                }
            })
            .catch(err => {
                console.error(err);
                isSubmitting = false;
                alert('A network error occurred. Please try again.');
                if (window.FamilyPlatformSpinner) {
                    window.FamilyPlatformSpinner.stop(submitBtn);
                } else if (submitBtn) {
                    submitBtn.disabled = false;
                }
            });
        });
    }
});
</script>
@endpush
@endsection
