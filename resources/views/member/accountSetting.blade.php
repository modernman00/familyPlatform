@extends('layouts.profileBase')
@section('title', 'ACCOUNT SETTING')
@section('data-page-id', 'AccountSettingPage')

@push('styles')
  <style>
    /* Modern Account Settings Structure */
    body {
      background-color: #f8f9fc;
      background-image: radial-gradient(at top left, rgba(255, 235, 240, 0.5) 0%, transparent 40%);
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    }

    .page-header {
      margin-top: 40px;
      margin-bottom: 40px;
    }

    .page-title {
      font-size: 1.8rem;
      font-weight: 800;
      color: #1e293b;
      margin-bottom: 0.5rem;
    }

    .page-subtitle {
      color: #64748b;
      font-size: 0.95rem;
    }

    /* Sidebar Navigation */
    .sidebar-card {
      background: #ffffff;
      border: none;
      border-radius: 12px;
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
      padding: 10px 0;
    }

    .nav-pills .nav-link {
      color: #64748b;
      font-weight: 600;
      padding: 12px 20px;
      border-radius: 0;
      margin-bottom: 2px;
      display: flex;
      align-items: center;
      gap: 12px;
      transition: all 0.2s;
      border-left: 3px solid transparent;
    }

    .nav-pills .nav-link:hover {
      background-color: #f8fafc;
      color: #475569;
    }

    .nav-pills .nav-link.active {
      background-color: #eff6ff;
      color: #4f46e5;
      border-left: 3px solid #4f46e5;
    }

    .nav-pills .nav-link i {
      font-size: 1.1rem;
    }

    /* Content Area */
    .content-card {
      background: #ffffff;
      border: none;
      border-radius: 12px;
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
      padding: 35px;
      min-height: 500px;
    }

    .section-title {
      font-size: 1.25rem;
      font-weight: 700;
      color: #1e293b;
      margin-bottom: 0.25rem;
    }

    .section-subtitle {
      color: #64748b;
      font-size: 0.9rem;
      margin-bottom: 20px;
    }

    .section-divider {
      height: 1px;
      background-color: #e2e8f0;
      margin-bottom: 30px;
    }

    /* Form Inputs */
    .form-label {
      font-weight: 600;
      color: #334155;
      font-size: 0.85rem;
      margin-bottom: 0.4rem;
    }

    .form-control, .form-select {
      border: 1px solid #cbd5e1;
      border-radius: 8px;
      padding: 10px 14px;
      font-size: 0.95rem;
      color: #1e293b;
      background-color: #ffffff;
      transition: all 0.2s;
    }

    .form-control:focus, .form-select:focus {
      border-color: #4f46e5;
      box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
      outline: none;
    }

    .form-control[readonly] {
      background-color: #f8fafc;
      color: #64748b;
    }

    .password-wrapper {
      position: relative;
    }

    .password-wrapper .eye-icon {
      position: absolute;
      right: 15px;
      top: 50%;
      transform: translateY(-50%);
      color: #64748b;
      cursor: pointer;
    }

    /* Custom Toggle Switch */
    .custom-switch {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 20px;
      background-color: #f8fafc;
      border-radius: 8px;
      border: 1px solid #e2e8f0;
      margin-bottom: 20px;
    }

    .switch-info h6 {
      margin-bottom: 0.25rem;
      font-weight: 600;
      color: #1e293b;
    }

    .switch-info p {
      margin-bottom: 0;
      font-size: 0.85rem;
      color: #64748b;
    }

    .toggle-group {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .toggle-label {
      font-size: 0.75rem;
      font-weight: 700;
      color: #64748b;
    }

    /* The switch - the box around the slider */
    .switch {
      position: relative;
      display: inline-block;
      width: 44px;
      height: 24px;
    }

    /* Hide default HTML checkbox */
    .switch input {
      opacity: 0;
      width: 0;
      height: 0;
    }

    /* The slider */
    .slider {
      position: absolute;
      cursor: pointer;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-color: #cbd5e1;
      transition: .4s;
      border-radius: 34px;
    }

    .slider:before {
      position: absolute;
      content: "";
      height: 18px;
      width: 18px;
      left: 3px;
      bottom: 3px;
      background-color: white;
      transition: .4s;
      border-radius: 50%;
    }

    input:checked + .slider {
      background-color: #4f46e5;
    }

    input:checked + .slider:before {
      transform: translateX(20px);
    }

    /* Buttons */
    .btn-save {
      background-color: #4f46e5;
      color: #ffffff;
      font-weight: 600;
      padding: 12px 24px;
      border-radius: 8px;
      border: none;
      width: 100%;
      margin-top: 20px;
      transition: background-color 0.2s;
    }

    .btn-save:hover {
      background-color: #4338ca;
      color: white;
    }

    /* Divider for old settings */
    .legacy-settings-header {
      margin-top: 40px;
      margin-bottom: 20px;
      padding-top: 30px;
      border-top: 1px dashed #e2e8f0;
    }

  </style>
@endpush

@section('content')
  @php
    $fullName = $_SESSION['fullName'] ?? ' ';
    $nameParts = explode(' ', $fullName, 2);
    $firstName = $nameParts[0] ?? '';
    $lastName = $nameParts[1] ?? '';
  @endphp

  <div class="container py-4">
    <!-- Header -->
    <div class="row text-center page-header">
      <div class="col-12">
        <h1 class="page-title">Account Settings</h1>
        <p class="page-subtitle">Manage your profile, preferences, and security settings.</p>
      </div>
    </div>

    <div class="row gx-4">
      <!-- Sidebar Navigation -->
      <div class="col-lg-3 mb-4">
        <div class="sidebar-card">
          <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
            <button class="nav-link active" id="v-pills-profile-tab" data-bs-toggle="pill" data-bs-target="#v-pills-profile" type="button" role="tab" aria-controls="v-pills-profile" aria-selected="true">
              <i class="bi bi-person-fill"></i> Profile
            </button>
            <button class="nav-link" id="v-pills-password-tab" data-bs-toggle="pill" data-bs-target="#v-pills-password" type="button" role="tab" aria-controls="v-pills-password" aria-selected="false">
              <i class="bi bi-lock-fill"></i> Password
            </button>
            <button class="nav-link" id="v-pills-preferences-tab" data-bs-toggle="pill" data-bs-target="#v-pills-preferences" type="button" role="tab" aria-controls="v-pills-preferences" aria-selected="false">
              <i class="bi bi-bell-fill"></i> Preferences
            </button>
            <button class="nav-link" id="v-pills-privacy-tab" data-bs-toggle="pill" data-bs-target="#v-pills-privacy" type="button" role="tab" aria-controls="v-pills-privacy" aria-selected="false">
              <i class="bi bi-shield-fill-check"></i> Privacy
            </button>
          </div>
        </div>
      </div>

      <!-- Main Content Area -->
      <div class="col-lg-9">
        <div class="content-card">
          <div class="tab-content" id="v-pills-tabContent">
            
            <!-- PROFILE TAB -->
            <div class="tab-pane fade show active" id="v-pills-profile" role="tabpanel" aria-labelledby="v-pills-profile-tab">
              <div class="section-title">Profile Information</div>
              <div class="section-subtitle">Update your personal details here.</div>
              <div class="section-divider"></div>

              <div id="setLoader" class="text-center my-4" style="display: none;">
                <div class="spinner-border text-primary" role="status">
                  <span class="visually-hidden">Loading...</span>
                </div>
              </div>

              <div class="alert alert-danger shadow-sm border-0 rounded-3" id="accountSettingForm_notification" style="display: none;">
                <div class="d-flex align-items-center">
                  <i class="bi bi-exclamation-octagon-fill fs-4 me-3"></i>
                  <p id="accountSettingForm_notification_error" class="mb-0"></p>
                </div>
              </div>

              <!-- Important: Existing form ID is required by accountSetting.js -->
              <form class="accountSettingForm" id="accountSettingForm" enctype="multipart/form-data">
                
                <div class="row g-4 mb-4">
                  <div class="col-md-6">
                    <label class="form-label">First Name</label>
                    <input type="text" class="form-control" value="{{ $firstName }}" readonly>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Last Name</label>
                    <input type="text" class="form-control" value="{{ $lastName }}" readonly>
                  </div>
                </div>

                <div class="row g-4 mb-4">
                  <div class="col-md-6">
                    <label class="form-label">Email Address (Read-only)</label>
                    <input type="email" class="form-control" id="email" name="email" value="{{ $accountData['email'] }}" readonly>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Mobile Number</label>
                    <input type="text" class="form-control" id="mobile" name="mobile" value="{{ $accountData['mobile'] }}">
                  </div>
                </div>

                <!-- Legacy Form Data section to prevent data loss -->
                <div class="legacy-settings-header">
                  <div class="section-title" style="font-size: 1.1rem;">Family & Regional Settings</div>
                  <div class="section-subtitle mb-0">Configure your family tree connections.</div>
                </div>

                <div class="row g-4 mb-4 mt-1">
                  <div class="col-md-6">
                    <label class="form-label">Country</label>
                    <input type="text" class="form-control" id="country" name="country" value="{{ $accountData['country'] }}">
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Occupation</label>
                    <input type="text" class="form-control" id="occupation" name="occupation" value="{{ $accountData['occupation'] }}">
                  </div>
                </div>

                <div class="row g-4 mb-4">
                  <div class="col-md-6">
                    <label for="children" class="form-label">Add Children</label>
                    <select class="form-select" id="children" name="children">
                      <option value="0" selected>0 Children</option>
                      @for ($i = 1; $i <= 10; $i++)
                        <option value="{{ $i }}">{{ $i }} {{ $i === 1 ? 'Child' : 'Children' }}</option>
                      @endfor
                    </select>
                    <div id="children_help" class="form-text text-danger mt-2 fw-bold small"></div>
                    <div id="children_div" class="mt-2"></div>
                  </div>

                  <div class="col-md-6">
                    <label for="sibling" class="form-label">Add Siblings</label>
                    <select class="form-select" id="sibling" name="sibling">
                      <option value="0" selected>0 Siblings</option>
                      @for ($i = 1; $i <= 10; $i++)
                        <option value="{{ $i }}">{{ $i }} {{ $i === 1 ? 'Sibling' : 'Siblings' }}</option>
                      @endfor
                    </select>
                    <div id="sibling_help" class="form-text text-danger mt-2 fw-bold small"></div>
                    <div id="sibling_div" class="mt-2"></div>
                  </div>
                </div>

                <div class="mb-4">
                  <label for="maritalStatus" class="form-label">Marital Status</label>
                  <select class="form-select" id="maritalStatus" name="maritalStatus">
                    <option disabled selected>Select status...</option>
                    <option value="Yes - Add Husband">Yes - Add Husband</option>
                    <option value="Yes - Add Wife">Yes - Add Wife</option>
                  </select>
                </div>

                <!-- Dynamic Spouse Section -->
                <div id="spouse" style="display: none;" class="p-4 rounded-3 mb-4" style="background-color: #f8fafc; border: 1px solid #e2e8f0;">
                  <h6 class="fw-bold mb-3 text-dark">Spouse Details</h6>
                  <div class="row g-3">
                    <div class="col-md-6">
                      <label class="form-label">Spouse's Name</label>
                      <input type="text" class="form-control" id="spouse_name" name="spouse_name" placeholder="Enter name">
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">Spouse's Email</label>
                      <input type="email" class="form-control" id="spouse_email" name="spouse_email" placeholder="Enter email">
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">Spouse's Mobile</label>
                      <input type="text" class="form-control" id="spouse_mobile" name="spouse_mobile" placeholder="Enter mobile number">
                    </div>
                    <div class="col-md-6" id="maiden_name_div" style="display: none;">
                      <label class="form-label">Maiden Name</label>
                      <input type="text" class="form-control" id="maiden_name" name="maiden_name" placeholder="Enter maiden name">
                    </div>
                  </div>
                </div>

                <input type="hidden" name="token" value="{{ $_SESSION['token'] ?? '' }}">
                <span id="fName" class="d-none">{{ $_SESSION['fullName'] }}</span>
                
                <button type="button" id="button" class="btn-save">Save Changes</button>
              </form>
            </div>

            <!-- PASSWORD TAB -->
            <div class="tab-pane fade" id="v-pills-password" role="tabpanel" aria-labelledby="v-pills-password-tab">
              <div class="section-title">Change Password</div>
              <div class="section-subtitle">Ensure your account is using a long, random password to stay secure.</div>
              <div class="section-divider"></div>

              <form id="passwordForm">
                <input type="hidden" name="action" value="updatePassword">
                <input type="hidden" name="token" value="{{ $_SESSION['token'] ?? '' }}">

                <div class="mb-4">
                  <label class="form-label">Current Password</label>
                  <div class="password-wrapper">
                    <input type="password" name="current_password" class="form-control">
                    <i class="bi bi-eye eye-icon"></i>
                  </div>
                </div>

                <div class="mb-4">
                  <label class="form-label">New Password</label>
                  <div class="password-wrapper">
                    <input type="password" name="new_password" class="form-control">
                    <i class="bi bi-eye eye-icon"></i>
                  </div>
                </div>

                <div class="mb-4">
                  <label class="form-label">Confirm New Password</label>
                  <div class="password-wrapper">
                    <input type="password" name="confirm_password" class="form-control">
                    <i class="bi bi-eye eye-icon"></i>
                  </div>
                </div>

                <button type="button" id="passwordBtn" class="btn-save">Save Password</button>
              </form>
            </div>

            <!-- PREFERENCES TAB -->
            <div class="tab-pane fade" id="v-pills-preferences" role="tabpanel" aria-labelledby="v-pills-preferences-tab">
              <div class="section-title">Notification Preferences</div>
              <div class="section-subtitle">Choose what notifications you want to receive.</div>
              <div class="section-divider"></div>

              <form id="preferencesForm">
                <input type="hidden" name="action" value="updatePreferences">
                <input type="hidden" name="token" value="{{ $_SESSION['token'] ?? '' }}">

                <div class="custom-switch">
                  <div class="switch-info">
                    <h6>Email Notifications</h6>
                    <p>Receive alerts about events and activities via email.</p>
                  </div>
                  <div class="toggle-group">
                    <span class="toggle-label">{{ ($accountData['email_notifications'] ?? '') === 'on' ? 'ON' : 'OFF' }}</span>
                    <label class="switch">
                      <input type="checkbox" name="email_notifications" {{ ($accountData['email_notifications'] ?? '') === 'on' ? 'checked' : '' }}>
                      <span class="slider"></span>
                    </label>
                  </div>
                </div>

                <div class="custom-switch">
                  <div class="switch-info">
                    <h6>SMS Notifications</h6>
                    <p>Receive critical alerts directly to your phone.</p>
                  </div>
                  <div class="toggle-group">
                    <span class="toggle-label">{{ ($accountData['sms_notifications'] ?? '') === 'on' ? 'ON' : 'OFF' }}</span>
                    <label class="switch">
                      <input type="checkbox" name="sms_notifications" {{ ($accountData['sms_notifications'] ?? '') === 'on' ? 'checked' : '' }}>
                      <span class="slider"></span>
                    </label>
                  </div>
                </div>

                <button type="button" id="preferencesBtn" class="btn-save mt-3">Save Preferences</button>
              </form>
            </div>

            <!-- PRIVACY TAB -->
            <div class="tab-pane fade" id="v-pills-privacy" role="tabpanel" aria-labelledby="v-pills-privacy-tab">
              <div class="section-title">Privacy & Security</div>
              <div class="section-subtitle">Control your data sharing and security layers.</div>
              <div class="section-divider"></div>

              <form id="privacyForm">
                <input type="hidden" name="action" value="updatePrivacy">
                <input type="hidden" name="token" value="{{ $_SESSION['token'] ?? '' }}">

                <div class="custom-switch">
                  <div class="switch-info">
                    <h6>Two-Factor Authentication (2FA)</h6>
                    <p>Add an extra layer of security to your account.</p>
                  </div>
                  <label class="switch">
                    <input type="checkbox" name="two_factor_auth" {{ ($accountData['two_factor_auth'] ?? '') === 'on' ? 'checked' : '' }}>
                    <span class="slider"></span>
                  </label>
                </div>

                <div class="mb-4 mt-4">
                  <label class="form-label fw-bold">Profile Visibility</label>
                  <select class="form-select" name="profile_visibility" style="background-color: #ffffff;">
                    <option value="Private" {{ ($accountData['profile_visibility'] ?? 'Private') === 'Private' ? 'selected' : '' }}>Private</option>
                    <option value="Public" {{ ($accountData['profile_visibility'] ?? '') === 'Public' ? 'selected' : '' }}>Public</option>
                    <option value="Family Only" {{ ($accountData['profile_visibility'] ?? '') === 'Family Only' ? 'selected' : '' }}>Family Only</option>
                  </select>
                </div>

                <div class="custom-switch">
                  <div class="switch-info">
                    <h6>Show My Profile</h6>
                    <p>Make your profile visible to other attendees.</p>
                  </div>
                  <div class="toggle-group">
                    <span class="toggle-label">{{ ($accountData['show_my_profile'] ?? '') === 'on' ? 'ON' : 'OFF' }}</span>
                    <label class="switch">
                      <input type="checkbox" name="show_my_profile" {{ ($accountData['show_my_profile'] ?? '') === 'on' ? 'checked' : '' }}>
                      <span class="slider"></span>
                    </label>
                  </div>
                </div>

                <div class="custom-switch">
                  <div class="switch-info">
                    <h6>Data Sharing</h6>
                    <p>Share anonymous usage data to improve the experience.</p>
                  </div>
                  <div class="toggle-group">
                    <span class="toggle-label">{{ ($accountData['data_sharing'] ?? '') === 'on' ? 'ON' : 'OFF' }}</span>
                    <label class="switch">
                      <input type="checkbox" name="data_sharing" {{ ($accountData['data_sharing'] ?? '') === 'on' ? 'checked' : '' }}>
                      <span class="slider"></span>
                    </label>
                  </div>
                </div>

                <button type="button" id="privacyBtn" class="btn-save mt-3">Save Privacy Settings</button>
              </form>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>
@endsection