@php $data = $data ?? []; @endphp

    @php
        $token = $_SESSION['token'] ?? '';
        $_SESSION['EDIT_PROFILE_ID'] = $_SESSION['id'];
    @endphp

    <!-- Edit Profile Modal Style overrides -->
    <style>
        #editProfileModal .modal-content {
            border-radius: 20px;
            background-color: var(--card-bg, var(--bg-white, #ffffff));
            color: var(--text-main, #0f172a);
            border: 1px solid var(--border-color, rgba(0, 0, 0, 0.08));
            overflow: hidden;
            box-shadow: 0 12px 36px rgba(0, 0, 0, 0.15);
        }
        #editProfileModal .modal-header {
            border-bottom: 1px solid var(--border-color, rgba(0, 0, 0, 0.08));
            padding: 0.85rem 1.25rem;
            background-color: var(--card-bg, var(--bg-white, #ffffff));
        }
        #editProfileModal .modal-title {
            font-family: var(--font-family), sans-serif;
            font-weight: 700;
            color: var(--text-main, #0f172a);
            font-size: 1.25rem;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        #editProfileModal .modal-title .back-btn {
            color: var(--text-main, #0f172a);
            cursor: pointer;
            text-decoration: none;
            background: none;
            border: none;
            padding: 0;
            display: inline-flex;
            align-items: center;
            font-size: 1.25rem;
        }
        #editProfileModal .modal-body {
            padding: 0.85rem 1rem;
            background-color: var(--card-bg, var(--bg-white, #ffffff));
        }
        #editProfileModal .form-container-box {
            background-color: var(--bg-light, #f8fafc);
            border: 1px solid var(--border-color, rgba(0, 0, 0, 0.06));
            border-radius: 16px;
            padding: 0.85rem;
            margin-top: 0.5rem;
        }
        #editProfileModal .form-label {
            color: var(--text-muted, #64748b);
            font-size: 0.72rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.2rem;
        }
        #editProfileModal .form-control, 
        #editProfileModal .form-select {
            background-color: var(--bg-white, #ffffff);
            border: 1px solid var(--border-color, #cbd5e1);
            color: var(--text-main, #0f172a);
            border-radius: 10px;
            padding: 0.45rem 0.75rem;
            font-size: 0.9rem;
            transition: all 0.25s;
        }
        #editProfileModal .form-control:focus, 
        #editProfileModal .form-select:focus {
            border-color: var(--brand-primary, #00bfa5);
            box-shadow: 0 0 0 3px rgba(0, 191, 165, 0.15);
            background-color: var(--bg-white, #ffffff);
            color: var(--text-main, #0f172a);
            outline: none;
        }
        #editProfileModal select.form-select {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%2364748b' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");
        }
        #editProfileModal .section-title {
            color: var(--text-main, #0f172a);
            font-size: 0.82rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            border-bottom: 1px solid var(--border-color, rgba(0, 0, 0, 0.08));
            padding-bottom: 0.35rem;
            margin-bottom: 0.6rem;
            margin-top: 0.6rem;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        #editProfileModal .section-title:first-child {
            margin-top: 0;
        }
        #editProfileModal .section-title i {
            color: var(--brand-primary, #00bfa5);
            font-size: 1rem;
        }
        #editProfileModal .profile-preview-container {
            width: 88px;
            height: 88px;
            position: relative;
            margin: 0 auto;
        }
        #editProfileModal .profile-preview-img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            border: 2px solid var(--brand-primary, #00bfa5);
            box-shadow: 0 3px 8px rgba(0,0,0,0.12);
            object-fit: cover;
        }
        #editProfileModal .camera-btn {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: var(--brand-primary, #00bfa5);
            border: 2px solid var(--bg-white, #ffffff);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 6px rgba(0,0,0,0.2);
            transition: transform 0.2s;
        }
        #editProfileModal .camera-btn:hover {
            transform: scale(1.1);
        }
        #editProfileModal .camera-btn i {
            color: #ffffff;
            font-size: 0.9rem;
        }
        #editProfileModal .modal-footer {
            border-top: 1px solid var(--border-color, rgba(0, 0, 0, 0.08));
            padding: 0.75rem 1rem calc(0.75rem + env(safe-area-inset-bottom, 0px));
            background-color: var(--card-bg, var(--bg-white, #ffffff));
            display: flex;
            justify-content: space-between;
            gap: 0.75rem;
            position: sticky;
            bottom: 0;
            z-index: 10;
            margin-top: 0.75rem;
        }
        #editProfileModal .btn-cancel {
            border-radius: 50px;
            padding: 0.55rem 1.25rem;
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text-main, #334155);
            border: 1px solid var(--border-color, #cbd5e1);
            background-color: transparent;
            transition: all 0.2s;
            flex: 1;
        }
        #editProfileModal .btn-cancel:hover {
            background-color: var(--bg-light, #f1f5f9);
            color: var(--text-main, #0f172a);
        }
        #editProfileModal .btn-save {
            border-radius: 50px;
            padding: 0.55rem 1.25rem;
            font-size: 0.9rem;
            font-weight: 600;
            color: #ffffff !important;
            background-color: var(--brand-primary, #00bfa5);
            border: none;
            transition: all 0.2s;
            flex: 1;
        }
        #editProfileModal .btn-save:hover {
            background-color: var(--brand-secondary, #004182);
            opacity: 1;
        }
        #editProfileModal .btn-close {
            display: none;
        }
        #editProfileModal .hint-text {
            color: var(--text-muted, #64748b);
            font-size: 0.78rem;
            font-weight: 500;
        }
        #editProfileModal .edit-profile-status:empty,
        #editProfileModal .edit-profile-status > div:empty {
            margin: 0;
        }
        #editProfileFormModal_notification {
            border-radius: 12px;
            padding: 0.65rem 0.85rem;
            font-size: 0.88rem;
            font-weight: 500;
        }
        #editProfileFormModal_notification.alert-danger,
        #editProfileFormModal_notification.bg-danger {
            background-color: #ef4444 !important;
            color: #ffffff !important;
        }
        #editProfileFormModal_notification.alert-success,
        #editProfileFormModal_notification.bg-success {
            background-color: #22c55e !important;
            color: #ffffff !important;
        }

        /* Dark mode support */
        body.dark-mode #editProfileModal .modal-content,
        body.dark-mode #editProfileModal .modal-header,
        body.dark-mode #editProfileModal .modal-body,
        body.dark-mode #editProfileModal .modal-footer {
            background-color: #1e293b !important;
            color: #f1f5f9 !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
        }
        body.dark-mode #editProfileModal .modal-title,
        body.dark-mode #editProfileModal .modal-title .back-btn,
        body.dark-mode #editProfileModal .section-title {
            color: #f8fafc !important;
        }
        body.dark-mode #editProfileModal .form-container-box {
            background-color: #0f172a !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
        }
        body.dark-mode #editProfileModal .form-control,
        body.dark-mode #editProfileModal .form-select {
            background-color: #1e293b !important;
            border-color: #334155 !important;
            color: #f8fafc !important;
        }
        body.dark-mode #editProfileModal .btn-cancel {
            border-color: #334155 !important;
            color: #cbd5e1 !important;
        }
        body.dark-mode #editProfileModal .btn-cancel:hover {
            background-color: #334155 !important;
            color: #ffffff !important;
        }
    </style>

    <!-- Edit Profile Modal -->
    <div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="editProfileModalLabel">
                        <button type="button" class="back-btn" data-bs-dismiss="modal" aria-label="Close">
                            <i class="bi bi-arrow-left"></i>
                        </button>
                        Edit Profile
                    </h5>
                </div>

                <div class="modal-body">

                    {{-- Status area lives outside the form and stays pinned to the top of the
                         scroll area so a save result (spinner / success / error) is always in
                         view, no matter how far the form is scrolled. --}}
                    <div id="editProfileStatus" class="edit-profile-status">
                        <div id="setLoader" class="text-center mb-2"></div>
                        <div id="editProfileFormModal_notification"></div>
                    </div>

                    <form id="editProfileFormModal" class="editProfileFormModal" enctype="multipart/form-data">

                        <!-- Profile Pic Section -->
                        <div class="text-center mb-3">
                            <div class="profile-preview-container">
                                <img src="{{ str_starts_with($data['img'] ?? '', '/') ? $data['img'] : '/resources/images/profile/' . ($data['img'] ?? $data['profilePics'] ?? 'avatarM.png') }}" alt="Profile" class="profile-preview-img" id="profilePreview">
                                <label for="img" class="camera-btn" title="Change Profile Picture">
                                    <i class="bi bi-camera"></i>
                                </label>
                                <input type="file" class="form-control" id="img" name="img" style="display: none;" accept="image/*">
                            </div>
                            <p class="hint-text mt-2 mb-0">Tap the camera to change photo</p>
                        </div>

                        <div class="form-container-box">
                            <!-- Personal Details -->
                            <div class="section-title">
                                <i class="bi bi-person"></i> Personal Details
                            </div>
                            
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label for="firstName" class="form-label">First Name</label>
                                    <input type="text" class="form-control" id="firstName" name="firstName" value="{{ $data['firstName'] ?? '' }}">
                                    <small id="firstName_error" class="text-danger ps-2 small"></small>
                                </div>

                                <div class="col-6">
                                    <label for="lastName" class="form-label">Last Name</label>
                                    <input type="text" class="form-control" id="lastName" name="lastName" value="{{ $data['lastName'] ?? '' }}">
                                    <small id="lastName_error" class="text-danger ps-2 small"></small>
                                </div>

                                <div class="col-12">
                                    <label for="marital_status" class="form-label">Relationship Status</label>
                                    <select class="form-select" id="marital_status" name="marital_status">
                                        @foreach (['Single', 'Dating', 'Married', 'Divorced', 'Widowed'] as $option)
                                            <option value="{{ $option }}" {{ ($data['marital_status'] ?? '') === $option ? 'selected' : '' }}>{{ $option }}</option>
                                        @endforeach
                                    </select>
                                    <small id="marital_status_error" class="text-danger ps-2 small"></small>
                                </div>
                            </div>

                            <!-- Contact Details -->
                            <div class="section-title">
                                <i class="bi bi-envelope"></i> Contact Details
                            </div>

                            <div class="row g-2">
                                <div class="col-6">
                                    <label for="country" class="form-label">Country</label>
                                    <input type="text" class="form-control" id="country" name="country" value="{{ $data['country'] ?? '' }}">
                                    <small id="country_error" class="text-danger ps-2 small"></small>
                                </div>

                                <div class="col-6">
                                    <label for="mobile" class="form-label">Mobile</label>
                                    <input type="text" class="form-control" id="mobile" name="mobile" value="{{ $data['mobile'] ?? '' }}">
                                    <small id="mobile_error" class="text-danger ps-2 small"></small>
                                </div>

                                <div class="col-12">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" value="{{ $data['email'] ?? '' }}">
                                    <small id="email_error" class="text-danger ps-2 small"></small>
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="token" value="{{ $token }}">

                        <div class="modal-footer">
                            <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" id="editProfileBtnModal" name="submit" class="btn btn-save">
                                Save Changes
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
