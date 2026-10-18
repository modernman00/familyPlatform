@extends('layouts.profileBase')
@section('title', 'Write Family Chronicle — FamilyPlatform')
@section('data-page-id', 'createFamilyJournalPage')

@push('styles')
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
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
        aspect-ratio: 16 / 9;
        max-width: 380px;
        border-radius: 14px;
        border: 2px dashed #cbd5e1;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        background: #f8fafc;
    }

    .preview-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
</style>
@endpush

@section('content')
<div class="container py-4 my-2" style="max-width: 900px;">
    <!-- Top Action Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <a href="/familyJournal" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 mb-2">
                <i class="bi bi-arrow-left me-1"></i> Back to Family Shelf
            </a>
            <h2 class="fw-bold mb-1" style="color: #1e1b4b; letter-spacing: -0.02em;">
                Preserve a Family Chronicle
            </h2>
            <p class="text-muted small mb-0">
                Private to Family Network #{{ htmlspecialchars($familyCode, ENT_QUOTES, 'UTF-8') }}
            </p>
        </div>
        <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill fw-semibold">
            <i class="bi bi-lock-fill me-1"></i> Private & Ad-Free
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
                    <option value="Heritage & Tradition">Tone: Heritage</option>
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
        <form id="journalForm" enctype="multipart/form-data">
            @include('csrf')

            <!-- Title -->
            <div class="mb-4">
                <label for="titleInput" class="form-label fw-bold" style="color: #1e1b4b;">Story Title <span class="text-danger">*</span></label>
                <input type="text" class="form-control form-control-lg rounded-3" id="titleInput" name="title" placeholder="e.g. The Secret Ingredient in Grandmother's Sunday Bread" required style="font-size: 1.25rem; font-weight: 600;">
            </div>

            <!-- Category & Mood -->
            <div class="row g-3 mb-4">
                <div class="col-sm-6">
                    <label for="categorySelect" class="form-label fw-semibold small text-muted">Category</label>
                    <select class="form-select rounded-3" id="categorySelect" name="category">
                        <option value="Milestone">Milestone & Life Event</option>
                        <option value="Heritage & Tradition">Heritage & Family Tradition</option>
                        <option value="Recipe">Heirloom Recipe & Kitchen Story</option>
                        <option value="Oral History">Oral History / Grandparent Memories</option>
                        <option value="Celebration">Reunion & Celebration</option>
                        <option value="Memorial">Memorial & Tribute</option>
                    </select>
                </div>
                <div class="col-sm-6">
                    <label for="moodSelect" class="form-label fw-semibold small text-muted">Story Mood</label>
                    <select class="form-select rounded-3" id="moodSelect" name="mood">
                        <option value="heartwarming">Heartwarming</option>
                        <option value="reflective">Reflective & Nostalgic</option>
                        <option value="inspiring">Inspiring & Triumphant</option>
                        <option value="humorous">Humorous & Playful</option>
                    </select>
                </div>
            </div>

            <!-- Summary / Excerpt -->
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label for="summaryInput" class="form-label fw-semibold small text-muted mb-0">Story Summary (Preview Excerpt)</label>
                    <button type="button" id="btnAutoSummarize" class="btn btn-link btn-sm p-0 text-decoration-none fw-semibold" style="font-size: 0.8rem; color: #4f46e5;">
                        <i class="bi bi-magic me-1"></i> Auto-Summarize Content
                    </button>
                </div>
                <textarea class="form-control rounded-3" id="summaryInput" name="summary" rows="2" placeholder="A brief teaser to show in the family journal shelf..."></textarea>
            </div>

            <!-- Rich Content Editor -->
            <div class="mb-4">
                <label class="form-label fw-bold" style="color: #1e1b4b;">Story Content <span class="text-danger">*</span></label>
                <div id="quillEditor"></div>
                <input type="hidden" name="content" id="contentInput">
            </div>

            <!-- Cover Photo Upload -->
            <div class="mb-4">
                <label for="coverImageInput" class="form-label fw-semibold small text-muted">Cover Photo (Optional)</label>
                <div class="d-flex flex-column flex-sm-row align-items-start gap-3">
                    <div class="preview-box" id="previewContainer">
                        <div class="text-center text-muted p-3" id="previewPlaceholder">
                            <i class="bi bi-image fs-1 opacity-50"></i>
                            <div class="small mt-1">Photo Preview</div>
                        </div>
                        <img id="imagePreview" src="" alt="Preview" style="display: none;">
                    </div>
                    <div>
                        <input type="file" class="form-control rounded-pill mb-2" id="coverImageInput" name="cover_image" accept="image/jpeg,image/png,image/webp">
                        <small class="text-muted d-block">Recommended 16:9 ratio (e.g. 1200x675). Max 10MB.</small>
                    </div>
                </div>
            </div>

            <!-- Status & Submit -->
            <div class="d-flex align-items-center justify-content-between pt-4 border-top flex-wrap gap-3">
                <div class="d-flex align-items-center gap-2">
                    <label for="statusSelect" class="form-label small fw-semibold text-muted mb-0">Status:</label>
                    <select class="form-select form-select-sm rounded-pill" id="statusSelect" name="status" style="width: 140px;">
                        <option value="published">Published</option>
                        <option value="draft">Save as Draft</option>
                    </select>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <a href="/familyJournal" class="btn btn-light rounded-pill px-4">Cancel</a>
                    <button type="submit" id="btnSubmitJournal" class="btn btn-primary rounded-pill px-4 fw-bold" style="background: #4f46e5; border-color: #4f46e5; min-height: 44px;">
                        <i class="bi bi-cloud-arrow-up-fill me-1"></i> Preserve Chronicle
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@include('component.submitSpinner')

