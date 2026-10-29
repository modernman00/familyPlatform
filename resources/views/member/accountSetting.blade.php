@extends('layouts.profileBase')
@section('title', 'ACCOUNT SETTING')
@section('data-page-id', 'AccountSettingPage')

@push('styles')
  <style>
    /* Modern Account Settings Structure */
    body {
      background-color: #f8f9fc;
      background-image: radial-gradient(at top left, rgba(255, 235, 240, 0.5) 0%, transparent 40%);
      font-family: var(--font-sans, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif);
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
      color: #334155;
      font-size: 0.95rem;
      font-weight: 500;
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
      color: #334155;
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
      color: #1e293b;
    }

    .nav-pills .nav-link.active {
      background-color: #fdf5f5;
      color: #a31621;
      border-left: 3px solid #a31621;
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
      color: #0f172a;
      margin-bottom: 0.25rem;
    }

    .section-subtitle {
      color: #334155;
      font-size: 0.9rem;
      font-weight: 500;
      margin-bottom: 20px;
    }

    .section-divider {
      height: 1px;
      background-color: #cbd5e1;
      margin-bottom: 30px;
    }

    /* Form Inputs & High Contrast Visibility */
    .form-label {
      font-weight: 700;
      color: #0f172a;
      font-size: 0.88rem;
      margin-bottom: 0.4rem;
    }

    .form-control, .form-select {
      border: 1px solid #cbd5e1;
      border-radius: 8px;
      padding: 10px 14px;
      font-size: 0.95rem;
      color: #0f172a !important;
      background-color: #ffffff !important;
      transition: all 0.2s;
    }

    .form-select option {
      color: #0f172a !important;
      background-color: #ffffff !important;
      font-weight: 500;
      padding: 8px;
    }

    .form-control::placeholder, .form-select::placeholder {
      color: #475569 !important;
      opacity: 1 !important;
    }

    .form-text {
      color: #334155 !important;
      font-weight: 500;
    }

    .form-control:focus, .form-select:focus {
      border-color: #a31621;
      box-shadow: 0 0 0 3px rgba(163,22,33, 0.15);
      outline: none;
    }

    .form-control[readonly] {
      background-color: #f1f5f9 !important;
      color: #334155 !important;
      font-weight: 500;
    }

    .switch-info p {
      margin-bottom: 0;
      font-size: 0.85rem;
      color: #334155;
      font-weight: 500;
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
      background-color: #a31621;
    }

    input:checked + .slider:before {
      transform: translateX(20px);
    }

    /* Buttons */
    .btn-save {
      background-color: #a31621;
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
      background-color: #8a1019;
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

    @if(($_GET['notice'] ?? '') === 'email_required_for_party_studio')
      <div class="alert alert-warning d-flex align-items-center gap-3 mb-4 rounded-3 shadow-sm" role="alert">
        <i class="bi bi-envelope-exclamation-fill fs-4 text-warning"></i>
        <div>
          <strong>Email Address Required for PartyPlatform Studio:</strong>
          Please add a valid email address to your profile below so your PartyPlatform host account and RSVP notifications can be linked.
        </div>
      </div>
    @endif

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

          <div class="px-3 pt-3 pb-1 text-uppercase text-muted fw-bold" id="familyConnectionsHeading" style="font-size: 0.72rem; letter-spacing: 0.06em; border-top: 1px solid #f1f5f9;">
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

              <div class="alert alert-info shadow-sm border-0 rounded-3" id="profileForm_notification" style="display: none;">
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

              <div class="alert alert-primary shadow-sm border-0 rounded-3 mb-4 d-flex flex-column flex-md-row align-items-center justify-content-between p-3 gap-2" style="background: linear-gradient(135deg, #fbefef 0%, #f5dede 100%);">
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

              {{-- ── Lineage Scope Selector ── --}}
              {{-- Shown whenever the user has a secondary/maternal family code set. --}}
              @php $hasMaternalCode = (($birthFamily['state'] ?? '') === 'active') && !empty($accountData['otherFamCode']); @endphp
              @if($hasMaternalCode)
              <div class="p-3 rounded-3 mb-4 border" id="parents-lineage-banner" style="background: linear-gradient(135deg,#fbefef 0%,#fbefef 100%); border-color:#e9d5ff!important;">
                <div class="fw-bold text-purple mb-2" style="color:#8a1019;">
                  <i class="bi bi-diagram-3-fill me-2"></i>Which family tree do these parents belong to?
                </div>
                <p class="small text-muted mb-3" id="parents-lineage-hint">You have both a marital and a birth family code. Your biological parents default to your <strong>Birth / Maiden Family (#{{ $accountData['otherFamCode'] }})</strong>, and any invitation sent will use that code.</p>
                <div class="d-flex flex-column flex-sm-row gap-3" role="group" aria-label="Lineage scope for parents">
                  <label class="lineage-scope-pill d-flex align-items-center gap-2 px-4 py-3 rounded-3 border fw-semibold cursor-pointer" style="min-height:44px; cursor:pointer; flex:1;" id="parents-scope-primary-label">
                    <input type="radio" name="parents_lineage_ui" id="parents-scope-primary" value="primary" class="form-check-input m-0">
                    <span>
                      <i class="bi bi-house-heart-fill text-primary me-1"></i>
                      Marital / Household Tree
                      <span class="badge bg-primary ms-2 text-uppercase" style="font-size:.65rem;">{{ $accountData['famCode'] ?? 'Primary' }}</span>
                    </span>
                  </label>
                  <label class="lineage-scope-pill d-flex align-items-center gap-2 px-4 py-3 rounded-3 border fw-semibold cursor-pointer" style="min-height:44px; cursor:pointer; flex:1;" id="parents-scope-maternal-label">
                    <input type="radio" name="parents_lineage_ui" id="parents-scope-maternal" value="maternal" checked class="form-check-input m-0">
                    <span>
                      <i class="bi bi-tree-fill text-success me-1"></i>
                      My Birth / Maiden Family
                      <span class="badge bg-success ms-2 text-uppercase" style="font-size:.65rem;">{{ $accountData['otherFamCode'] }}</span>
                    </span>
                  </label>
                </div>
              </div>
              @endif

              <div class="alert alert-info shadow-sm border-0 rounded-3" id="parentsForm_notification" style="display: none;">
                <div class="d-flex align-items-center">
                  <i class="bi bi-exclamation-octagon-fill fs-4 me-3"></i>
                  <p id="parentsForm_notification_error" class="mb-0"></p>
                </div>
              </div>

              <form id="parentsForm">
                <input type="hidden" name="action" value="updateParents">
                <input type="hidden" name="token" value="{{ $_SESSION['token'] ?? '' }}">
                {{-- lineage_scope & lineage_code are updated by the radio buttons above (JS) --}}
                <input type="hidden" name="lineage_scope" id="parentsLineageScope" value="{{ $hasMaternalCode ? 'maternal' : 'primary' }}">
                <input type="hidden" name="lineage_code" id="parentsLineageCode" value="{{ $accountData['otherFamCode'] ?? '' }}">
                {{-- Expose the user's maiden/other code for use by the JS invite helper --}}
                <input type="hidden" id="otherFamCode_id" value="{{ $accountData['otherFamCode'] ?? '' }}">
                <input type="hidden" id="famCode_id" value="{{ $accountData['famCode'] ?? '' }}">

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

                <!-- Parents' relationship status (drives the divorced / separated stamp on the tree) -->
                <div class="mb-4">
                  <label for="parents_status" class="form-label fw-bold">Are your parents still together?</label>
                  <select class="form-select" id="parents_status" name="parents_status">
                    <option value="">Prefer not to say</option>
                    <option value="married">Yes - together</option>
                    <option value="divorced">Divorced</option>
                    <option value="separated">Separated</option>
                    <option value="widowed">One of them has passed away</option>
                  </select>
                  <div class="form-text">If you have half-siblings, add them under <strong>Siblings</strong> and choose "Same Mother Only" or "Same Father Only".</div>
                </div>

                <button type="button" id="parentsBtn" class="btn-save">Save Parents Details</button>
              </form>
            </div>

            <!-- CHILDREN TAB -->
            <div class="tab-pane fade" id="v-pills-children" role="tabpanel" aria-labelledby="v-pills-children-tab">
              <div class="section-title">Children Information</div>
              <div class="section-subtitle">Add and manage children connections in your family tree.</div>
              <div class="section-divider"></div>

              <div class="alert alert-info shadow-sm border-0 rounded-3" id="childrenForm_notification" style="display: none;">
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

              {{-- ── Lineage Scope Selector ── --}}
              @if($hasMaternalCode)
              <div class="p-3 rounded-3 mb-4 border" id="siblings-lineage-banner" style="background: linear-gradient(135deg,#fbefef 0%,#fbefef 100%); border-color:#e9d5ff!important;">
                <div class="fw-bold mb-2" style="color:#8a1019;">
                  <i class="bi bi-diagram-3-fill me-2"></i>Which family tree do these siblings belong to?
                </div>
                <p class="small text-muted mb-3" id="siblings-lineage-hint">You have both a marital and a birth family code. Your brothers and sisters default to your <strong>Birth / Maiden Family (#{{ $accountData['otherFamCode'] }})</strong>, and any invitation sent will use that code.</p>
                <div class="d-flex flex-column flex-sm-row gap-3" role="group" aria-label="Lineage scope for siblings">
                  <label class="lineage-scope-pill d-flex align-items-center gap-2 px-4 py-3 rounded-3 border fw-semibold" style="min-height:44px; cursor:pointer; flex:1;" id="siblings-scope-primary-label">
                    <input type="radio" name="siblings_lineage_ui" id="siblings-scope-primary" value="primary" class="form-check-input m-0">
                    <span>
                      <i class="bi bi-house-heart-fill text-primary me-1"></i>
                      Marital / Household Tree
                      <span class="badge bg-primary ms-2 text-uppercase" style="font-size:.65rem;">{{ $accountData['famCode'] ?? 'Primary' }}</span>
                    </span>
                  </label>
                  <label class="lineage-scope-pill d-flex align-items-center gap-2 px-4 py-3 rounded-3 border fw-semibold" style="min-height:44px; cursor:pointer; flex:1;" id="siblings-scope-maternal-label">
                    <input type="radio" name="siblings_lineage_ui" id="siblings-scope-maternal" value="maternal" checked class="form-check-input m-0">
                    <span>
                      <i class="bi bi-tree-fill text-success me-1"></i>
                      My Birth / Maiden Family
                      <span class="badge bg-success ms-2 text-uppercase" style="font-size:.65rem;">{{ $accountData['otherFamCode'] }}</span>
                    </span>
                  </label>
                </div>
              </div>
              @endif

              <div class="alert alert-info shadow-sm border-0 rounded-3" id="siblingsForm_notification" style="display: none;">
                <div class="d-flex align-items-center">
                  <i class="bi bi-exclamation-octagon-fill fs-4 me-3"></i>
                  <p id="siblingsForm_notification_error" class="mb-0"></p>
                </div>
              </div>

              <form id="siblingsForm">
                <input type="hidden" name="action" value="updateSiblings">
                <input type="hidden" name="token" value="{{ $_SESSION['token'] ?? '' }}">
                <input type="hidden" name="lineage_scope" id="siblingsLineageScope" value="{{ $hasMaternalCode ? 'maternal' : 'primary' }}">
                <input type="hidden" name="lineage_code" id="siblingsLineageCode" value="{{ $accountData['otherFamCode'] ?? '' }}">

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

              <div class="alert alert-info shadow-sm border-0 rounded-3" id="maritalForm_notification" style="display: none;">
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
                    <div class="col-md-6" id="spouse_gender_div" style="display: none;">
                      <label class="form-label">Former partner is your</label>
                      <select class="form-select" id="spouse_gender" name="spouse_gender">
                        <option value="">Select...</option>
                        <option value="Male">Husband / Male partner</option>
                        <option value="Female">Wife / Female partner</option>
                      </select>
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
                    <span class="toggle-label">{{ ($accountData['email_notifications'] ?? 'on') === 'on' ? 'ON' : 'OFF' }}</span>
                    <label class="switch">
                      <input type="checkbox" name="email_notifications" value="{{ ($accountData['email_notifications'] ?? 'on') === 'on' ? 'on' : 'off' }}" {{ ($accountData['email_notifications'] ?? 'on') === 'on' ? 'checked' : '' }}>
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
                    <span class="toggle-label">{{ ($accountData['sms_notifications'] ?? 'on') === 'on' ? 'ON' : 'OFF' }}</span>
                    <label class="switch">
                      <input type="checkbox" name="sms_notifications" value="{{ ($accountData['sms_notifications'] ?? 'on') === 'on' ? 'on' : 'off' }}" {{ ($accountData['sms_notifications'] ?? 'on') === 'on' ? 'checked' : '' }}>
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
                    <span class="toggle-label" id="pushPrefLabel">CHECKING...</span>
                    <label class="switch">
                      <input type="checkbox" id="pushPrefToggle" data-standalone="1">
                      <span class="slider"></span>
                    </label>
                  </div>
                </div>

                <!-- Self-Serve Notification Diagnostics & Device Test Card -->
                <div class="p-3 mt-3 rounded-3 border" style="background-color: #f8fafc; border-color: #e2e8f0 !important;">
                  <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                    <span class="fw-bold text-dark" style="font-size: 0.9rem;">
                      <i class="bi bi-shield-check text-success me-1"></i> Device Delivery &amp; Push Diagnostics
                    </span>
                    <span id="pushDeviceBadge" class="badge bg-light text-secondary border">Checking Status...</span>
                  </div>
                  <p class="small text-muted mb-3" style="font-size: 0.82rem; line-height: 1.45;">
                    Verify whether this device is actively registered to receive lockscreen and in-app milestone alerts.
                  </p>
                  <div class="d-flex flex-wrap gap-2">
                    <button type="button" id="sendTestNotifBtn" class="btn btn-sm btn-outline-danger d-inline-flex align-items-center" style="min-height: 44px; border-color: #a31621; color: #a31621;">
                      <i class="bi bi-broadcast me-1.5"></i>
                      <span>Send Test Alert to This Device</span>
                    </button>
                    <button type="button" id="resyncDeviceBtn" class="btn btn-sm btn-light border d-inline-flex align-items-center" style="min-height: 44px;">
                      <i class="bi bi-arrow-clockwise me-1.5"></i>
                      <span>Re-sync Device Endpoint</span>
                    </button>
                  </div>
                  <div id="testNotifFeedback" class="small mt-2" style="display: none;"></div>
                </div>

                <!-- Icon & Button Tooltip Frequency & Guidance Control -->
                <div class="custom-switch mt-3 pt-3 border-top">
                  <div class="switch-info">
                    <h6><i class="bi bi-info-circle-fill text-primary me-1"></i> Icon &amp; Button Explanatory Tooltips</h6>
                    <p>Manage how frequently guidance tooltips appear when hovering over icons and buttons across the platform.</p>
                  </div>
                  <div class="d-flex align-items-center gap-2">
                    <select id="tooltipModeSelect" class="form-select form-select-sm" style="max-width: 220px; font-size: 0.85rem; border-radius: 8px;">
                      <option value="smart">💡 Smart (Hides Once Learned)</option>
                      <option value="always">🟢 Always Show (Guided Mode)</option>
                      <option value="off">⚪ Off (Minimal UI)</option>
                    </select>
                  </div>
                </div>
                <div class="d-flex flex-wrap align-items-center justify-content-between mt-2 pt-1 mb-2">
                  <small class="text-muted" style="font-size: 0.78rem; max-width: 440px; line-height: 1.4;">
                    In <strong>Smart</strong> mode, tooltips appear only if you hesitate for &gt;450ms, and disappear once you click or view them 3 times.
                  </small>
                  <button type="button" id="resetTooltipMasteryBtn" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 d-inline-flex align-items-center" style="font-size: 0.75rem; min-height: 36px;" title="Reset your learned icons to view all tooltips again">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Learned Icons
                  </button>
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
                    <p>Require a 6-digit verification code sent to your email when signing in.</p>
                  </div>
                  <div class="toggle-group">
                    <span class="toggle-label">{{ ($accountData['two_factor_auth'] ?? 'off') === 'on' ? 'ON' : 'OFF' }}</span>
                    <label class="switch">
                      <input type="checkbox" name="two_factor_auth" value="{{ ($accountData['two_factor_auth'] ?? 'off') === 'on' ? 'on' : 'off' }}" {{ ($accountData['two_factor_auth'] ?? 'off') === 'on' ? 'checked' : '' }}>
                      <span class="slider"></span>
                    </label>
                  </div>
                </div>

                <div class="mb-4 mt-4">
                  <label class="form-label fw-bold">Profile Visibility</label>
                  <select class="form-select" name="profile_visibility" id="profileVisibilitySelect" style="background-color: #ffffff;">
                    <option value="Public" {{ ($accountData['profile_visibility'] ?? 'Public') === 'Public' ? 'selected' : '' }}>Public</option>
                    <option value="Family Only" {{ ($accountData['profile_visibility'] ?? '') === 'Family Only' ? 'selected' : '' }}>Family Only</option>
                    <option value="Private" {{ ($accountData['profile_visibility'] ?? '') === 'Private' ? 'selected' : '' }}>Private</option>
                  </select>
                </div>

                <div class="custom-switch">
                  <div class="switch-info">
                    <h6>Show My Profile</h6>
                    <p>Allow family members and other verified users to discover your profile in member directory search.</p>
                  </div>
                  <div class="toggle-group">
                    <span class="toggle-label">{{ ($accountData['show_my_profile'] ?? 'on') === 'on' ? 'ON' : 'OFF' }}</span>
                    <label class="switch">
                      <input type="checkbox" name="show_my_profile" value="{{ ($accountData['show_my_profile'] ?? 'on') === 'on' ? 'on' : 'off' }}" {{ ($accountData['show_my_profile'] ?? 'on') === 'on' ? 'checked' : '' }}>
                      <span class="slider"></span>
                    </label>
                  </div>
                </div>

                <div class="custom-switch">
                  <div class="switch-info">
                    <h6>Show Year of Birth</h6>
                    <p>Display your birth year under Date of Birth on your profile (e.g. 29 / Jul / 1979). When turned off, only the day and month are visible (e.g. 29 / Jul).</p>
                  </div>
                  <div class="toggle-group">
                    <span class="toggle-label">{{ ($accountData['show_birth_year'] ?? 'off') === 'on' ? 'ON' : 'OFF' }}</span>
                    <label class="switch">
                      <input type="checkbox" name="show_birth_year" value="{{ ($accountData['show_birth_year'] ?? 'off') === 'on' ? 'on' : 'off' }}" {{ ($accountData['show_birth_year'] ?? 'off') === 'on' ? 'checked' : '' }}>
                      <span class="slider"></span>
                    </label>
                  </div>
                </div>

                <button type="button" id="privacyBtn" class="btn-save mt-3">Save Privacy Settings</button>
              </form>

              <!-- Biometric Passkeys Card -->
              <div class="mt-5 pt-4 border-top">
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <div>
                    <h6 class="fw-bold mb-1"><i class="bi bi-fingerprint text-primary me-2"></i>Biometric Passkeys</h6>
                    <p class="text-muted small mb-0">Log in securely using Face ID, Touch ID, or Windows Hello without passwords.</p>
                  </div>
                  <button type="button" id="btn-register-passkey" class="btn-save btn-sm px-3" style="font-size: 0.85rem;">
                    <i class="bi bi-plus-circle me-1"></i> Register This Device
                  </button>
                </div>

                <div id="passkey-status-msg" style="display: none;" class="alert small mb-3"></div>

                @if(!empty($passkeys) && count($passkeys) > 0)
                  <div class="d-flex flex-column gap-2 mt-3">
                    @foreach($passkeys as $pk)
                      <div class="d-flex justify-content-between align-items-center p-3 rounded-3 border" style="background-color: #f8fafc;">
                        <div class="d-flex align-items-center gap-3">
                          <div class="rounded-3 bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                            <i class="bi bi-laptop"></i>
                          </div>
                          <div>
                            <div class="fw-bold small text-dark">{{ $pk['device_name'] ?: 'Biometric Device' }}</div>
                            <div class="text-muted" style="font-size: 0.75rem;">Added {{ date('M j, Y', strtotime($pk['created_at'])) }}</div>
                          </div>
                        </div>
                        <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3 btn-revoke-passkey" data-id="{{ $pk['credential_id'] }}">
                          Revoke
                        </button>
                      </div>
                    @endforeach
                  </div>
                @else
                  <div class="p-3 text-center border rounded-3 text-muted small" style="background-color: #f8fafc;">
                    No biometric devices registered yet. Click "Register This Device" above to enable passkey sign in.
                  </div>
                @endif
              </div>

              <div class="section-divider mt-4"></div>
              <div class="switch-info">
                <h6>Your data & account deletion (GDPR)</h6>
                <p>Download a copy of your personal data (Article 15) or manage account deletion (Article 17) with options for immediate permanent erasure or a 30-day scheduled grace period.</p>
              </div>
              <div class="d-flex flex-wrap gap-2 mt-2">
                <button type="button" id="dataExportBtn" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                  <i class="bi bi-download me-1"></i> Download my data
                </button>
                <button type="button" id="dataDeleteBtn" class="btn btn-outline-danger btn-sm rounded-pill px-3">
                  <i class="bi bi-trash3 me-1"></i> Delete account
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
              @if(!empty($pendingFamilyRequest) && ($pendingFamilyRequest['purpose'] ?? 'join') !== 'birth_link')
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
                      @if(!empty($pendingFamilyRequest) && ($pendingFamilyRequest['purpose'] ?? 'join') !== 'birth_link')
                        <div class="mt-2 mb-2" id="currentFamCodePendingContainer">
                          <span class="badge rounded-pill px-3 py-1.5 fw-semibold" style="background-color: #fef3c7; color: #92400e; border: 1px solid #fde68a;">
                            <i class="bi bi-hourglass-split me-1"></i> Transfer to <strong>#{{ $pendingFamilyRequest['family_code'] }}</strong> pending approval
                          </span>
                        </div>
                      @endif
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

              <!-- Change Family Code by Family Vote (RFC-0005) -->
              @php
                $cr = $codeRename ?? ['is_creator' => false, 'can_propose' => false, 'code' => '', 'open' => null, 'cooldown' => false];
                $crOpen = $cr['open'] ?? null;
              @endphp
              <div class="card border-0 shadow-sm rounded-3 mb-4" id="codeRenameCard" style="background: #ffffff; border: 1px solid #e2e8f0;">
                <div class="card-body p-4">
                  <div class="d-flex align-items-center gap-2 mb-1">
                    <i class="bi bi-ui-checks-grid fs-4 text-primary"></i>
                    <h5 class="fw-bold mb-0">Change Our Family Code</h5>
                  </div>
                  <p class="text-muted small mb-3">The creator of the code can propose a new one. The whole family votes, and if more vote yes than no it changes for everyone automatically.</p>
                  <div id="codeRenameAlert" class="alert alert-danger small py-2 mb-3" style="display:none;" role="alert"></div>

                  @if($crOpen)
                    <div class="p-3 rounded-3 mb-3" style="background:#f8fafc; border:1px solid #e2e8f0;" id="codeRenameOpen">
                      <div class="fw-semibold mb-1">Vote open: #{{ $crOpen['old_code'] }} &rarr; #{{ $crOpen['new_code'] }}</div>
                      <div class="small text-muted mb-2">
                        So far {{ (int)($crOpen['yes'] ?? 0) }} yes and {{ (int)($crOpen['no'] ?? 0) }} no from {{ (int)($crOpen['eligible'] ?? 0) }} members.
                        It passes if more members vote yes than no (at least one besides you must vote) when voting closes on {{ date('j M Y', strtotime((string)$crOpen['expires_at'])) }}, or straight away if over half the family says yes.
                      </div>
                      <div class="d-flex flex-wrap gap-2">
                        <a class="btn btn-primary rounded-pill px-4 fw-semibold" style="min-height:44px;" href="/profilePage?post_no={{ (int)$crOpen['post_no'] }}#post{{ (int)$crOpen['post_no'] }}">
                          <i class="bi bi-box-arrow-up-right me-1"></i> Go to the vote
                        </a>
                        @if(($crOpen['requested_by'] ?? '') === (string)($accountData['id'] ?? ''))
                          <button type="button" id="btnCancelCodeRename" class="btn btn-outline-danger rounded-pill px-4 fw-semibold" style="min-height:44px;">Cancel vote</button>
                        @endif
                      </div>
                    </div>
                  @elseif(!empty($cr['can_propose']))
                    <form id="codeRenameForm" onsubmit="return false;">
                      @include('csrf')
                      <label class="form-label small fw-bold text-muted text-uppercase" for="newFamilyCode">New family code</label>
                      <input type="text" id="newFamilyCode" class="form-control mb-1" maxlength="12" minlength="4" placeholder="e.g. ADEYEMI" autocomplete="off" style="text-transform:uppercase;">
                      <div class="form-text mb-3">4 to 12 letters or numbers. Your family is asked to vote first; nothing changes until the vote passes.</div>
                      <button type="button" id="btnProposeCodeRename" class="btn btn-primary w-100 rounded-pill fw-semibold" style="min-height:44px;">
                        <i class="bi bi-send me-1"></i> Ask my family to vote
                      </button>
                    </form>
                  @elseif(!empty($cr['is_creator']) && !empty($cr['cooldown']))
                    <div class="alert alert-secondary small mb-0">The family code was changed recently. You can propose another change in 30 days.</div>
                  @else
                    <div class="alert alert-secondary small mb-0">Only the person who created this family code can propose changing it. If they do, you will be asked to vote.</div>
                  @endif
                </div>
              </div>

              <!-- Birth / Maiden Family Code Card (RFC-0004) -->
              @php
                $bf = $birthFamily ?? ['state' => 'none', 'code' => '', 'origin' => null, 'pending' => null];
                $bfState = (string)($bf['state'] ?? 'none');
                $maidenPrefill = (string)($accountData['maiden_name'] ?? $accountData['mother_maiden'] ?? '');
              @endphp
              <div class="card border-0 shadow-sm rounded-3 mb-4" id="birthFamilyCard" data-state="{{ $bfState }}" style="background: #ffffff; border: 1px solid #e2e8f0;">
                <div class="card-body p-4">
                  <div class="d-flex align-items-center gap-3 mb-3 flex-wrap">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: #fbefef; color: #b8202c;">
                      <i class="bi bi-diagram-3-fill fs-4"></i>
                    </div>
                    <div class="flex-grow-1">
                      <div class="d-flex align-items-center gap-2 flex-wrap">
                        <h5 class="fw-bold mb-0">Birth / Maiden Family Code</h5>
                        <span class="badge rounded-pill px-2.5 py-1" style="background-color: #fbefef; color: #8a1019; font-size: 0.75rem;">Dual Lineage</span>
                      </div>
                      <span class="text-muted small">Keep your birth family connected without changing your household code.</span>
                    </div>
                    <div>
                      @if($bfState === 'active')
                        <span class="badge rounded-pill px-3 py-2 fw-semibold" style="background-color: #ecfdf5; color: #047857; border: 1px solid #a7f3d0;" id="birthCodeStatusBadge">
                          <i class="bi bi-check2-circle me-1"></i> {{ ($bf['origin'] ?? '') === 'created' ? 'Created by you' : 'Linked &amp; approved' }}
                        </span>
                      @elseif($bfState === 'pending')
                        <span class="badge rounded-pill px-3 py-2 fw-semibold" style="background-color: #fef3c7; color: #92400e; border: 1px solid #fde68a;" id="birthCodeStatusBadge">
                          <i class="bi bi-hourglass-split me-1"></i> Awaiting approval
                        </span>
                      @elseif($bfState === 'unverified')
                        <span class="badge rounded-pill px-3 py-2 fw-semibold" style="background-color: #fef2f2; color: #b91c1c; border: 1px solid #fecaca;" id="birthCodeStatusBadge">
                          <i class="bi bi-exclamation-triangle me-1"></i> Unverified
                        </span>
                      @else
                        <span class="badge rounded-pill px-3 py-2 fw-semibold" style="background-color: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0;" id="birthCodeStatusBadge">
                          <i class="bi bi-dash-circle me-1"></i> Not set up
                        </span>
                      @endif
                    </div>
                  </div>

                  <div id="birthFamilyAlert" class="alert alert-danger small py-2 mb-3" style="display:none;" role="alert"></div>

                  @if($bfState === 'active')
                    <div class="p-3 rounded-3 mb-3 d-flex align-items-center justify-content-between flex-wrap gap-3" style="background-color: #fbefef; border: 1px solid #fbefef;">
                      <div>
                        <span class="text-uppercase text-muted fw-bold small" style="letter-spacing: 0.05em;">Your birth family code</span>
                        <h3 class="fw-bolder mb-0 mt-1" style="color:#8a1019; letter-spacing:1px;">#<span id="birthCodeValue">{{ $bf['code'] }}</span></h3>
                      </div>
                      <div class="d-flex gap-2 flex-wrap">
                        <button type="button" id="btnCopyBirthCode" class="btn btn-outline-primary rounded-pill px-4 fw-semibold" style="min-height:44px;" data-code="{{ $bf['code'] }}">
                          <i class="bi bi-clipboard me-1"></i> Copy Code
                        </button>
                        @if(($bf['origin'] ?? '') === 'linked')
                          <button type="button" id="btnClearBirthCode" class="btn btn-outline-secondary rounded-pill px-3 fw-semibold" style="min-height:44px;" title="Unlink this birth family">
                            <i class="bi bi-x-circle me-1"></i> Unlink
                          </button>
                        @endif
                      </div>
                    </div>
                    <p class="small text-muted mb-0">
                      Use <strong>My Birth / Maiden Family</strong> in the <em>Parents</em> and <em>Siblings</em> tabs to invite your family into this tree.
                      Your household tree (<strong>{{ $accountData['famCode'] ?? 'Primary' }}</strong>) stays unchanged.
                      @if(($bf['origin'] ?? '') === 'created')
                        This code is permanent once created.
                      @endif
                    </p>
                  @elseif($bfState === 'pending')
                    <div class="p-3 rounded-3 d-flex align-items-center justify-content-between flex-wrap gap-2" style="background-color: #fffbeb; border: 1px solid #fef3c7; color: #92400e;">
                      <p class="small mb-0">
                        You asked to link to <strong>#{{ $bf['code'] }}</strong>. Waiting for
                        <strong>{{ $bf['pending']['inviter_first_name'] ?? '' }} {{ $bf['pending']['inviter_last_name'] ?? '' }}</strong> to confirm.
                      </p>
                      <button type="button" id="cancelBirthLinkBtn" class="btn btn-sm btn-outline-danger rounded-pill px-3" style="min-height:44px;">
                        <i class="bi bi-x-circle me-1"></i> Cancel Request
                      </button>
                    </div>
                  @else
                    @if($bfState === 'unverified')
                      <div class="alert alert-warning small mb-3" id="birthUnverifiedNotice">
                        <i class="bi bi-shield-exclamation me-1"></i>
                        The code <strong>#{{ $bf['code'] }}</strong> was saved before family codes were verified, so it is not used for invitations or tree changes.
                        Create your birth family or ask a member of that family to confirm you below.
                      </div>
                    @endif

                    <div class="row g-4">
                      <div class="col-md-6">
                        <div class="h-100 p-3 rounded-3" style="border: 1px solid #e2e8f0;">
                          <h6 class="fw-bold mb-1"><i class="bi bi-stars me-1 text-primary"></i> My birth family isn't on yet</h6>
                          <p class="text-muted small">We'll create a new family code from your maiden name. Your parents and siblings can join with it.</p>
                          <form id="createBirthCodeForm" onsubmit="return false;">
                            @include('csrf')
                            <label class="form-label small fw-bold text-muted text-uppercase" for="birthMaidenName">Maiden / birth family name</label>
                            <input type="text" id="birthMaidenName" name="maiden_name" class="form-control mb-3" maxlength="60" placeholder="e.g. Adeyemi" value="{{ $maidenPrefill }}" autocomplete="off">
                            <button type="button" id="btnCreateBirthCode" class="btn btn-primary w-100 rounded-pill fw-semibold" style="min-height:44px;">
                              <i class="bi bi-plus-circle me-1"></i> Create Birth Family Code
                            </button>
                          </form>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="h-100 p-3 rounded-3" style="border: 1px solid #e2e8f0;">
                          <h6 class="fw-bold mb-1"><i class="bi bi-link-45deg me-1 text-success"></i> My family is already here</h6>
                          <p class="text-muted small">Enter their code and a family member who can confirm you. Your household code won't change.</p>
                          <form id="linkBirthCodeForm" onsubmit="return false;">
                            @include('csrf')
                            <div class="input-group mb-2">
                              <span class="input-group-text bg-light text-muted fw-bold">#</span>
                              <input type="text" id="birthLinkCode" name="family_code" class="form-control text-uppercase fw-bold" maxlength="12" placeholder="e.g. ADE123" autocomplete="off">
                            </div>
                            <div class="row g-2 mb-2">
                              <div class="col-6"><input type="text" id="birthInviterFirst" name="inviter_first_name" class="form-control" placeholder="Their first name" maxlength="100"></div>
                              <div class="col-6"><input type="text" id="birthInviterLast" name="inviter_last_name" class="form-control" placeholder="Their last name" maxlength="100"></div>
                            </div>
                            <input type="text" id="birthInviterContact" name="inviter_email_or_mobile" class="form-control mb-3" placeholder="Their email or mobile" maxlength="100">
                            <button type="button" id="btnRequestBirthLink" class="btn btn-success w-100 rounded-pill fw-semibold" style="min-height:44px;">
                              <i class="bi bi-send me-1"></i> Request to Link
                            </button>
                          </form>
                        </div>
                      </div>
                    </div>
                  @endif
                </div>
              </div>

              <!-- Two Operational Panels: Stay Solo vs Join Existing -->
              <div class="row g-4">
                
                <!-- 1. Branch Out Solo -->
                <div class="col-md-6">
                  <div class="card h-100 border-0 shadow-sm rounded-3" style="background: #ffffff; border: 1px solid #e2e8f0;">
                    <div class="card-body p-4 d-flex flex-column">
                      <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: #fdf5f5; color: #a31621;">
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
    @include('csrf')
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
    @include('csrf')
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

    // Tab persistence & deep-link focus (e.g. #v-pills-familycode or #birthFamilyCard)
    const handleDeepLinkTarget = function(hashVal) {
      if (!hashVal) return;
      const cleanHash = hashVal.replace(/^#/, '');
      let targetTrigger = document.querySelector(`button[data-bs-target="#${cleanHash}"]`);
      let elementToFocus = null;

      if (cleanHash === 'family-connections' || cleanHash === 'familyConnections') {
        targetTrigger = document.getElementById('v-pills-parents-tab');
        elementToFocus = document.getElementById('familyConnectionsHeading') || document.getElementById('v-pills-parents');
      } else if (cleanHash === 'family-code-mgt' || cleanHash === 'familyCodeMgt') {
        targetTrigger = document.getElementById('v-pills-familycode-tab');
        elementToFocus = document.getElementById('birthFamilyCard') || document.getElementById('v-pills-familycode');
      } else if (!targetTrigger) {
        // Check if hash matches an element inside a tab pane (e.g. #birthFamilyCard)
        const innerEl = document.getElementById(cleanHash);
        if (innerEl) {
          elementToFocus = innerEl;
          const parentPane = innerEl.closest('.tab-pane');
          if (parentPane && parentPane.id) {
            targetTrigger = document.querySelector(`button[data-bs-target="#${parentPane.id}"]`);
          }
        }
      } else {
        // If clicking #v-pills-familycode directly, check for birthFamilyCard
        if (cleanHash === 'v-pills-familycode') {
          elementToFocus = document.getElementById('birthFamilyCard');
        }
      }

      if (targetTrigger && window.bootstrap && window.bootstrap.Tab) {
        const tabInst = window.bootstrap.Tab.getOrCreateInstance(targetTrigger);
        tabInst.show();
      }

      if (elementToFocus) {
        setTimeout(function() {
          elementToFocus.scrollIntoView({ behavior: 'smooth', block: 'center' });
          elementToFocus.classList.add('pulse-highlight-target');
          const inputToFocus = elementToFocus.querySelector('input:not([type="hidden"]), select, button.btn-primary');
          if (inputToFocus) {
            try { inputToFocus.focus({ preventScroll: true }); } catch (e) {}
          }
          setTimeout(function() {
            elementToFocus.classList.remove('pulse-highlight-target');
          }, 2500);
        }, 180);
      }
    };

    handleDeepLinkTarget(window.location.hash);
    window.addEventListener('hashchange', function() {
      handleDeepLinkTarget(window.location.hash);
    });

    // Keep URL hash updated when switching tabs
    document.querySelectorAll('button[data-bs-toggle="pill"]').forEach(function(pillBtn) {
      pillBtn.addEventListener('shown.bs.tab', function(e) {
        const target = e.target.getAttribute('data-bs-target');
        if (target && history.replaceState) {
          history.replaceState(null, null, target);
        }
      });
    });

    // Live update for custom switch ON/OFF labels and checkbox value sync
    document.querySelectorAll('.toggle-group .switch input[type="checkbox"]').forEach(function(checkbox) {
      checkbox.value = checkbox.checked ? 'on' : 'off';
      checkbox.addEventListener('change', function() {
        this.value = this.checked ? 'on' : 'off';
        const label = this.closest('.toggle-group')?.querySelector('.toggle-label');
        if (label && label.id !== 'pushPrefLabel') {
          label.textContent = this.checked ? 'ON' : 'OFF';
        }
      });
    });

    // Keep Show My Profile toggle synchronized when Profile Visibility dropdown changes
    const profileVisSelect = document.getElementById('profileVisibilitySelect');
    const showMyProfileCheckbox = document.querySelector('#privacyForm input[name="show_my_profile"]');
    if (profileVisSelect && showMyProfileCheckbox) {
      profileVisSelect.addEventListener('change', function() {
        if (this.value === 'Public') {
          showMyProfileCheckbox.checked = true;
          showMyProfileCheckbox.dispatchEvent(new Event('change'));
        } else if (this.value === 'Private') {
          showMyProfileCheckbox.checked = false;
          showMyProfileCheckbox.dispatchEvent(new Event('change'));
        }
      });
    }

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

    // 1b. RFC-0005: propose / cancel a family code change (family poll)
    (function () {
      const alertBox = document.getElementById('codeRenameAlert');
      const showErr = (msg) => { if (alertBox) { alertBox.textContent = msg; alertBox.style.display = 'block'; } };
      const post = (action, extra) => {
        const fd = new FormData();
        fd.append('action', action);
        Object.keys(extra || {}).forEach((k) => fd.append(k, extra[k]));
        if (csrfToken) fd.append('token', csrfToken);
        return fetch('/accountSetting', {
          method: 'POST',
          body: fd,
          headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-XSRF-TOKEN': csrfToken, 'X-CSRF-TOKEN': csrfToken }
        }).then((res) => res.json());
      };
      const ok = (d) => d && (d.status === 200 || d.status === 'success' || d.success === true || d.ok === true);
      const msgOf = (d, fallback) => (d && (d.message || (d.data && d.data.message))) || fallback;

      const btnPropose = document.getElementById('btnProposeCodeRename');
      if (btnPropose) {
        btnPropose.addEventListener('click', function () {
          const input = document.getElementById('newFamilyCode');
          const code = input ? input.value.trim() : '';
          if (alertBox) alertBox.style.display = 'none';
          if (!code) { showErr('Please enter the new family code.'); return; }
          btnPropose.disabled = true;
          post('proposeFamilyCodeRename', { new_code: code })
            .then((d) => {
              if (ok(d)) { window.location.hash = 'v-pills-familycode'; window.location.reload(); return; }
              btnPropose.disabled = false;
              showErr(msgOf(d, 'Could not start the family vote.'));
            })
            .catch(() => { btnPropose.disabled = false; showErr('Network error. Please try again.'); });
        });
      }

      const btnCancel = document.getElementById('btnCancelCodeRename');
      if (btnCancel) {
        btnCancel.addEventListener('click', function () {
          btnCancel.disabled = true;
          post('cancelFamilyCodeRename')
            .then((d) => {
              if (ok(d)) { window.location.hash = 'v-pills-familycode'; window.location.reload(); return; }
              btnCancel.disabled = false;
              showErr(msgOf(d, 'Could not cancel the vote.'));
            })
            .catch(() => { btnCancel.disabled = false; showErr('Network error. Please try again.'); });
        });
      }
    })();

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

            const isSuccess = data.status === 200 || data.status === 'success' || data.success === true || data.ok === true;
            if (isSuccess) {
              const newCode = (data.data && data.data.family_code) || (data.token && data.token.family_code) || 'Updated';
              const valSpan = document.getElementById('currentFamCodeValue');
              if (valSpan) valSpan.textContent = newCode;
              if (copyBtn) copyBtn.setAttribute('data-code', newCode);

              if (window.Swal) {
                window.Swal.fire({
                  icon: 'success',
                  title: 'Branched Out Successfully!',
                  html: 'Your new solo Family Code is <strong>' + newCode + '</strong>.<br><br>Your profile has been transitioned to your new private family tree.',
                  confirmButtonColor: '#a31621'
                }).then(() => {
                  window.location.hash = 'v-pills-familycode';
                  window.location.reload();
                });
              } else {
                alert('Success! Your new Family Code is: ' + newCode);
                window.location.hash = 'v-pills-familycode';
                window.location.reload();
              }
            } else {
              const err = data.message || (data.data && data.data.message) || 'Could not generate solo family code.';
              if (window.Swal) {
                window.Swal.fire({ icon: 'error', title: 'Error', text: err, confirmButtonColor: '#a31621' });
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
              window.Swal.fire({ icon: 'error', title: 'Network Error', text: 'Failed to process request. Please try again.', confirmButtonColor: '#a31621' });
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
            confirmButtonColor: '#a31621'
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
        const currentUserId = document.getElementById('userId')?.value || '{{ $_SESSION["id"] ?? "" }}';
        if (currentUserId) {
          fd.append('id', currentUserId);
        }
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

          const isSuccess = data.status === 200 || data.status === 'success' || data.success === true || data.ok === true;
          if (isSuccess) {
            const msg = data.message || (data.data && data.data.message) || 'Transfer request submitted successfully.';
            if (window.Swal) {
              window.Swal.fire({
                icon: 'success',
                title: 'Transfer Request Sent!',
                html: msg + '<br><br><span class="badge bg-warning text-dark px-3 py-2"><i class="bi bi-hourglass-split me-1"></i> Awaiting Inviter Approval</span><br><small class="text-muted mt-2 d-block">Your primary family code will automatically switch once your inviter approves the transfer request.</small>',
                confirmButtonColor: '#10b981'
              }).then(() => {
                window.location.hash = 'v-pills-familycode';
                window.location.reload();
              });
            } else {
              alert(msg);
              window.location.hash = 'v-pills-familycode';
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

            const isSuccess = data.status === 200 || data.status === 'success' || data.success === true || data.ok === true;
            if (isSuccess) {
              const alertBox = document.getElementById('pendingRequestAlert');
              if (alertBox) alertBox.style.display = 'none';
              const pendingContainer = document.getElementById('currentFamCodePendingContainer');
              if (pendingContainer) pendingContainer.style.display = 'none';

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

    // 5. Birth / Maiden Family Code (RFC-0004): create, link, unlink, cancel, copy
    const birthAlert = document.getElementById('birthFamilyAlert');
    const showBirthError = (msg) => {
      if (birthAlert) {
        birthAlert.textContent = msg;
        birthAlert.style.display = 'block';
      }
      if (window.Swal) window.Swal.fire({ icon: 'error', title: 'Could not continue', text: msg, confirmButtonColor: '#a31621' });
    };

    const postBirthAction = (fields, btn, onDone) => {
      if (birthAlert) birthAlert.style.display = 'none';
      const origHtml = btn ? btn.innerHTML : '';
      if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
      }
      const fd = new FormData();
      Object.entries(fields).forEach(([k, v]) => fd.append(k, v));
      if (csrfToken) fd.append('token', csrfToken);

      fetch('/accountSetting', {
        method: 'POST',
        body: fd,
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-XSRF-TOKEN': csrfToken, 'X-CSRF-TOKEN': csrfToken }
      })
        .then((res) => res.json().catch(() => ({})))
        .then((data) => {
          if (btn) { btn.disabled = false; btn.innerHTML = origHtml; }
          const ok = data?.status === 200 || data?.status === 'success' || data?.success === true || data?.ok === true;
          if (ok) {
            onDone(data?.message || data?.data?.message || 'Done.');
          } else {
            showBirthError(data?.message || data?.data?.message || 'Something went wrong. Please try again.');
          }
        })
        .catch(() => {
          if (btn) { btn.disabled = false; btn.innerHTML = origHtml; }
          showBirthError('Network error. Please try again.');
        });
    };

    const reloadToFamilyTab = (msg) => {
      const go = () => {
        window.location.hash = 'v-pills-familycode';
        window.location.reload();
      };
      if (window.Swal) {
        window.Swal.fire({ icon: 'success', title: 'Done', text: msg, confirmButtonColor: '#a31621' }).then(go);
      } else {
        go();
      }
    };

    const btnCreateBirth = document.getElementById('btnCreateBirthCode');
    if (btnCreateBirth) {
      btnCreateBirth.addEventListener('click', () => {
        const maiden = (document.getElementById('birthMaidenName')?.value || '').trim();
        if (maiden.length < 2) {
          showBirthError('Enter your maiden (birth family) name first.');
          return;
        }
        postBirthAction({ action: 'createBirthFamilyCode', maiden_name: maiden }, btnCreateBirth, reloadToFamilyTab);
      });
    }

    const btnLinkBirth = document.getElementById('btnRequestBirthLink');
    if (btnLinkBirth) {
      btnLinkBirth.addEventListener('click', () => {
        const val = (id) => (document.getElementById(id)?.value || '').trim();
        const code = val('birthLinkCode').toUpperCase().replace(/[^A-Z0-9]/g, '');
        if (!code || !val('birthInviterFirst') || !val('birthInviterLast') || !val('birthInviterContact')) {
          showBirthError('Fill in the family code and the name and contact of a family member who can confirm you.');
          return;
        }
        postBirthAction({
          action: 'requestBirthFamilyLink',
          family_code: code,
          inviter_first_name: val('birthInviterFirst'),
          inviter_last_name: val('birthInviterLast'),
          inviter_email_or_mobile: val('birthInviterContact')
        }, btnLinkBirth, reloadToFamilyTab);
      });
    }

    const confirmThen = (opts, proceed) => {
      if (window.Swal) {
        window.Swal.fire({ icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc2626', ...opts }).then((r) => { if (r.isConfirmed) proceed(); });
      } else if (confirm(opts.text || opts.title)) {
        proceed();
      }
    };

    const btnClearBirth = document.getElementById('btnClearBirthCode');
    if (btnClearBirth) {
      btnClearBirth.addEventListener('click', () => {
        confirmThen(
          { title: 'Unlink birth family?', text: 'You will leave this family tree. The tree and its members are not deleted.', confirmButtonText: 'Yes, unlink' },
          () => postBirthAction({ action: 'updateSecondaryFamilyCode', otherFamCode: '' }, btnClearBirth, reloadToFamilyTab)
        );
      });
    }

    const btnCancelBirthLink = document.getElementById('cancelBirthLinkBtn');
    if (btnCancelBirthLink) {
      btnCancelBirthLink.addEventListener('click', () => {
        confirmThen(
          { title: 'Cancel request?', text: 'Cancel your pending request to link a birth family?', confirmButtonText: 'Yes, cancel' },
          () => postBirthAction({ action: 'cancelFamilyRequest' }, btnCancelBirthLink, reloadToFamilyTab)
        );
      });
    }

    const btnCopyBirth = document.getElementById('btnCopyBirthCode');
    if (btnCopyBirth) {
      btnCopyBirth.addEventListener('click', () => {
        const code = btnCopyBirth.getAttribute('data-code') || '';
        const done = () => {
          const orig = btnCopyBirth.innerHTML;
          btnCopyBirth.innerHTML = '<i class="bi bi-check2 me-1"></i> Copied';
          setTimeout(() => { btnCopyBirth.innerHTML = orig; }, 1800);
        };
        if (navigator.clipboard?.writeText) {
          navigator.clipboard.writeText(code).then(done).catch(() => {});
        }
      });
    }

    // WebAuthn Passkey Registration & Revocation Handlers
    const regPasskeyBtn = document.getElementById('btn-register-passkey');
    const passkeyStatusMsg = document.getElementById('passkey-status-msg');

    const showPasskeyMsg = (text, isSuccess) => {
      if (!passkeyStatusMsg) return;
      passkeyStatusMsg.style.display = 'block';
      passkeyStatusMsg.className = isSuccess ? 'alert alert-success small mb-3' : 'alert alert-danger small mb-3';
      passkeyStatusMsg.textContent = text;
    };

    const base64URLToBuffer = (base64URL) => {
      const base64 = base64URL.replace(/-/g, '+').replace(/_/g, '/');
      const padLength = (4 - (base64.length % 4)) % 4;
      const padded = base64.padEnd(base64.length + padLength, '=');
      const binary = atob(padded);
      const bytes = new Uint8Array(binary.length);
      for (let i = 0; i < binary.length; i++) {
        bytes[i] = binary.charCodeAt(i);
      }
      return bytes.buffer;
    };

    const bufferToBase64URL = (buffer) => {
      const bytes = new Uint8Array(buffer);
      let binary = '';
      for (let i = 0; i < bytes.byteLength; i++) {
        binary += String.fromCharCode(bytes[i]);
      }
      return btoa(binary).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
    };

    if (regPasskeyBtn) {
      regPasskeyBtn.addEventListener('click', async () => {
        const originalHtml = regPasskeyBtn.innerHTML;
        if (window.location.protocol !== 'https:' && window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1') {
          showPasskeyMsg('Passkey registration requires a secure connection (HTTPS).', false);
          return;
        }
        if (!window.PublicKeyCredential) {
          showPasskeyMsg('Passkeys are not supported on this browser or platform.', false);
          return;
        }

        try {
          regPasskeyBtn.disabled = true;
          regPasskeyBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Authorizing...';
          showPasskeyMsg('', true);
          if (passkeyStatusMsg) passkeyStatusMsg.style.display = 'none';

          const res = await fetch('/webauthn/register/options', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({})
          });
          const data = await res.json();
          if (data.status !== 'success' && data.status !== 200) {
            throw new Error(data.message || 'Failed to initialize biometric challenge.');
          }

          const options = data.data || data.token;
          const isIp = /^(\d{1,3}\.){3}\d{1,3}$/.test(window.location.hostname);
          const rpConfig = { name: 'FamilyPlatform' };
          if (!isIp && window.location.hostname !== 'localhost') {
            rpConfig.id = window.location.hostname;
          }

          const publicKey = {
            challenge: base64URLToBuffer(options.challenge),
            rp: rpConfig,
            user: {
              id: base64URLToBuffer(options.user.id),
              name: options.user.name,
              displayName: options.user.displayName
            },
            pubKeyCredParams: options.pubKeyCredParams,
            authenticatorSelection: options.authenticatorSelection || {
              authenticatorAttachment: 'platform',
              userVerification: 'preferred'
            },
            timeout: options.timeout || 60000,
            attestation: options.attestation || 'none'
          };

          const credential = await navigator.credentials.create({ publicKey });
          if (!credential) {
            throw new Error('Passkey creation was canceled.');
          }

          const verifyRes = await fetch('/webauthn/register', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
              id: credential.id,
              rawId: bufferToBase64URL(credential.rawId),
              type: credential.type,
              response: {
                clientDataJSON: bufferToBase64URL(credential.response.clientDataJSON),
                attestationObject: bufferToBase64URL(credential.response.attestationObject)
              }
            })
          });
          const verifyData = await verifyRes.json();

          if (verifyData.status === 'success' || verifyData.status === 200) {
            showPasskeyMsg('✓ Biometric device registered successfully!', true);
            setTimeout(() => window.location.reload(), 1200);
          } else {
            throw new Error(verifyData.message || 'Device registration failed on server.');
          }
        } catch (err) {
          console.warn(err);
          const msg = (err.name === 'NotAllowedError') 
            ? 'Biometric prompt was canceled or timed out.' 
            : (err.message || 'Could not register biometric device.');
          showPasskeyMsg(msg, false);
        } finally {
          regPasskeyBtn.disabled = false;
          regPasskeyBtn.innerHTML = originalHtml;
        }
      });
    }

    // Revocation Handlers
    document.querySelectorAll('.btn-revoke-passkey').forEach(btn => {
      btn.addEventListener('click', async () => {
        const credentialId = btn.getAttribute('data-id');
        if (!confirm('Are you sure you want to revoke this biometric device?')) return;

        const originalText = btn.textContent;
        btn.disabled = true;
        btn.textContent = 'Revoking...';

        try {
          const res = await fetch('/webauthn/revoke', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ credential_id: credentialId })
          });
          const data = await res.json();
          if (data.status === 'success' || data.status === 200) {
            window.location.reload();
          } else {
            (window.swalError || alert)(data.message || 'Failed to revoke device.');
            btn.disabled = false;
            btn.textContent = originalText;
          }
        } catch (e) {
          (window.swalError || alert)('Network error while revoking device.');
          btn.disabled = false;
          btn.textContent = originalText;
        }
      });
    });

    // Icon & Button Tooltip Frequency & Guidance Controls
    const tooltipModeSelect = document.getElementById('tooltipModeSelect');
    const resetTooltipMasteryBtn = document.getElementById('resetTooltipMasteryBtn');

    if (tooltipModeSelect && window.FamilyTooltip) {
      tooltipModeSelect.value = window.FamilyTooltip.getMode();
      tooltipModeSelect.addEventListener('change', function() {
        const selectedMode = this.value;
        window.FamilyTooltip.setMode(selectedMode);
        if (window.Swal) {
          window.Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: 'Tooltip mode updated to ' + selectedMode.toUpperCase(),
            showConfirmButton: false,
            timer: 2000
          });
        }
      });
    }

    if (resetTooltipMasteryBtn && window.FamilyTooltip) {
      resetTooltipMasteryBtn.addEventListener('click', function() {
        const origHtml = this.innerHTML;
        window.FamilyTooltip.resetMastery();
        this.innerHTML = '<i class="bi bi-check2 me-1"></i> Reset Complete!';
        this.classList.remove('btn-outline-secondary');
        this.classList.add('btn-success');
        
        setTimeout(() => {
          this.innerHTML = origHtml;
          this.classList.remove('btn-success');
          this.classList.add('btn-outline-secondary');
        }, 2200);

        if (window.Swal) {
          window.Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'info',
            title: 'Learned icon memory reset. All tooltips will show again.',
            showConfirmButton: false,
            timer: 2500
          });
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