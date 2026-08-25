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
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    max-width: 900px;
    margin: 0 auto;
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
    border-radius: 6px;
    font-size: 1rem;
}
.input-control:focus {
    border-color: #4A90E2;
    outline: none;
    box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.2);
}
#editor-container {
    height: 400px;
    border-radius: 0 0 6px 6px;
    font-size: 1.1rem;
}
.ql-toolbar {
    border-radius: 6px 6px 0 0;
    background: #f9f9f9;
}
.btn-submit {
    background: #4A90E2;
    color: white;
    border: none;
    padding: 12px 32px;
    font-size: 1.1rem;
    font-weight: 600;
    border-radius: 6px;
    cursor: pointer;
    transition: background 0.3s;
}
.btn-submit:hover {
    background: #357ABD;
}
</style>

<div class="blog-editor-container">
    <form id="blogForm" action="/admin/blog/store" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="title">Blog Title</label>
            <input type="text" id="title" name="title" class="input-control" required placeholder="Enter a catchy title...">
        </div>

        <div class="form-group">
            <label for="summary">Short Summary (For SEO & Previews)</label>
            <textarea id="summary" name="summary" class="input-control" rows="3" required placeholder="Write a brief summary of what this post is about..."></textarea>
        </div>

        <div class="form-group">
            <label for="cover_image">Cover Image (Optional)</label>
            <input type="file" id="cover_image" name="cover_image" class="input-control" accept="image/png, image/jpeg, image/jpg, image/webp">
        </div>

        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status" class="input-control">
                <option value="draft">Save as Draft</option>
                <option value="published">Publish Immediately</option>
            </select>
        </div>

        <div class="form-group" id="social-media-options">
            <label>Auto-Post to Social Media</label>
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
            <small style="color: #666; display: block; margin-top: 5px;">Posts will be queued and published automatically when the status is set to Published.</small>
        </div>

        <div class="form-group">
            <label>Blog Content</label>
            <!-- Quill Editor Container -->
            <div id="editor-container"></div>
            <!-- Hidden input to hold the HTML content for form submission -->
            <input type="hidden" name="content" id="content">
        </div>

        <div style="text-align: right;">
            <button type="submit" class="btn-submit" id="btnSubmit">Save Blog Post</button>
        </div>
    </form>
</div>

<!-- Include the Quill library -->
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

<!-- Initialize Quill editor and Form Submission -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Quill
    var quill = new Quill('#editor-container', {
        theme: 'snow',
        placeholder: 'Write your amazing story here...',
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

    blogForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Populate the hidden input with the Quill HTML content
        contentInput.value = quill.root.innerHTML;

        if (quill.getText().trim().length === 0) {
            Swal.fire('Error', 'Blog content cannot be empty.', 'error');
            return;
        }

        btnSubmit.disabled = true;
        btnSubmit.innerHTML = 'Saving...';

        const formData = new FormData(blogForm);

        fetch('/admin/blog/store', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
                // CSRF Token if required by framework
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.code === 200 || data.status === 'success') {
                Swal.fire('Success!', data.message || 'Blog saved successfully.', 'success').then(() => {
                    // Redirect to the newly created blog post
                    if (data.data && data.data.slug) {
                        window.location.href = '/blog/' + data.data.slug;
                    } else {
                        window.location.href = '/admin/dashboard';
                    }
                });
            } else {
                Swal.fire('Error', data.message || 'Failed to save blog.', 'error');
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = 'Save Blog Post';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Error', 'A network error occurred while saving.', 'error');
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = 'Save Blog Post';
        });
    });
});
</script>
@endsection
