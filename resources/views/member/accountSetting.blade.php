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
            <button class="nav-link" id="v-pills-familycode-tab" data-bs-toggle="pill" data-bs-target="#v-pills-familycode" type="button" role="tab" aria-controls="v-pills-familycode" aria-selected="false">
              <i class="bi bi-diagram-3-fill"></i> Family Network & Code
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

            <!-- FAMILY NETWORK & CODE TAB -->
            <div class="tab-pane fade" id="v-pills-familycode" role="tabpanel" aria-labelledby="v-pills-familycode-tab">
              <div class="section-title">Family Network & Code Management</div>
              <div class="section-subtitle">Manage your active family code, branch out into your own private family space, or join another family network.</div>
              <div class="section-divider"></div>

              <!-- Notifications Alert -->
              <div class="alert alert-danger shadow-sm border-0 rounded-3" id="famCodeAlert" style="display: none;">
                <div class="d-flex align-items-center">
                  <i class="bi bi-exclamation-octagon-fill fs-4 me-3"></i>
                  <p id="famCodeAlertMessage" class="mb-0"></p>
                </div>
              </div>

              <!-- Active Pending Request Alert (if any) -->
              @if(!empty($pendingFamilyRequest))
              <div class="alert alert-warning shadow-sm border-0 rounded-3 p-3 mb-4" id="pendingRequestAlert" style="background-color: #fffbeb; border: 1px solid #fef3c7; color: #92400e;">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                  <div>
                    <h6 class="fw-bold mb-1"><i class="bi bi-hourglass-split me-1 text-warning"></i> Pending Family Transfer Request</h6>
                    <p class="small mb-0">
                      You requested to join family code <strong class="badge bg-warning text-dark">{{ $pendingFamilyRequest['family_code'] }}</strong>.
                      Awaiting approval from <strong>{{ $pendingFamilyRequest['inviter_first_name'] }} {{ $pendingFamilyRequest['inviter_last_name'] }}</strong>.
                    </p>
                  </div>
                  <button type="button" id="cancelRequestBtn" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                    <i class="bi bi-x-circle me-1"></i> Cancel Request
                  </button>
                </div>
              </div>
              @endif

              <!-- Current Family Code Card -->
              <div class="card border-0 shadow-sm rounded-3 mb-4" style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border: 1px solid #e2e8f0;">
                <div class="card-body p-4">
                  <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                      <span class="text-uppercase text-muted fw-bold small" style="letter-spacing: 0.05em;">Your Current Family Code</span>
                      <h3 class="fw-bolder text-primary mb-1 mt-1" id="currentFamCodeText" style="letter-spacing: 1px;">
                        <i class="bi bi-hash text-muted"></i><span id="currentFamCodeValue">{{ $accountData['famCode'] ?? 'NOT ASSIGNED' }}</span>
                      </h3>
                      <p class="text-muted small mb-0">
                        This code links your profile, personal memories, family tree, and shared feed with other verified family members.
                      </p>
                    </div>
                    <div>
                      <button type="button" id="copyCurrentFamCodeBtn" class="btn btn-outline-primary rounded-pill px-4 fw-semibold" data-code="{{ $accountData['famCode'] ?? '' }}">
                        <i class="bi bi-clipboard me-1"></i> Copy Code
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Secondary / Maternal / Maiden Family Code Card -->
              <div class="card border-0 shadow-sm rounded-3 mb-4" style="background: #ffffff; border: 1px solid #e2e8f0;">
                <div class="card-body p-4">
                  <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: #faf5ff; color: #9333ea;">
                      <i class="bi bi-diagram-3-fill fs-4"></i>
                    </div>
                    <div class="flex-grow-1">
                      <div class="d-flex align-items-center gap-2 flex-wrap">
                        <h5 class="fw-bold mb-0">Secondary / Maternal / Maiden Family Code</h5>
                        <span class="badge rounded-pill px-2.5 py-1" style="background-color: #f3e8ff; color: #7e22ce; font-size: 0.75rem;">Dual Lineage &amp; In-Laws</span>
                      </div>
                      <span class="text-muted small">Connect to your maternal relatives and in-law circles in the Kinship Suggestion Engine.</span>
                    </div>
                    <div>
                      @if(!empty($accountData['otherFamCode']))
                        <span class="badge rounded-pill px-3 py-2 fw-semibold" style="background-color: #ecfdf5; color: #047857; border: 1px solid #a7f3d0;" id="secondaryCodeStatusBadge">
                          <i class="bi bi-check2-circle me-1"></i> Active Link: <strong>{{ $accountData['otherFamCode'] }}</strong>
                        </span>
                      @else
                        <span class="badge rounded-pill px-3 py-2 fw-semibold" style="background-color: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0;" id="secondaryCodeStatusBadge">
                          <i class="bi bi-dash-circle me-1"></i> Not Linked
                        </span>
                      @endif
                    </div>
                  </div>

                  <div class="p-3 rounded-3 mb-3" style="background-color: #faf5ff; border: 1px solid #f3e8ff; font-size: 0.85rem; color: #581c87;">
                    <div class="d-flex align-items-start gap-2">
                      <i class="bi bi-info-circle-fill text-purple mt-1"></i>
                      <div>
                        <strong>How this works:</strong>
                        <ul class="mb-0 ps-3 mt-1">
                          <li><strong>For Married Women:</strong> Enter your biological maiden family code to stay connected with your parents' and siblings' lineage.</li>
                          <li><strong>For All Members:</strong> Enter your mother's maternal family code to automatically discover maternal aunts, uncles, and cousins.</li>
                          <li><em>Your primary household tree (<span class="fw-bold text-dark">{{ $accountData['famCode'] ?? 'Primary' }}</span>) remains completely unchanged.</em></li>
                        </ul>
                      </div>
                    </div>
                  </div>

                  <form id="secondaryFamCodeForm" onsubmit="return false;">
                    <div class="row g-3 align-items-center">
                      <div class="col-md-7">
                        <label class="form-label small fw-bold text-muted text-uppercase" for="otherFamCodeInput">Maternal / Maiden Family Code</label>
                        <div class="input-group">
                          <span class="input-group-text bg-light text-muted fw-bold">#</span>
                          <input type="text" name="otherFamCode" id="otherFamCodeInput" class="form-control text-uppercase fw-bold" placeholder="e.g. ADE123" value="{{ $accountData['otherFamCode'] ?? '' }}" maxlength="12">
                        </div>
                        <div class="form-text">Must be different from your primary family code ({{ $accountData['famCode'] ?? '' }}).</div>
                      </div>
                      <div class="col-md-5 d-flex align-items-end gap-2 pt-2">
                        <button type="button" id="btnSaveOtherFamCode" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold shadow-sm flex-grow-1">
                          <i class="bi bi-check-lg me-1"></i> Save Secondary Code
                        </button>
                        <button type="button" id="btnClearOtherFamCode" class="btn btn-outline-secondary rounded-pill px-3 py-2 fw-semibold {{ empty($accountData['otherFamCode']) ? 'd-none' : '' }}" title="Clear secondary family code">
                          <i class="bi bi-x-circle me-1"></i> Clear
                        </button>
                      </div>
                    </div>
                  </form>
                </div>
              </div>

              <!-- Two Operational Panels: Stay Solo vs Join Existing -->
              <div class="row g-4">
                
                <!-- 1. Branch Out Solo -->
                <div class="col-md-6">
                  <div class="card h-100 border-0 shadow-sm rounded-3" style="background: #ffffff; border: 1px solid #e2e8f0;">
                    <div class="card-body p-4 d-flex flex-column">
                      <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: #eff6ff; color: #3b82f6;">
                          <i class="bi bi-tree-fill fs-4"></i>
                        </div>
                        <div>
                          <h5 class="fw-bold mb-0">Start Your Own Family Space</h5>
                          <span class="badge bg-light text-primary border border-primary-subtle">Branch Out / Solo</span>
                        </div>
                      </div>
                      
                      <p class="text-muted small mb-4 flex-grow-1">
                        Want to establish an independent household tree or branch out on your own? Generating a new code provisions a fresh private family lineage where you are the administrator.
                      </p>

                      <form id="staySoloForm" onsubmit="return false;">
                        <div class="mb-3">
                          <label class="form-label" for="soloSurname">Family Surname (Optional)</label>
                          <input type="text" name="surname" id="soloSurname" class="form-control" placeholder="{{ $lastName ?: 'Surname' }}" value="{{ $lastName }}">
                          <div class="form-text">Will create your 6-character code (e.g. {{ strtoupper(substr($lastName ?: 'OLA', 0, 3)) }}345).</div>
                        </div>

                        <button type="button" id="btnStaySolo" class="btn btn-primary w-100 py-2 fw-semibold rounded-3 shadow-sm">
                          <i class="bi bi-stars me-1"></i> Generate New Solo Code (6-char)
                        </button>
                      </form>
                    </div>
                  </div>
                </div>

                <!-- 2. Transfer / Join Existing Family -->
                <div class="col-md-6">
                  <div class="card h-100 border-0 shadow-sm rounded-3" style="background: #ffffff; border: 1px solid #e2e8f0;">
                    <div class="card-body p-4 d-flex flex-column">
                      <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: #ecfdf5; color: #10b981;">
                          <i class="bi bi-people-fill fs-4"></i>
                        </div>
                        <div>
                          <h5 class="fw-bold mb-0">Join Another Family Network</h5>
                          <span class="badge bg-light text-success border border-success-subtle">Transfer Request</span>
                        </div>
                      </div>

                      <p class="text-muted small mb-3">
                        Moving to an existing family network? Enter the family code and the details of a family member who can verify your relationship.
                      </p>

                      <form id="joinFamilyForm" onsubmit="return false;">
                        <div class="mb-2">
                          <label class="form-label" for="targetFamCode">Target Family Code *</label>
                          <input type="text" name="family_code" id="targetFamCode" class="form-control text-uppercase fw-bold" placeholder="e.g. OLA345" maxlength="10" required>
                        </div>

                        <div class="row g-2 mb-2">
                          <div class="col-6">
                            <label class="form-label" for="inviterFirstName">Inviter First Name *</label>
                            <input type="text" name="inviter_first_name" id="inviterFirstName" class="form-control" placeholder="First Name" required>
                          </div>
                          <div class="col-6">
                            <label class="form-label" for="inviterLastName">Inviter Last Name *</label>
                            <input type="text" name="inviter_last_name" id="inviterLastName" class="form-control" placeholder="Last Name" required>
                          </div>
                        </div>

                        <div class="mb-3">
                          <label class="form-label" for="inviterContact">Inviter Email or Mobile *</label>
                          <input type="text" name="inviter_email_or_mobile" id="inviterContact" class="form-control" placeholder="email@example.com or phone" required>
                        </div>

                        <button type="button" id="btnRequestJoin" class="btn btn-success w-100 py-2 fw-semibold rounded-3 shadow-sm">
                          <i class="bi bi-send-check me-1"></i> Send Transfer Request
                        </button>
                      </form>
                    </div>
                  </div>
                </div>

              </div>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
  document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    // 1. Copy Family Code
    const copyBtn = document.getElementById('copyCurrentFamCodeBtn');
    if (copyBtn) {
      copyBtn.addEventListener('click', async function() {
        const valSpan = document.getElementById('currentFamCodeValue');
        const code = (valSpan ? valSpan.textContent : copyBtn.getAttribute('data-code') || '').trim();
        if (!code) return;

        try {
          if (navigator.clipboard && navigator.clipboard.writeText) {
            await navigator.clipboard.writeText(code);
          } else {
            const ta = document.createElement('textarea');
            ta.value = code;
            document.body.appendChild(ta);
            ta.select();
            document.execCommand('copy');
            document.body.removeChild(ta);
          }

          const originalHtml = copyBtn.innerHTML;
          copyBtn.innerHTML = '<i class="bi bi-check2 me-1"></i> Copied!';
          copyBtn.classList.remove('btn-outline-primary');
          copyBtn.classList.add('btn-success');
          setTimeout(() => {
            copyBtn.innerHTML = originalHtml;
            copyBtn.classList.remove('btn-success');
            copyBtn.classList.add('btn-outline-primary');
          }, 2500);

          if (window.Swal) {
            window.Swal.fire({
              toast: true,
              position: 'top-end',
              icon: 'success',
              title: 'Family Code copied: ' + code,
              showConfirmButton: false,
              timer: 2500
            });
          }
        } catch (err) {
          console.error('Clipboard copy error:', err);
        }
      });
    }

    // 2. Branch Out Solo
    const btnStaySolo = document.getElementById('btnStaySolo');
    if (btnStaySolo) {
      btnStaySolo.addEventListener('click', function() {
        const surnameInput = document.getElementById('soloSurname');
        const surname = surnameInput ? surnameInput.value.trim() : '';

        const proceed = () => {
          btnStaySolo.disabled = true;
          const origHtml = btnStaySolo.innerHTML;
          btnStaySolo.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Generating...';

          const fd = new FormData();
          fd.append('action', 'staySoloCode');
          fd.append('surname', surname);
          if (csrfToken) {
            fd.append('token', csrfToken);
          }

          fetch('/accountSetting', {
            method: 'POST',
            body: fd,
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'X-XSRF-TOKEN': csrfToken,
              'X-CSRF-TOKEN': csrfToken
            }
          })
          .then(res => res.json())
          .then(data => {
            btnStaySolo.disabled = false;
            btnStaySolo.innerHTML = origHtml;

            if (data.status === 200 || data.success) {
              const newCode = (data.data && data.data.family_code) || 'Updated';
              const valSpan = document.getElementById('currentFamCodeValue');
              if (valSpan) valSpan.textContent = newCode;
              if (copyBtn) copyBtn.setAttribute('data-code', newCode);

              if (window.Swal) {
                window.Swal.fire({
                  icon: 'success',
                  title: 'Branched Out Successfully!',
                  html: 'Your new solo Family Code is <strong>' + newCode + '</strong>.<br><br>Your profile has been transitioned to your new private family tree.',
                  confirmButtonColor: '#4f46e5'
                }).then(() => {
                  window.location.reload();
                });
              } else {
                alert('Success! Your new Family Code is: ' + newCode);
                window.location.reload();
              }
            } else {
              const err = data.message || (data.data && data.data.message) || 'Could not generate solo family code.';
              if (window.Swal) {
                window.Swal.fire({ icon: 'error', title: 'Error', text: err, confirmButtonColor: '#4f46e5' });
              } else {
                alert(err);
              }
            }
          })
          .catch(err => {
            btnStaySolo.disabled = false;
            btnStaySolo.innerHTML = origHtml;
            console.error('Solo code error:', err);
            if (window.Swal) {
              window.Swal.fire({ icon: 'error', title: 'Network Error', text: 'Failed to process request. Please try again.', confirmButtonColor: '#4f46e5' });
            }
          });
        };

        if (window.Swal) {
          window.Swal.fire({
            title: 'Branch Out to Solo Family?',
            html: 'This will generate a brand-new unique Family Code for you.<br><br>You will become the family administrator of your own lineage with a fresh family tree. Are you sure you want to proceed?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Branch Out',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#4f46e5'
          }).then((res) => {
            if (res.isConfirmed) {
              proceed();
            }
          });
        } else {
          if (confirm('Are you sure you want to branch out to your own solo family space?')) {
            proceed();
          }
        }
      });
    }

    // 3. Request to Join Another Family
    const btnRequestJoin = document.getElementById('btnRequestJoin');
    if (btnRequestJoin) {
      btnRequestJoin.addEventListener('click', function() {
        const famCodeEl = document.getElementById('targetFamCode');
        const fNameEl = document.getElementById('inviterFirstName');
        const lNameEl = document.getElementById('inviterLastName');
        const contactEl = document.getElementById('inviterContact');

        const famCode = famCodeEl ? famCodeEl.value.trim() : '';
        const fName = fNameEl ? fNameEl.value.trim() : '';
        const lName = lNameEl ? lNameEl.value.trim() : '';
        const contact = contactEl ? contactEl.value.trim() : '';

        if (!famCode || !fName || !lName || !contact) {
          if (window.Swal) {
            window.Swal.fire({
              icon: 'warning',
              title: 'Missing Required Fields',
              text: 'Please fill in target family code and all inviter details.',
              confirmButtonColor: '#10b981'
            });
          } else {
            alert('Please fill in target family code and all inviter details.');
          }
          return;
        }

        btnRequestJoin.disabled = true;
        const origHtml = btnRequestJoin.innerHTML;
        btnRequestJoin.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Submitting...';

        const fd = new FormData();
        fd.append('action', 'requestJoinFamily');
        fd.append('family_code', famCode);
        fd.append('inviter_first_name', fName);
        fd.append('inviter_last_name', lName);
        fd.append('inviter_email_or_mobile', contact);
        if (csrfToken) {
          fd.append('token', csrfToken);
        }

        fetch('/accountSetting', {
          method: 'POST',
          body: fd,
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-XSRF-TOKEN': csrfToken,
            'X-CSRF-TOKEN': csrfToken
          }
        })
        .then(res => res.json())
        .then(data => {
          btnRequestJoin.disabled = false;
          btnRequestJoin.innerHTML = origHtml;

          if (data.status === 200 || data.success) {
            const msg = data.message || (data.data && data.data.message) || 'Transfer request submitted successfully.';
            if (window.Swal) {
              window.Swal.fire({
                icon: 'success',
                title: 'Request Sent!',
                text: msg,
                confirmButtonColor: '#10b981'
              }).then(() => {
                window.location.reload();
              });
            } else {
              alert(msg);
              window.location.reload();
            }
          } else {
            const err = data.message || (data.data && data.data.message) || 'Could not submit transfer request.';
            if (window.Swal) {
              window.Swal.fire({ icon: 'error', title: 'Request Failed', text: err, confirmButtonColor: '#10b981' });
            } else {
              alert(err);
            }
          }
        })
        .catch(err => {
          btnRequestJoin.disabled = false;
          btnRequestJoin.innerHTML = origHtml;
          console.error('Join request error:', err);
          if (window.Swal) {
            window.Swal.fire({ icon: 'error', title: 'Network Error', text: 'Failed to process request. Please try again.', confirmButtonColor: '#10b981' });
          }
        });
      });
    }

    // 4. Cancel Pending Request
    const cancelBtn = document.getElementById('cancelRequestBtn');
    if (cancelBtn) {
      cancelBtn.addEventListener('click', function() {
        const proceedCancel = () => {
          cancelBtn.disabled = true;
          const origHtml = cancelBtn.innerHTML;
          cancelBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

          const fd = new FormData();
          fd.append('action', 'cancelFamilyRequest');
          if (csrfToken) {
            fd.append('token', csrfToken);
          }

          fetch('/accountSetting', {
            method: 'POST',
            body: fd,
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'X-XSRF-TOKEN': csrfToken,
              'X-CSRF-TOKEN': csrfToken
            }
          })
          .then(res => res.json())
          .then(data => {
            cancelBtn.disabled = false;
            cancelBtn.innerHTML = origHtml;

            if (data.status === 200 || data.success) {
              const alertBox = document.getElementById('pendingRequestAlert');
              if (alertBox) alertBox.style.display = 'none';

              if (window.Swal) {
                window.Swal.fire({
                  toast: true,
                  position: 'top-end',
                  icon: 'info',
                  title: 'Transfer request cancelled.',
                  showConfirmButton: false,
                  timer: 2500
                });
              }
            } else {
              const err = data.message || 'Could not cancel request.';
              if (window.Swal) {
                window.Swal.fire({ icon: 'error', title: 'Error', text: err });
              }
            }
          })
          .catch(err => {
            cancelBtn.disabled = false;
            cancelBtn.innerHTML = origHtml;
            console.error('Cancel request error:', err);
          });
        };

        if (window.Swal) {
          window.Swal.fire({
            title: 'Cancel Request?',
            text: 'Are you sure you want to cancel your pending family transfer request?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Cancel Request',
            cancelButtonText: 'Keep Request',
            confirmButtonColor: '#dc2626'
          }).then((res) => {
            if (res.isConfirmed) proceedCancel();
          });
        } else {
          if (confirm('Cancel your pending transfer request?')) proceedCancel();
        }
      });
    }

    // 5. Update Secondary / Maternal / Maiden Family Code
    const btnSaveOtherCode = document.getElementById('btnSaveOtherFamCode');
    const btnClearOtherCode = document.getElementById('btnClearOtherFamCode');
    const otherCodeInput = document.getElementById('otherFamCodeInput');
    const secStatusBadge = document.getElementById('secondaryCodeStatusBadge');

    const submitSecondaryCode = (codeToSave) => {
      if (btnSaveOtherCode) {
        btnSaveOtherCode.disabled = true;
      }
      if (btnClearOtherCode) {
        btnClearOtherCode.disabled = true;
      }

      const fd = new FormData();
      fd.append('action', 'updateSecondaryFamilyCode');
      fd.append('otherFamCode', codeToSave);
      if (csrfToken) {
        fd.append('token', csrfToken);
      }

      fetch('/accountSetting', {
        method: 'POST',
        body: fd,
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'X-XSRF-TOKEN': csrfToken,
          'X-CSRF-TOKEN': csrfToken
        }
      })
      .then(res => res.json())
      .then(data => {
        if (btnSaveOtherCode) btnSaveOtherCode.disabled = false;
        if (btnClearOtherCode) btnClearOtherCode.disabled = false;

        if (data.status === 200 || data.success) {
          const msg = data.message || (data.data && data.data.message) || 'Secondary family code updated successfully.';
          const rawCode = (codeToSave || '').trim().toUpperCase().replace('#', '');
          const safeCode = rawCode.replace(/[^A-Z0-9]/g, '');
          
          if (otherCodeInput) {
            otherCodeInput.value = safeCode;
          }

          if (secStatusBadge) {
            if (safeCode) {
              secStatusBadge.style.backgroundColor = '#ecfdf5';
              secStatusBadge.style.color = '#047857';
              secStatusBadge.style.borderColor = '#a7f3d0';
              secStatusBadge.innerHTML = `<i class="bi bi-check2-circle me-1"></i> Active Link: <strong>${safeCode}</strong>`;
              if (btnClearOtherCode) btnClearOtherCode.classList.remove('d-none');
            } else {
              secStatusBadge.style.backgroundColor = '#f1f5f9';
              secStatusBadge.style.color = '#64748b';
              secStatusBadge.style.borderColor = '#e2e8f0';
              secStatusBadge.innerHTML = `<i class="bi bi-dash-circle me-1"></i> Not Linked`;
              if (btnClearOtherCode) btnClearOtherCode.classList.add('d-none');
            }
          }

          if (window.Swal) {
            window.Swal.fire({
              icon: 'success',
              title: newCode ? 'Secondary Code Linked!' : 'Secondary Code Cleared',
              text: msg,
              confirmButtonColor: '#4f46e5'
            });
          } else {
            alert(msg);
          }
        } else {
          const err = data.message || (data.data && data.data.message) || 'Could not update secondary family code.';
          if (window.Swal) {
            window.Swal.fire({ icon: 'error', title: 'Update Failed', text: err, confirmButtonColor: '#4f46e5' });
          } else {
            alert(err);
          }
        }
      })
      .catch(err => {
        if (btnSaveOtherCode) btnSaveOtherCode.disabled = false;
        if (btnClearOtherCode) btnClearOtherCode.disabled = false;
        console.error('Secondary code update error:', err);
        if (window.Swal) {
          window.Swal.fire({ icon: 'error', title: 'Network Error', text: 'Failed to update. Please try again.', confirmButtonColor: '#4f46e5' });
        } else {
          alert('Network error while updating secondary code.');
        }
      });
    };

    if (btnSaveOtherCode) {
      btnSaveOtherCode.addEventListener('click', function() {
        const codeVal = otherCodeInput ? otherCodeInput.value.trim() : '';
        submitSecondaryCode(codeVal);
      });
    }

    if (btnClearOtherCode) {
      btnClearOtherCode.addEventListener('click', function() {
        const proceedClear = () => {
          submitSecondaryCode('');
        };

        if (window.Swal) {
          window.Swal.fire({
            title: 'Clear Secondary Family Code?',
            text: 'This will disconnect your profile from maternal and in-law kinship suggestions for this code.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Clear Code',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#dc2626'
          }).then(res => {
            if (res.isConfirmed) proceedClear();
          });
        } else {
          if (confirm('Clear your secondary family code?')) proceedClear();
        }
      });
    }
  });
  </script>
        </div>
      </div>
    </div>
  </div>
@endsection