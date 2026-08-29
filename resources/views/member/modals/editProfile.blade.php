@php $data = $data ?? []; @endphp

    @php
        $token = $_SESSION['token'] ?? '';
        $_SESSION['EDIT_PROFILE_ID'] = $_SESSION['id'];
    @endphp

    <!-- Edit Profile Modal Style overrides -->
    <style>
        #editProfileModal .modal-content {
            border-radius: 24px;
            background-color: #121212;
            border: none;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.6);
        }
        #editProfileModal .modal-header {
            border-bottom: none;
            padding: 1rem 1rem 0.25rem;
            background-color: transparent;
        }
        #editProfileModal .modal-title {
            font-family: var(--font-family), sans-serif;
            font-weight: 600;
            color: #ffffff;
            font-size: 1.5rem;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        #editProfileModal .modal-title .back-btn {
            color: #ffffff;
            cursor: pointer;
            text-decoration: none;
            background: none;
            border: none;
            padding: 0;
            display: inline-flex;
            align-items: center;
            font-size: 1.5rem;
        }
        #editProfileModal .modal-body {
            padding: 1rem;
            background-color: transparent;
        }
        #editProfileModal .form-container-box {
            background-color: #1a1a1a;
            border: 1px solid #333;
            border-radius: 20px;
            padding: 1rem;
            margin-top: 0.5rem;
        }
        #editProfileModal .form-label {
            color: #a1a1aa;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.25rem;
        }
        #editProfileModal .form-control, 
        #editProfileModal .form-select {
            background-color: #27272a;
            border: 1px solid transparent;
            color: #ffffff;
            border-radius: 12px;
            padding: 0.5rem 0.75rem;
            font-size: 0.95rem;
            transition: all 0.25s;
        }
        #editProfileModal .form-control:focus, 
        #editProfileModal .form-select:focus {
            border-color: #4ade80;
            box-shadow: none;
            background-color: #27272a;
            color: #ffffff;
            outline: none;
        }
        #editProfileModal select.form-select {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23a1a1aa' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");
        }
        #editProfileModal .section-title {
            color: #ffffff;
            font-size: 0.9rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 1px solid #333;
            padding-bottom: 0.5rem;
            margin-bottom: 0.8rem;
            margin-top: 1rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        #editProfileModal .section-title:first-child {
            margin-top: 0;
        }
        #editProfileModal .section-title i {
            color: #4ade80;
            font-size: 1.2rem;
        }
        #editProfileModal .profile-preview-container {
            width: 120px;
            height: 120px;
            position: relative;
            margin: 0 auto;
        }
        #editProfileModal .profile-preview-img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            border: 2px solid #333;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
            object-fit: cover;
        }
        #editProfileModal .camera-btn {
            position: absolute;
            bottom: 5px;
            right: 5px;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: #4ade80;
            border: 2px solid #121212;
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
            color: #000000;
            font-size: 1.1rem;
        }
        #editProfileModal .modal-footer {
            border-top: none;
            padding: 1.5rem 0 0;
            background-color: transparent;
            display: flex;
            justify-content: space-between;
            gap: 1rem;
        }
        #editProfileModal .btn-cancel {
            border-radius: 50px;
            padding: 0.8rem 2rem;
            font-size: 1rem;
            font-weight: 600;
            color: #ffffff;
            border: 1px solid #444;
            background-color: transparent;
            transition: all 0.2s;
            flex: 1;
        }
        #editProfileModal .btn-cancel:hover {
            background-color: #27272a;
            color: #ffffff;
        }
        #editProfileModal .btn-save {
            border-radius: 50px;
            padding: 0.8rem 2rem;
            font-size: 1rem;
            font-weight: 600;
            color: #000000 !important;
            background-color: #4ade80;
            border: none;
            transition: all 0.2s;
            flex: 1;
        }
        #editProfileModal .btn-save:hover {
            background-color: #22c55e;
            opacity: 1;
        }
        #editProfileModal .btn-close {
            display: none;
        }
        #editProfileModal .hint-text {
            color: #a1a1aa;
            font-size: 0.85rem;
            font-weight: 500;
        }
        #editProfileModal .modal-body {
            /* modal-dialog-scrollable makes this the scroll container */
            padding: 1.5rem;
        }
        #editProfileModal .edit-profile-status:empty,
        #editProfileModal .edit-profile-status > div:empty {
            margin: 0;
        }
        #editProfileFormModal_notification {
            border-radius: 12px;
            padding: 0.85rem 1rem;
            font-size: 0.9rem;
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
                        <div class="text-center mb-4">
                            <div class="profile-preview-container">
                                <img src="{{ str_starts_with($data['img'] ?? '', '/') ? $data['img'] : '/resources/images/profile/' . ($data['img'] ?? $data['profilePics'] ?? 'avatarM.png') }}" alt="Profile" class="profile-preview-img" id="profilePreview">
                                <label for="img" class="camera-btn" title="Change Profile Picture">
                                    <i class="bi bi-camera"></i>
                                </label>
                                <input type="file" class="form-control" id="img" name="img" style="display: none;" accept="image/*">
                            </div>
                            <p class="hint-text mt-3 mb-0">Tap the camera to change photo</p>
                        </div>

                        <div class="form-container-box">
                            <!-- Personal Details -->
                            <div class="section-title">
                                <i class="bi bi-person"></i> Personal Details
                            </div>
                            
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label for="firstName" class="form-label">First Name</label>
                                    <input type="text" class="form-control" id="firstName" name="firstName" value="{{ $data['firstName'] ?? '' }}">
                                    <small id="firstName_error" class="text-danger ps-2 small"></small>
                                </div>

                                <div class="col-md-6">
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

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="country" class="form-label">Country</label>
                                    <input type="text" class="form-control" id="country" name="country" value="{{ $data['country'] ?? '' }}">
                                    <small id="country_error" class="text-danger ps-2 small"></small>
                                </div>

                                <div class="col-md-6">
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
