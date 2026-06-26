    @php

        $token = urlencode(base64_encode(random_bytes(32)));
        $_SESSION['token'] = $token;
        setcookie('XSRF-TOKEN', $token, [
            'expires' => time() + 900,
            'path' => '/',
            'samesite' => 'Lax',
            'secure' => ($_ENV['APP_ENV'] ?? 'production') === 'production',
            'httponly' => false,
        ]);

        $_SESSION['EDIT_PROFILE_ID'] = $_SESSION['id'];

    @endphp

    <!-- Edit Profile Modal -->
    <div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content shadow-lg border-0" style="border-radius: 20px; background-color: var(--card-bg);">
                
                <div class="modal-header border-0 pb-0 px-4 pt-4">
                    <h4 class="modal-title fw-bold" id="editProfileModalLabel" style="font-family: 'Playfair Display', serif; color: var(--text-color);">
                        Edit Profile
                    </h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="filter: var(--close-btn-filter);"></button>
                </div>

                <div class="modal-body px-4 pt-4">
                    <form id="editProfileFormModal" class="editProfileFormModal" enctype="multipart/form-data">
                        
                        <div id="setLoader" class="text-center mb-3"></div>
                        <div id="editProfileFormModal_notification"></div>

                        <div class="text-center mb-5 position-relative">
                            <div class="position-relative d-inline-block">
                                @isset($data['profilePics'])
                                    <img src="/resources/images/profile/{{ $data['profilePics'] }}" alt="Profile"
                                        class="shadow-sm" width="130" height="130" id="profilePreview" 
                                        style="border-radius: 50%; border: 4px solid var(--bg-color); object-fit: cover;">
                                    
                                    <label for="img" class="position-absolute bottom-0 end-0 bg-white shadow-sm d-flex align-items-center justify-content-center"
                                        style="width: 38px; height: 38px; border-radius: 50%; cursor: pointer; border: 1px solid var(--border-color);"
                                        title="Change Profile Picture">
                                        <i class="bi bi-camera-fill" style="color: var(--primary-color);"></i>
                                    </label>
                                    <input type="file" class="form-control" id="img" name="img"
                                        style="display: none;" accept="image/*">
                                @endisset
                            </div>
                            <p class="text-muted mt-2 small">Tap the camera to change photo</p>
                        </div>

                        <div class="row g-4 mb-5">
                            <div class="col-12">
                                <h6 class="text-uppercase fw-bold small opacity-75 mb-3 px-1" style="color: var(--primary-color); letter-spacing: 1.5px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">
                                    <i class="bi bi-person-fill me-2"></i>Personal Details
                                </h6>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="firstName" class="form-label small fw-bold px-1" style="color: var(--text-muted);">First Name</label>
                                <input type="text" class="form-control rounded-pill px-4 py-2" id="firstName" name="personal['firstName']" 
                                    value="{{ $data['firstName'] }}" style="background-color: var(--bg-color); border: 1px solid var(--border-color);">
                                <small id="firstName_error" class="text-danger ps-3"></small>
                            </div>

                            <div class="col-md-6">
                                <label for="lastName" class="form-label small fw-bold px-1" style="color: var(--text-muted);">Last Name</label>
                                <input type="text" class="form-control rounded-pill px-4 py-2" id="lastName" name="personal['lastName']" 
                                    value="{{ $data['lastName'] }}" style="background-color: var(--bg-color); border: 1px solid var(--border-color);">
                                <small id="lastName_error" class="text-danger ps-3"></small>
                            </div>

                            <div class="col-md-6">
                                <label for="marital_status" class="form-label small fw-bold px-1" style="color: var(--text-muted);">Relationship Status</label>
                                <select class="form-select rounded-pill px-4 py-2" id="marital_status" name="personal['marital_status']" style="background-color: var(--bg-color); border: 1px solid var(--border-color);">
                                    @foreach (['Single', 'Dating', 'Married', 'Divorced', 'Widowed'] as $option)
                                        <option value="{{ $option }}" {{ $data['marital_status'] === $option ? 'selected' : '' }}>{{ $option }}</option>
                                    @endforeach
                                </select>
                                <small id="marital_status_error" class="text-danger ps-3"></small>
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-12">
                                <h6 class="text-uppercase fw-bold small opacity-75 mb-3 px-1" style="color: var(--primary-color); letter-spacing: 1.5px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">
                                    <i class="bi bi-envelope-fill me-2"></i>Contact Details
                                </h6>
                            </div>

                            <div class="col-md-6">
                                <label for="country" class="form-label small fw-bold px-1" style="color: var(--text-muted);">Country</label>
                                <input type="text" class="form-control rounded-pill px-4 py-2" id="country" name="contact['country']" 
                                    value="{{ $data['country'] }}" style="background-color: var(--bg-color); border: 1px solid var(--border-color);">
                                <small id="country_error" class="text-danger ps-3"></small>
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label small fw-bold px-1" style="color: var(--text-muted);">Email</label>
                                <input type="email" class="form-control rounded-pill px-4 py-2" id="email" name="contact['email']" 
                                    value="{{ $data['email'] }}" style="background-color: var(--bg-color); border: 1px solid var(--border-color);">
                                <small id="email_error" class="text-danger ps-3"></small>
                            </div>

                            <div class="col-md-6">
                                <label for="mobile" class="form-label small fw-bold px-1" style="color: var(--text-muted);">Mobile</label>
                                <input type="text" class="form-control rounded-pill px-4 py-2" id="mobile" name="contact['mobile']" 
                                    value="{{ $data['mobile'] }}" style="background-color: var(--bg-color); border: 1px solid var(--border-color);">
                                <small id="mobile_error" class="text-danger ps-3"></small>
                            </div>
                        </div>

                        <div class="d-flex justify-content-center mt-2">
                            <div class="g-recaptcha" data-sitekey="{{ $_ENV['RECAPTCHA_KEY'] }}" data-theme="light"></div>
                        </div>

                        <input type="hidden" name="token" value="{{ $token }}">

                        <div class="modal-footer border-0 px-4 pb-4 mt-3">
                            <button type="button" class="btn fw-semibold px-4" data-bs-dismiss="modal" style="border-radius: 20px; color: var(--text-muted);">Cancel</button>
                            <button type="button" id="editProfileBtnModal" name="submit" class="btn text-white fw-semibold px-5" 
                                style="background-color: var(--primary-color); border-radius: 20px;">
                                Save Changes
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
