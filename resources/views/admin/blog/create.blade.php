@extends('admin.base')

@section('title', 'Create New Blog Post')

@section('content-title', "Create New Blog Post")

@section('content')
<!-- Include Quill stylesheet -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<!-- Include SweetAlert2 for notifications -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
.blog-editor-container {
    background: white;
    padding: 35px;
    border-radius: 16px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    max-width: 960px;
    margin: 0 auto 3rem;
}

.ai-assistant-card {
    background: linear-gradient(135deg, rgba(163,22,33, 0.08) 0%, rgba(122,14,22, 0.05) 100%);
    border: 1px solid rgba(163,22,33, 0.25);
    border-radius: 14px;
    padding: 24px;
    margin-bottom: 28px;
}

.ai-icon-badge {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(163,22,33, 0.2);
    color: #8a1019;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
}

.form-group {
    margin-bottom: 24px;
}

.form-group label {
    font-weight: 600;
    color: #363636;
    margin-bottom: 8px;
    display: block;
}

.input-control {
    width: 100%;
    padding: 12px 16px;
    border: 1px solid #dbdbdb;
    border-radius: 8px;
    font-size: 1rem;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.input-control:focus {
    border-color: #a31621;
    outline: none;
    box-shadow: 0 0 0 3px rgba(163,22,33, 0.2);
}

#editor-container {
    height: 440px;
    border-radius: 0 0 8px 8px;
    font-size: 1.1rem;
}

.ql-toolbar {
    border-radius: 8px 8px 0 0;
    background: #f8fafc;
}