@push('scripts')
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const quill = new Quill('#quillEditor', {
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

    // Image preview handler
    const coverInput = document.getElementById('coverImageInput');
    const imagePreview = document.getElementById('imagePreview');
    const previewPlaceholder = document.getElementById('previewPlaceholder');

    coverInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                imagePreview.src = e.target.result;
                imagePreview.style.display = 'block';
                previewPlaceholder.style.display = 'none';
            };
            reader.readAsDataURL(file);
        } else {
            imagePreview.src = '';
            imagePreview.style.display = 'none';
            previewPlaceholder.style.display = 'block';
        }
    });

    // AI Story Drafter
    const btnAiDraft = document.getElementById('btnGenerateAiDraft');
    const aiPromptInput = document.getElementById('aiPromptInput');
    const aiToneSelect = document.getElementById('aiToneSelect');
    const aiStatusAlert = document.getElementById('aiStatusAlert');

    btnAiDraft.addEventListener('click', function() {
        const prompt = aiPromptInput.value.trim();
        if (!prompt) {
            alert('Please enter a brief story prompt or family memory topic.');
            return;
        }

        btnAiDraft.disabled = true;
        btnAiDraft.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Drafting...';
        aiStatusAlert.style.display = 'block';
        aiStatusAlert.innerHTML = '<span class="text-primary"><i class="bi bi-cpu me-1"></i> AI is crafting your family story...</span>';

        const csrfToken = document.querySelector('input[name="token"]')?.value || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        fetch('/familyJournal/ai-draft', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                prompt: prompt,
                tone: aiToneSelect.value,
                token: csrfToken
            })
        })
        .then(res => res.json())
        .then(res => {
            btnAiDraft.disabled = false;
            btnAiDraft.innerHTML = '<i class="bi bi-sparkles me-1"></i> Draft';

            if (res.status === 'success' && res.data) {
                document.getElementById('titleInput').value = res.data.title || '';
                document.getElementById('summaryInput').value = res.data.summary || '';
                quill.root.innerHTML = res.data.content || '';
                aiStatusAlert.innerHTML = '<span class="text-success fw-bold"><i class="bi bi-check-circle-fill me-1"></i> Story draft generated! Edit and customize it below.</span>';
            } else {
                aiStatusAlert.innerHTML = '<span class="text-danger">' + (res.message || 'Failed to draft story.') + '</span>';
            }
        })
        .catch(err => {
            console.error(err);
            btnAiDraft.disabled = false;
            btnAiDraft.innerHTML = '<i class="bi bi-sparkles me-1"></i> Draft';
            aiStatusAlert.innerHTML = '<span class="text-danger">A network error occurred while contacting AI.</span>';
        });
    });

    // Auto-Summarize Content
    const btnAutoSummarize = document.getElementById('btnAutoSummarize');
    btnAutoSummarize.addEventListener('click', function() {
        const text = quill.getText().trim();
        if (!text || text.length < 20) {
            alert('Please write some story content first to auto-summarize.');
            return;
        }

        const words = text.split(/\s+/).slice(0, 35).join(' ');
        document.getElementById('summaryInput').value = words + (text.split(/\s+/).length > 35 ? '...' : '');
    });

    // Form Submit
    const form = document.getElementById('journalForm');
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        // Copy Quill HTML to hidden content input
        const html = quill.root.innerHTML;
        const text = quill.getText().trim();

        if (!text) {
            alert('Please write some content for your story.');
            return;
        }

        document.getElementById('contentInput').value = html;

        const formData = new FormData(form);
        const submitBtn = document.getElementById('btnSubmitJournal');
        if (window.FamilyPlatformSpinner) {
            window.FamilyPlatformSpinner.start(submitBtn, ' Preserving...');
        } else {
            submitBtn.disabled = true;
        }

        fetch('/familyJournal/store', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                window.location.href = data.redirect || '/familyJournal';
            } else {
                alert(data.message || 'Failed to preserve chronicle.');
                if (window.FamilyPlatformSpinner) {
                    window.FamilyPlatformSpinner.stop(submitBtn);
                } else {
                    submitBtn.disabled = false;
                }
            }
        })
        .catch(err => {
            console.error(err);
            alert('A network error occurred. Please try again.');
            if (window.FamilyPlatformSpinner) {
                window.FamilyPlatformSpinner.stop(submitBtn);
            } else {
                submitBtn.disabled = false;
            }
        });
    });
});
</script>
@endpush
@endsection
