    @php
        $token = $_SESSION['token'] ?? '';
        $_SESSION['EDIT_PROFILE_ID'] = $_SESSION['id'];
    @endphp

    <!-- Edit Profile Modal Style overrides -->
    <style>
        #editProfileModal .modal-content {
            border-radius: 16px;
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        #editProfileModal .modal-header {
            border-bottom: 1px solid var(--border-color);
            padding: 1.25rem 1.5rem;
            background-color: var(--card-bg);
        }
        #editProfileModal .modal-title {
            font-family: var(--font-family), sans-serif;
            font-weight: 700;
            color: var(--text-color);
            font-size: 1.25rem;
            margin: 0;
        }
        #editProfileModal .modal-body {
            padding: 1.5rem;
            background-color: var(--card-bg);
        }
        #editProfileModal .form-label {
            color: var(--text-muted);
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.35rem;
        }
        #editProfileModal .form-control, 
        #editProfileModal .form-select {
            background-color: var(--bg-color);
            border: 1px solid var(--border-color);
            color: var(--text-color);
            border-radius: 8px;
            padding: 0.55rem 0.9rem;
            font-size: 0.95rem;
            transition: border-color 0.25s, box-shadow 0.25s, background-color 0.25s;
        }
        #editProfileModal .form-control:focus, 
        #editProfileModal .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(30, 96, 64, 0.15);
            background-color: var(--bg-color);
            color: var(--text-color);
            outline: none;
        }
        #editProfileModal .section-title {
            color: var(--primary-color);
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 0.4rem;
            margin-bottom: 1rem;
            margin-top: 0.5rem;
        }
        #editProfileModal .profile-preview-container {
            width: 100px;
            height: 100px;
            position: relative;
            margin: 0 auto;
        }
        #editProfileModal .profile-preview-img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            border: 3px solid var(--card-bg);
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            object-fit: cover;
        }
        #editProfileModal .camera-btn {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
            transition: transform 0.2s, background-color 0.2s;
        }
        #editProfileModal .camera-btn:hover {
            transform: scale(1.1);
            background-color: var(--hover-color);
        }
        #editProfileModal .camera-btn i {
            color: var(--primary-color);
            font-size: 0.9rem;
        }
        #editProfileModal .modal-footer {
            border-top: 1px solid var(--border-color);
            padding: 1rem 1.5rem;
            background-color: var(--card-bg);
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
        }
        #editProfileModal .btn-cancel {
            border-radius: 8px;
            padding: 0.6rem 1.5rem;
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--text-muted);
            border: 1px solid transparent;
            background-color: transparent;
            transition: background-color 0.2s, color 0.2s;
        }
        #editProfileModal .btn-cancel:hover {
            background-color: var(--hover-color);
            color: var(--text-color);
        }
        #editProfileModal .btn-save {
            border-radius: 8px;
            padding: 0.6rem 2rem;
            font-size: 0.95rem;
            font-weight: 600;
            color: #ffffff;
            background-color: var(--primary-color);
            border: 1px solid transparent;
            transition: opacity 0.2s, background-color 0.2s;
        }
        #editProfileModal .btn-save:hover {
            background-color: var(--primary-color);
            opacity: 0.9;
        }
    </style>

    <!-- Edit Profile Modal -->
    <div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0">
                
                <div class="modal-header">
                    <h5 class="modal-title" id="editProfileModalLabel">
                        Edit Profile
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="filter: var(--close-btn-filter);"></button>
                </div>

                <div class="modal-body">
                    <form id="editProfileFormModal" class="editProfileFormModal" enctype="multipart/form-data">
                        
                        <div id="setLoader" class="text-center mb-2"></div>
                        <div id="editProfileFormModal_notification"></div>

                        <!-- Profile Pic Section -->
                        <div class="text-center mb-4">
                            <div class="profile-preview-container">
                                @if(isset($data['img']))
    <img src="{{ url('public/img/profile/' . $data['img']) }}" alt="Profile"
        class="profile-preview-img" id="profilePreview">
@elseif(isset($data['profilePics']))
    <img src="{{ url('public/img/profile/' . $data['profilePics']) }}" alt="Profile"
        class="profile-preview-img" id="profilePreview">
@else
    <img src="{{ url('public/img/profile/avatarM.png') }}" alt="Profile"
        class="profile-preview-img" id="profilePreview">
@endif
                                <label for="img" class="camera-btn" title="Change Profile Picture">
                                    <i class="bi bi-camera-fill"></i>
                                </label>
                                <input type="file" class="form-control" id="img" name="img"
                                    style="display: none;" accept="image/*">
                            </div>
                            <p class="text-muted mt-2 mb-0 small">Tap the camera to change photo</p>
                        </div>

                        <!-- Personal Details -->
                        <div class="section-title">
                            <i class="bi bi-person-fill me-1"></i> Personal Details
                        </div>
                        
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="firstName" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="firstName" name="firstName" 
                                    value="{{ $data['firstName'] }}">
                                <small id="firstName_error" class="text-danger ps-2 small"></small>
                            </div>

                            <div class="col-md-6">
                                <label for="lastName" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="lastName" name="lastName" 
                                    value="{{ $data['lastName'] }}">
                                <small id="lastName_error" class="text-danger ps-2 small"></small>
                            </div>

                            <div class="col-12">
                                <label for="marital_status" class="form-label">Relationship Status</label>
                                <select class="form-select" id="marital_status" name="marital_status">
                                    @foreach (['Single', 'Dating', 'Married', 'Divorced', 'Widowed'] as $option)
                                        <option value="{{ $option }}" {{ $data['marital_status'] === $option ? 'selected' : '' }}>{{ $option }}</option>
                                    @endforeach
                                </select>
                                <small id="marital_status_error" class="text-danger ps-2 small"></small>
                            </div>
                        </div>

                        <!-- Contact Details -->
                        <div class="section-title">
                            <i class="bi bi-envelope-fill me-1"></i> Contact Details
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="country" class="form-label">Country</label>
                                <input type="text" class="form-control" id="country" name="country" 
                                    value="{{ $data['country'] }}">
                                <small id="country_error" class="text-danger ps-2 small"></small>
                            </div>

                            <div class="col-md-6">
                                <label for="mobile" class="form-label">Mobile</label>
                                <input type="text" class="form-control" id="mobile" name="mobile" 
                                    value="{{ $data['mobile'] }}">
                                <small id="mobile_error" class="text-danger ps-2 small"></small>
                            </div>

                            <div class="col-12">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                    value="{{ $data['email'] }}">
                                <small id="email_error" class="text-danger ps-2 small"></small>
                            </div>
                        </div>

                        <input type="hidden" name="token" value="{{ $token }}">

                        <div class="modal-footer border-0 px-0 pb-0 mt-4">
                            <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" id="editProfileBtnModal" name="submit" class="btn btn-save text-white">
                                Save Changes
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