.btn-submit {
    background: var(--brand-secondary, #7a0e16);
    color: white;
    border: none;
    padding: 12px 36px;
    font-size: 1.1rem;
    font-weight: 600;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.3s, transform 0.2s;
}

.btn-submit:hover {
    background: #002d5b;
    transform: translateY(-1px);
}

.btn-ai-sparkle {
    background: #a31621;
    color: white;
    border: none;
    padding: 10px 20px;
    font-weight: 600;
    border-radius: 8px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s;
}

.btn-ai-sparkle:hover {
    background: #8a1019;
    color: white;
    transform: translateY(-1px);
}

.btn-ai-sparkle:disabled {
    opacity: 0.65;
    cursor: not-allowed;
    transform: none;
}
</style>

<div class="blog-editor-container">
    <!-- AI Story Assistant Box -->
    <div class="ai-assistant-card">
        <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
            <div class="d-flex align-items-center gap-3">
                <span class="ai-icon-badge">
                    <i class="fas fa-magic"></i>
                </span>
                <div>
                    <h5 class="fw-bold mb-0" style="color: #1a202c; font-size: 1.1rem;">AI Family Story Assistant</h5>
                    <small class="text-muted">Draft complete heartwarming stories or generate SEO summaries in seconds</small>
                </div>
            </div>
            <span class="badge rounded-pill text-bg-light border px-3 py-1 small fw-semibold text-muted shadow-sm">
                <i class="fas fa-sparkles me-1 text-primary"></i> DeepSeek & GPT-4o
            </span>
        </div>

        <div class="row g-2 align-items-center">
            <div class="col-12 col-md-7">
                <input type="text" id="aiTopicInput" class="input-control" placeholder="Enter topic: e.g. 10 Meaningful Questions to Ask Your Grandparents..." style="background: white;">
            </div>
            <div class="col-12 col-md-3">
                <select id="aiToneSelect" class="input-control" style="background: white;">
                    <option value="warm, inspiring, and family-focused">Warm & Inspiring</option>
                    <option value="practical, step-by-step, and organized">Practical Guide</option>
                    <option value="nostalgic, emotional, and reflective">Nostalgic Heritage</option>
                    <option value="celebratory, cheerful, and energetic">Celebration & Milestone</option>
                </select>
            </div>
            <div class="col-12 col-md-2">
                <button type="button" id="btnAiGenerate" class="btn-ai-sparkle w-100 justify-content-center">
                    <i class="fas fa-magic"></i> Generate Draft
                </button>
            </div>
        </div>
    </div>

    <form id="blogForm" action="/admin/blog/store" method="POST" enctype="multipart/form-data">
        @include('csrf')
        @include('component.submitSpinner')

        <div class="form-group">
            <label for="title">Blog Title</label>
            <input type="text" id="title" name="title" class="input-control" required placeholder="Enter a catchy title...">
        </div>

        <div class="form-group">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <label for="summary" class="mb-0">Short Summary (For SEO, Previews & Social Feeds)</label>
                <button type="button" id="btnAiSummarize" class="btn btn-sm btn-link text-decoration-none p-0 fw-semibold" style="color: var(--brand-secondary, #7a0e16); font-size: 0.88rem;">
                    <i class="fas fa-compress-alt me-1"></i> Auto-Summarize Content
                </button>
            </div>
            <textarea id="summary" name="summary" class="input-control" rows="3" required placeholder="Write a brief summary of what this post is about..."></textarea>
        </div>

        <div class="form-group">
            <label for="cover_image">Cover Image (Optional)</label>
            <input type="file" id="cover_image" name="cover_image" class="input-control" accept="image/png, image/jpeg, image/jpg, image/webp">
        </div>

        <div class="form-group">
            <label for="status">Publication Status</label>
            <select id="status" name="status" class="input-control">
                <option value="draft">Save as Draft</option>
                <option value="published" selected>Publish Immediately</option>
            </select>
        </div>

        <div class="form-group" id="social-media-options">
            <label>Auto-Post to Social Media Upon Publishing</label>
            <div style="display: flex; gap: 20px; align-items: center; margin-top: 8px;">
                <label style="font-weight: normal; margin-bottom: 0; cursor: pointer;">
                    <input type="checkbox" name="post_facebook" value="1" checked> Facebook
                </label>
                <label style="font-weight: normal; margin-bottom: 0; cursor: pointer;">
                    <input type="checkbox" name="post_instagram" value="1" checked> Instagram
                </label>
                <label style="font-weight: normal; margin-bottom: 0; cursor: pointer;">
                    <input type="checkbox" name="post_linkedin" value="1" checked> LinkedIn
                </label>
            </div>
            <small style="color: #666; display: block; margin-top: 5px;">Posts are automatically formatted within a 90-word budget and queued for publishing.</small>
        </div>

        <div class="form-group">
            <label>Blog Content</label>
            <!-- Quill Editor Container -->
            <div id="editor-container"></div>
            <!-- Hidden input to hold the HTML content for form submission -->
            <input type="hidden" name="content" id="content">
        </div>

        <div style="text-align: right;">
            <button type="submit" class="btn-submit" id="btnSubmit">Save & Publish Blog Post</button>
        </div>
    </form>
</div>

<!-- Include the Quill library -->
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

<!-- Initialize Quill editor, AI Story Generation and Form Submission -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Quill
    var quill = new Quill('#editor-container', {
        theme: 'snow',
        placeholder: 'Write your family story here or click "Generate Draft" above to start...',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                ['blockquote', 'code-block'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'color': [] }, { 'background': [] }],
                ['link', 'image', 'video'],
                ['clean']
            ]
        }
    });

    const blogForm = document.getElementById('blogForm');
    const contentInput = document.getElementById('content');
    const btnSubmit = document.getElementById('btnSubmit');
    const titleInput = document.getElementById('title');
    const summaryInput = document.getElementById('summary');

    // AI Generation elements
    const aiTopicInput = document.getElementById('aiTopicInput');
    const aiToneSelect = document.getElementById('aiToneSelect');
    const btnAiGenerate = document.getElementById('btnAiGenerate');
    const btnAiSummarize = document.getElementById('btnAiSummarize');
    const tokenInput = document.querySelector('input[name="token"]');

    // 1. AI Draft Generation Handler
    if (btnAiGenerate && aiTopicInput) {
        btnAiGenerate.addEventListener('click', function() {
            const topic = aiTopicInput.value.trim();
            if (!topic) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Topic Required',
                    text: 'Please enter a story topic or idea before generating a draft.'
                });
                aiTopicInput.focus();
                return;
            }

            const tone = aiToneSelect ? aiToneSelect.value : 'warm, inspiring, and family-focused';
            const csrfToken = tokenInput ? tokenInput.value : '';

            btnAiGenerate.disabled = true;
            btnAiGenerate.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';

            const payload = new FormData();
            payload.append('topic', topic);
            payload.append('tone', tone);
            payload.append('token', csrfToken);

            fetch('/admin/blog/ai-generate', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-XSRF-TOKEN': csrfToken
                },
                body: payload
            })
            .then(res => res.json())
            .then(data => {
                btnAiGenerate.disabled = false;
                btnAiGenerate.innerHTML = '<i class="fas fa-magic"></i> Generate Draft';

                if (data.code === 200 || data.status === 'success') {
                    const draft = data.data || {};
                    if (draft.title && titleInput) {
                        titleInput.value = draft.title;
                    }
                    if (draft.summary && summaryInput) {
                        summaryInput.value = draft.summary;
                    }
                    if (draft.content) {
                        quill.clipboard.dangerouslyPasteHTML(draft.content);
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Draft Generated!',
                        text: 'Your story has been drafted and loaded into the editor. You can now personalize, add images, or refine before publishing.'
                    });
                } else {
                    Swal.fire('Error', data.message || 'Failed to generate story draft.', 'error');
                }
            })
            .catch(err => {
                console.error('AI Draft Error:', err);
                btnAiGenerate.disabled = false;
                btnAiGenerate.innerHTML = '<i class="fas fa-magic"></i> Generate Draft';
                Swal.fire('Error', 'An error occurred while contacting the AI story service.', 'error');
            });
        });
    }

    // 2. AI Auto-Summarize Handler
    if (btnAiSummarize && summaryInput) {
        btnAiSummarize.addEventListener('click', function() {
            const textContent = quill.getText().trim();
            if (textContent.length < 20) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Content Required',
                    text: 'Please write or generate some content in the editor first before summarizing.'
                });
                return;
            }

            const csrfToken = tokenInput ? tokenInput.value : '';
            btnAiSummarize.disabled = true;
            btnAiSummarize.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Summarizing...';

            const payload = new FormData();
            payload.append('content', quill.root.innerHTML);
            payload.append('token', csrfToken);

            fetch('/admin/blog/ai-summarize', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-XSRF-TOKEN': csrfToken
                },
                body: payload
            })
            .then(res => res.json())
            .then(data => {
                btnAiSummarize.disabled = false;
                btnAiSummarize.innerHTML = '<i class="fas fa-compress-alt me-1"></i> Auto-Summarize Content';

                if (data.code === 200 || data.status === 'success') {
                    const summary = (data.data && data.data.summary) ? data.data.summary : (data.summary || '');
                    summaryInput.value = summary;

                    Swal.fire({
                        icon: 'success',
                        title: 'Summary Created!',
                        text: 'SEO summary generated successfully.',
                        timer: 1800,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire('Error', data.message || 'Failed to summarize content.', 'error');
                }
            })
            .catch(err => {
                console.error('AI Summarize Error:', err);
                btnAiSummarize.disabled = false;
                btnAiSummarize.innerHTML = '<i class="fas fa-compress-alt me-1"></i> Auto-Summarize Content';
                Swal.fire('Error', 'An error occurred while generating the summary.', 'error');
            });
        });
    }

    // 3. Form Submission Handler
    blogForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Populate the hidden input with the Quill HTML content
        contentInput.value = quill.root.innerHTML;

        if (quill.getText().trim().length === 0) {
            Swal.fire('Error', 'Blog content cannot be empty.', 'error');
            return;
        }

        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Saving...';

        const formData = new FormData(blogForm);

        fetch('/admin/blog/store', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.code === 200 || data.status === 'success') {
                Swal.fire('Success!', data.message || 'Blog saved successfully.', 'success').then(() => {
                    if (data.data && data.data.slug) {
                        window.location.href = '/blog/' + data.data.slug;
                    } else {
                        window.location.href = '/admin/dashboard';
                    }
                });
            } else {
                Swal.fire('Error', data.message || 'Failed to save blog.', 'error');
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = 'Save & Publish Blog Post';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Error', 'A network error occurred while saving.', 'error');
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = 'Save & Publish Blog Post';
        });
    });
});
</script>
@endsection
