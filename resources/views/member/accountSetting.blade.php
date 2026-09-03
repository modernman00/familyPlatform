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
          <div class="px-3 pt-2 pb-1 text-uppercase text-muted fw-bold" style="font-size: 0.72rem; letter-spacing: 0.06em;">
            Account & Security
          </div>
          <div class="nav flex-column nav-pills mb-3" id="v-pills-account-tab" role="tablist" aria-orientation="vertical">
            <button class="nav-link active" id="v-pills-profile-tab" data-bs-toggle="pill" data-bs-target="#v-pills-profile" type="button" role="tab" aria-controls="v-pills-profile" aria-selected="true">
              <i class="bi bi-person-fill"></i> Profile & Work
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

          <div class="px-3 pt-3 pb-1 text-uppercase text-muted fw-bold" style="font-size: 0.72rem; letter-spacing: 0.06em; border-top: 1px solid #f1f5f9;">
            Family Connections
          </div>
          <div class="nav flex-column nav-pills" id="v-pills-family-tab" role="tablist" aria-orientation="vertical">
            <button class="nav-link" id="v-pills-parents-tab" data-bs-toggle="pill" data-bs-target="#v-pills-parents" type="button" role="tab" aria-controls="v-pills-parents" aria-selected="false">
              <i class="bi bi-people-fill"></i> Parents
            </button>
            <button class="nav-link" id="v-pills-children-tab" data-bs-toggle="pill" data-bs-target="#v-pills-children" type="button" role="tab" aria-controls="v-pills-children" aria-selected="false">
              <i class="bi bi-person-heart"></i> Children
            </button>
            <button class="nav-link" id="v-pills-siblings-tab" data-bs-toggle="pill" data-bs-target="#v-pills-siblings" type="button" role="tab" aria-controls="v-pills-siblings" aria-selected="false">
              <i class="bi bi-person-lines-fill"></i> Siblings
            </button>
            <button class="nav-link" id="v-pills-marital-tab" data-bs-toggle="pill" data-bs-target="#v-pills-marital" type="button" role="tab" aria-controls="v-pills-marital" aria-selected="false">
              <i class="bi bi-heart-fill"></i> Marital Status
            </button>
          </div>
        </div>
      </div>

      <!-- Main Content Area -->
      <div class="col-lg-9">
        <div class="content-card">
          <div class="tab-content" id="v-pills-tabContent">
            
            <!-- PROFILE & WORK TAB -->
            <div class="tab-pane fade show active" id="v-pills-profile" role="tabpanel" aria-labelledby="v-pills-profile-tab">
              <div class="section-title">Profile & Work Information</div>
              <div class="section-subtitle">Update your personal contact and professional details.</div>
              <div class="section-divider"></div>

              <div id="setLoader" class="text-center my-4" style="display: none;">
                <div class="spinner-border text-primary" role="status">
                  <span class="visually-hidden">Loading...</span>
                </div>
              </div>

              <div class="alert alert-danger shadow-sm border-0 rounded-3" id="profileForm_notification" style="display: none;">
                <div class="d-flex align-items-center">
                  <i class="bi bi-exclamation-octagon-fill fs-4 me-3"></i>
                  <p id="profileForm_notification_error" class="mb-0"></p>
                </div>
              </div>

              <form class="accountSettingForm" id="profileForm" enctype="multipart/form-data">
                <input type="hidden" name="action" value="updateProfile">
                
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

                <div class="row g-4 mb-4">
                  <div class="col-md-12">
                    <label class="form-label">Country</label>
                    <input type="text" class="form-control" id="country" name="country" value="{{ $accountData['country'] ?? '' }}" placeholder="Enter country of residence">
                  </div>
                </div>

                <div class="legacy-settings-header mt-4 pt-3">
                  <div class="section-title" style="font-size: 1.1rem;">Work Information</div>
                  <div class="section-subtitle mb-0">Update your professional details.</div>
                </div>

                <div class="row g-4 mb-4 mt-1">
                  <div class="col-md-6">
                    <label class="form-label">Employment Status</label>
                    <select class="form-select" id="employmentStatus" name="employmentStatus">
                      <option disabled {{ empty($accountData['employmentStatus']) ? 'selected' : '' }}>Select status...</option>
                      <option value="Employed" {{ ($accountData['employmentStatus'] ?? '') === 'Employed' ? 'selected' : '' }}>Employed</option>
                      <option value="Self-Employed" {{ ($accountData['employmentStatus'] ?? '') === 'Self-Employed' ? 'selected' : '' }}>Self-Employed</option>
                      <option value="Unemployed" {{ ($accountData['employmentStatus'] ?? '') === 'Unemployed' ? 'selected' : '' }}>Unemployed</option>
                      <option value="Student" {{ ($accountData['employmentStatus'] ?? '') === 'Student' ? 'selected' : '' }}>Student</option>
                      <option value="Retired" {{ ($accountData['employmentStatus'] ?? '') === 'Retired' ? 'selected' : '' }}>Retired</option>
                    </select>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Occupation</label>
                    <input type="text" class="form-control" id="occupation" name="occupation" value="{{ $accountData['occupation'] ?? '' }}" placeholder="e.g. Software Engineer, Doctor">
                  </div>
                </div>

                <input type="hidden" name="token" value="{{ $_SESSION['token'] ?? '' }}">
                <span id="fName" class="d-none">{{ $_SESSION['fullName'] ?? '' }}</span>
                
                <button type="button" id="profileBtn" class="btn-save">Save Profile Details</button>
              </form>
            </div>

            <!-- PARENTS TAB -->
            <div class="tab-pane fade" id="v-pills-parents" role="tabpanel" aria-labelledby="v-pills-parents-tab">
              <div class="section-title" id="family-settings">Parents Information</div>
              <div class="section-subtitle">Configure your father and mother details for your family tree connections.</div>
              <div class="section-divider"></div>

              <div class="alert alert-primary shadow-sm border-0 rounded-3 mb-4 d-flex flex-column flex-md-row align-items-center justify-content-between p-3 gap-2" style="background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);">
                <div class="d-flex align-items-center">
                  <i class="bi bi-stars fs-3 text-primary me-3"></i>
                  <div>
                    <div class="fw-bold text-dark">Dedicated Family Lineage Studio</div>
                    <div class="small text-muted">Use our world-class interactive studio to manage generations, photos, and live tree connections.</div>
                  </div>
                </div>
                <a href="/familyStudio" class="btn btn-primary btn-sm px-3 py-2 fw-bold text-nowrap" style="border-radius: 8px;">
                  Open Studio <i class="bi bi-arrow-right ms-1"></i>
                </a>
              </div>

              <div class="alert alert-danger shadow-sm border-0 rounded-3" id="parentsForm_notification" style="display: none;">
                <div class="d-flex align-items-center">
                  <i class="bi bi-exclamation-octagon-fill fs-4 me-3"></i>
                  <p id="parentsForm_notification_error" class="mb-0"></p>
                </div>
              </div>

              <form id="parentsForm">
                <input type="hidden" name="action" value="updateParents">
                <input type="hidden" name="token" value="{{ $_SESSION['token'] ?? '' }}">

                <!-- Father's Card -->
                <div class="p-4 rounded-3 mb-4" style="background-color: #f8fafc; border: 1px solid #e2e8f0;">
                  <h6 class="fw-bold mb-3 text-dark text-uppercase" style="letter-spacing: 0.5px; font-size: 0.9rem;">
                    <i class="bi bi-person-fill text-primary me-1"></i> Father's Details
                  </h6>
                  <div class="row g-3">
                    <div class="col-md-6">
                      <label class="form-label">Father's First Name</label>
                      <input type="text" class="form-control" id="father_first_name" name="father_first_name" value="{{ $accountData['father_first_name'] ?? '' }}" placeholder="Enter first name">
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">Father's Last Name</label>
                      <input type="text" class="form-control" id="father_last_name" name="father_last_name" value="{{ $accountData['father_last_name'] ?? '' }}" placeholder="Enter last name">
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">Father's Email</label>
                      <input type="email" class="form-control" id="father_email" name="father_email" value="{{ $accountData['father_email'] ?? '' }}" placeholder="father@example.com">
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">Father's Mobile</label>
                      <input type="text" class="form-control" id="father_mobile" name="father_mobile" value="{{ $accountData['father_mobile'] ?? '' }}" placeholder="e.g. +44...">
                    </div>
                  </div>
                </div>

                <!-- Mother's Card -->
                <div class="p-4 rounded-3 mb-4" style="background-color: #f8fafc; border: 1px solid #e2e8f0;">
                  <h6 class="fw-bold mb-3 text-dark text-uppercase" style="letter-spacing: 0.5px; font-size: 0.9rem;">
                    <i class="bi bi-person-fill text-danger me-1"></i> Mother's Details
                  </h6>
                  <div class="row g-3">
                    <div class="col-md-4">
                      <label class="form-label">Mother's First Name</label>
                      <input type="text" class="form-control" id="mother_first_name" name="mother_first_name" value="{{ $accountData['mother_first_name'] ?? '' }}" placeholder="Enter first name">
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Mother's Last Name</label>
                      <input type="text" class="form-control" id="mother_last_name" name="mother_last_name" value="{{ $accountData['mother_last_name'] ?? '' }}" placeholder="Enter last name">
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Maiden Name</label>
                      <input type="text" class="form-control" id="mother_maiden" name="maiden_name" value="{{ $accountData['maiden_name'] ?? $accountData['mother_maiden'] ?? '' }}" placeholder="Maiden name">
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">Mother's Email</label>
                      <input type="email" class="form-control" id="mother_email" name="mother_email" value="{{ $accountData['mother_email'] ?? '' }}" placeholder="mother@example.com">
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">Mother's Mobile</label>
                      <input type="text" class="form-control" id="mother_mobile" name="mother_mobile" value="{{ $accountData['mother_mobile'] ?? '' }}" placeholder="e.g. +44...">
                    </div>
                  </div>
                </div>

                <button type="button" id="parentsBtn" class="btn-save">Save Parents Details</button>
              </form>
            </div>

            <!-- CHILDREN TAB -->
            <div class="tab-pane fade" id="v-pills-children" role="tabpanel" aria-labelledby="v-pills-children-tab">
              <div class="section-title">Children Information</div>
              <div class="section-subtitle">Add and manage children connections in your family tree.</div>
              <div class="section-divider"></div>

              <div class="alert alert-danger shadow-sm border-0 rounded-3" id="childrenForm_notification" style="display: none;">
                <div class="d-flex align-items-center">
                  <i class="bi bi-exclamation-octagon-fill fs-4 me-3"></i>
                  <p id="childrenForm_notification_error" class="mb-0"></p>
                </div>
              </div>

              <form id="childrenForm">
                <input type="hidden" name="action" value="updateChildren">
                <input type="hidden" name="token" value="{{ $_SESSION['token'] ?? '' }}">

                <div class="mb-4">
                  <label for="children" class="form-label fw-bold">Select Number of Children</label>
                  <select class="form-select" id="children" name="children">
                    <option value="0" selected>0 Children</option>
                    @for ($i = 1; $i <= 10; $i++)
                      <option value="{{ $i }}">{{ $i }} {{ $i === 1 ? 'Child' : 'Children' }}</option>
                    @endfor
                  </select>
                  <div id="children_help" class="form-text mt-2 fw-bold small"></div>
                  <div id="children_div" class="mt-3"></div>
                </div>

                <button type="button" id="childrenBtn" class="btn-save">Save Children Details</button>
              </form>
            </div>

            <!-- SIBLINGS TAB -->
            <div class="tab-pane fade" id="v-pills-siblings" role="tabpanel" aria-labelledby="v-pills-siblings-tab">
              <div class="section-title">Siblings Information</div>
              <div class="section-subtitle">Add and manage your brothers and sisters in your family tree.</div>
              <div class="section-divider"></div>

              <div class="alert alert-danger shadow-sm border-0 rounded-3" id="siblingsForm_notification" style="display: none;">
                <div class="d-flex align-items-center">
                  <i class="bi bi-exclamation-octagon-fill fs-4 me-3"></i>
                  <p id="siblingsForm_notification_error" class="mb-0"></p>
                </div>
              </div>

              <form id="siblingsForm">
                <input type="hidden" name="action" value="updateSiblings">
                <input type="hidden" name="token" value="{{ $_SESSION['token'] ?? '' }}">

                <div class="mb-4">
                  <label for="sibling" class="form-label fw-bold">Select Number of Siblings</label>
                  <select class="form-select" id="sibling" name="sibling">
                    <option value="0" selected>0 Siblings</option>
                    @for ($i = 1; $i <= 10; $i++)
                      <option value="{{ $i }}">{{ $i }} {{ $i === 1 ? 'Sibling' : 'Siblings' }}</option>
                    @endfor
                  </select>
                  <div id="sibling_help" class="form-text mt-2 fw-bold small"></div>
                  <div id="sibling_div" class="mt-3"></div>
                </div>

                <button type="button" id="siblingsBtn" class="btn-save">Save Siblings Details</button>
              </form>
            </div>

            <!-- MARITAL STATUS TAB -->
            <div class="tab-pane fade" id="v-pills-marital" role="tabpanel" aria-labelledby="v-pills-marital-tab">
              <div class="section-title">Marital Status & Spouse</div>
              <div class="section-subtitle">Configure your relationship status and partner connections.</div>
              <div class="section-divider"></div>

              <div class="alert alert-danger shadow-sm border-0 rounded-3" id="maritalForm_notification" style="display: none;">
                <div class="d-flex align-items-center">
                  <i class="bi bi-exclamation-octagon-fill fs-4 me-3"></i>
                  <p id="maritalForm_notification_error" class="mb-0"></p>
                </div>
              </div>

              <form id="maritalForm">
                <input type="hidden" name="action" value="updateMarital">
                <input type="hidden" name="token" value="{{ $_SESSION['token'] ?? '' }}">

                <div class="mb-4">
                  <label for="maritalStatus" class="form-label fw-bold">Marital Status</label>
                  <select class="form-select" id="maritalStatus" name="maritalStatus">
                    <option disabled {{ empty($accountData['maritalStatus']) ? 'selected' : '' }}>Select status...</option>
                    <option value="Single" {{ ($accountData['maritalStatus'] ?? '') === 'Single' ? 'selected' : '' }}>Single</option>
                    <option value="Yes - Add Husband" {{ ($accountData['maritalStatus'] ?? '') === 'Yes - Add Husband' ? 'selected' : '' }}>Yes - Add Husband</option>
                    <option value="Yes - Add Wife" {{ ($accountData['maritalStatus'] ?? '') === 'Yes - Add Wife' ? 'selected' : '' }}>Yes - Add Wife</option>
                    <option value="Divorced" {{ ($accountData['maritalStatus'] ?? '') === 'Divorced' ? 'selected' : '' }}>Divorced</option>
                    <option value="Widowed" {{ ($accountData['maritalStatus'] ?? '') === 'Widowed' ? 'selected' : '' }}>Widowed</option>
                  </select>
                </div>

                <!-- Dynamic Spouse Section -->
                <div id="spouse" style="display: none;" class="p-4 rounded-3 mb-4" style="background-color: #f8fafc; border: 1px solid #e2e8f0;">
                  <h6 class="fw-bold mb-3 text-dark text-uppercase" style="letter-spacing: 0.5px; font-size: 0.9rem;">
                    <i class="bi bi-heart-fill text-danger me-1"></i> Spouse Details
                  </h6>
                  <div class="row g-3">
                    <div class="col-md-6">
                      <label class="form-label">Spouse's Name</label>
                      <input type="text" class="form-control" id="spouse_name" name="spouse_name" value="{{ $accountData['spouse_name'] ?? '' }}" placeholder="Enter full name">
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">Spouse's Email</label>
                      <input type="email" class="form-control" id="spouse_email" name="spouse_email" value="{{ $accountData['spouse_email'] ?? '' }}" placeholder="Enter email">
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">Spouse's Mobile</label>
                      <input type="text" class="form-control" id="spouse_mobile" name="spouse_mobile" value="{{ $accountData['spouse_mobile'] ?? '' }}" placeholder="Enter mobile number">
                    </div>
                    <div class="col-md-6" id="maiden_name_div" style="display: none;">
                      <label class="form-label">Maiden Name</label>
                      <input type="text" class="form-control" id="maiden_name" name="maiden_name" placeholder="Enter maiden name">
                    </div>
                  </div>
                </div>

                <button type="button" id="maritalBtn" class="btn-save">Save Marital Status</button>
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

                <div class="custom-switch">
                  <div class="switch-info">
                    <h6>Browser &amp; Mobile Notifications</h6>
                    <p id="pushPrefHint">Get instant alerts on this device even when the app is closed.</p>
                  </div>
                  <div class="toggle-group">
                    <span class="toggle-label" id="pushPrefLabel">OFF</span>
                    <label class="switch">
                      <input type="checkbox" id="pushPrefToggle" data-standalone="1">
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

              <div class="section-divider mt-4"></div>
              <div class="switch-info">
                <h6>Your data</h6>
                <p>Download a copy of the personal data we hold about your account (GDPR Article 15). Shared family content created by other members isn't included.</p>
              </div>
              <div class="d-flex flex-wrap gap-2 mt-2">
                <button type="button" id="dataExportBtn" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                  <i class="bi bi-download me-1"></i> Download my data
                </button>
                <button type="button" id="dataDeleteBtn" class="btn btn-outline-danger btn-sm rounded-pill px-3">
                  <i class="bi bi-trash3 me-1"></i> Request account deletion
                </button>
              </div>
              <p id="dataExportStatus" class="small text-muted mt-2" style="display:none;"></p>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>
@endsection