@extends('layouts.profileBase')
@section('title', 'ACCOUNT SETTING')
@section('data-page-id', 'AccountSettingPage')

@push('styles')
  <style>
    /* Modern Sleek Design for Account Settings */
    body {
      background-color: #f7f9fc;
    }

    .settings-card {
      border: none;
      border-radius: 20px;
      box-shadow: 0 15px 35px rgba(50, 50, 93, 0.03), 0 5px 15px rgba(0, 0, 0, 0.03);
      background: #ffffff;
      overflow: hidden;
      transition: all 0.3s ease;
    }

    .settings-header {
      background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
      padding: 2.5rem 2rem;
      border-radius: 20px 20px 0 0;
      position: relative;
      overflow: hidden;
    }

    .settings-header::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    }

    .header-content {
      position: relative;
      z-index: 1;
      color: white;
    }

    .settings-title {
      font-weight: 800;
      letter-spacing: 1px;
      font-size: 1.8rem;
      margin-bottom: 0.5rem;
      text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .form-group-modern {
      margin-bottom: 1.5rem;
    }

    .form-label {
      font-weight: 600;
      color: #525f7f;
      font-size: 0.85rem;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      margin-bottom: 0.5rem;
    }

    .form-control,
    .form-select {
      border-radius: 12px;
      border: 2px solid #e9ecef;
      padding: 0.8rem 1.2rem;
      font-size: 1rem;
      color: #32325d;
      background-color: #fcfcfc;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .form-control:focus,
    .form-select:focus {
      border-color: #4facfe;
      background-color: #fff;
      box-shadow: 0 0 0 4px rgba(79, 172, 254, 0.15);
      transform: translateY(-1px);
    }

    .input-group-text {
      border-radius: 12px 0 0 12px;
      border: 2px solid #e9ecef;
      border-right: none;
      background-color: #f8f9fa;
      color: #8898aa;
    }

    .input-group .form-control {
      border-top-left-radius: 0;
      border-bottom-left-radius: 0;
    }

    .section-divider {
      height: 1px;
      background: linear-gradient(90deg, transparent, #e9ecef, transparent);
      margin: 2rem 0;
    }

    .section-heading {
      color: #32325d;
      font-weight: 700;
      margin-bottom: 1.5rem;
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }

    .section-heading i {
      color: #4facfe;
      background: rgba(79, 172, 254, 0.1);
      padding: 8px;
      border-radius: 8px;
    }

    .btn-submit {
      background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
      border: none;
      border-radius: 12px;
      padding: 1rem 3rem;
      color: white;
      font-weight: 700;
      letter-spacing: 0.5px;
      text-transform: uppercase;
      font-size: 0.9rem;
      box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11), 0 1px 3px rgba(0, 0, 0, 0.08);
      transition: all 0.3s;
    }

    .btn-submit:hover {
      transform: translateY(-2px);
      box-shadow: 0 7px 14px rgba(50, 50, 93, 0.1), 0 3px 6px rgba(0, 0, 0, 0.08);
      color: white;
    }

    .info-badge {
      background: rgba(255, 255, 255, 0.2);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.3);
      padding: 0.5rem 1rem;
      border-radius: 10px;
      color: white;
      display: inline-block;
      margin-top: 0.5rem;
      margin-right: 0.5rem;
    }

    .info-label {
      font-size: 0.7rem;
      text-transform: uppercase;
      opacity: 0.8;
      display: block;
    }

    .info-value {
      font-weight: 600;
      font-size: 1.1rem;
    }

    /* Reveal Animation */
    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(20px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .animate-reveal {
      animation: fadeInUp 0.6s ease-out forwards;
    }

    /* Family Member Dynamic Cards */
    .family-member-card {
      background: #ffffff;
      border: 1px solid #eef2f7;
      border-radius: 16px;
      padding: 1.5rem;
      margin-bottom: 1.5rem;
      position: relative;
      transition: all 0.3s ease;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
    }

    .family-member-card:hover {
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
      transform: translateY(-2px);
      border-color: #dbeafe;
    }

    .family-member-card::before {
      content: '';
      position: absolute;
      left: 0;
      top: 20px;
      bottom: 20px;
      width: 4px;
      background: linear-gradient(to bottom, #4facfe, #00f2fe);
      border-radius: 0 4px 4px 0;
    }

    .member-badge {
      background: #f0f7ff;
      color: #004e92;
      font-weight: 700;
      padding: 0.35rem 0.8rem;
      border-radius: 50px;
      font-size: 0.75rem;
      display: inline-flex;
      align-items: center;
      letter-spacing: 0.5px;
    }

    /* Refined Input Styling */
    .input-group-modern {
      background-color: #f9fafb;
      border: 1px solid #e0e6ed;
      border-radius: 12px;
      display: flex;
      align-items: center;
      transition: all 0.2s;
      overflow: hidden;
      position: relative;
      /* Context for absolute positioning if needed */
    }

    .input-group-modern:focus-within {
      border-color: #4facfe;
      box-shadow: 0 0 0 3px rgba(79, 172, 254, 0.15);
      background-color: #fff;
    }

    .input-group-modern .input-icon {
      padding-left: 1rem;
      padding-right: 0.75rem;
      color: #adb5bd;
      font-size: 1.1rem;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    /* Standard inputs */
    .input-group-modern .form-control-modern {
      border: none;
      background: transparent;
      padding: 0.85rem 0.5rem;
      flex: 1;
      /* Key fix: allows input/select to fill space */
      width: 100%;
      min-width: 0;
      /* Prevents flex overflow issues */
      color: #495057;
      outline: none;
      font-weight: 500;
      font-size: 0.95rem;
      line-height: 1.5;
    }

    /* Ensure Bootstrap form-select inside our wrapper behaves correctly */
    .input-group-modern .form-select {
      font-weight: 500;
      color: #495057;
      font-size: 0.95rem;
      cursor: pointer;
    }

    .input-group-modern .form-control-modern::placeholder {
      color: #ced4da;
      font-weight: 400;
    }

    .label-premium {
      font-size: 0.75rem;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      color: #8898aa;
      font-weight: 700;
      margin-bottom: 0.5rem;
      display: block;
    }
  </style>
@endpush

@section('content')
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-lg-9 col-xl-8">
        <div class="card settings-card animate-reveal">

          <div class="settings-header">
            <div class="header-content text-center">
              <h1 class="settings-title"><i class="bi bi-gear-wide-connected me-2"></i> Account Settings</h1>
              <p class="mb-3 opacity-90">Manage your profile and family configuration</p>

              <div class="d-flex flex-wrap justify-content-center mt-4">
                <div class="info-badge">
                  <span class="info-label">Account ID</span>
                  <span class="info-value">{{ $accountData['id'] }}</span>
                </div>
                <div class="info-badge">
                  <span class="info-label">Family Code</span>
                  <span class="info-value">{{ $accountData['famCode'] }}</span>
                </div>
                <div class="info-badge">
                  <span class="info-label">Name</span>
                  <span class="info-value" id="fName">{{ $_SESSION['fullName'] }}</span>
                </div>
              </div>
            </div>
          </div>

          <div class="card-body p-4 p-md-5">

            <div id="setLoader" class="text-center my-4" style="display: none;">
              <div class="spinner-grow text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
            </div>

            <div class="alert alert-danger shadow-sm border-0 rounded-3" id="accountSettingForm_notification"
              style="display: none;">
              <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-octagon-fill fs-4 me-3"></i>
                <p id="accountSettingForm_notification_error" class="mb-0"></p>
              </div>
            </div>

            <form class="accountSettingForm styleform_form" id="accountSettingForm" enctype="multipart/form-data">

              <!-- Personal Information -->
              <div class="section-heading mt-2">
                <i class="bi bi-person-lines-fill"></i>
                <h4 class="mb-0">Personal Information</h4>
              </div>

              <div class="row g-4">
                <div class="col-md-6">
                  <div class="form-group-modern">
                    <label for="mobile" class="form-label">Mobile Number</label>
                    <div class="input-group">
                      <span class="input-group-text"><i class="bi bi-phone"></i></span>
                      <input type="text" class="form-control" id="mobile" name="mobile"
                        value="{{ $accountData['mobile'] }}">
                    </div>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group-modern">
                    <label for="email" class="form-label">Email Address</label>
                    <div class="input-group">
                      <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                      <input type="email" class="form-control" id="email" name="email"
                        value="{{ $accountData['email'] }}">
                    </div>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group-modern">
                    <label for="country" class="form-label">Country</label>
                    <div class="input-group">
                      <span class="input-group-text"><i class="bi bi-globe-americas"></i></span>
                      <input type="text" class="form-control" id="country" name="country"
                        value="{{ $accountData['country'] }}">
                    </div>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group-modern">
                    <label for="occupation" class="form-label">Occupation</label>
                    <div class="input-group">
                      <span class="input-group-text"><i class="bi bi-briefcase"></i></span>
                      <input type="text" class="form-control" id="occupation" name="occupation"
                        value="{{ $accountData['occupation'] }}">
                    </div>
                  </div>
                </div>
              </div>

              <div class="section-divider"></div>

              <!-- Family Configuration -->
              <div class="section-heading">
                <i class="bi bi-people-fill"></i>
                <h4 class="mb-0">Family Configuration</h4>
              </div>

              <div class="row g-4">
                <div class="col-md-6">
                  <div class="form-group-modern">
                    <label for="children" class="form-label">Add Children</label>
                    <select class="form-select" id="children" name="children">
                      <option value="0" selected>0 Children</option>
                      @for ($i = 1; $i <= 10; $i++)
                        <option value="{{ $i }}">{{ $i }} {{ $i === 1 ? 'Child' : 'Children' }}</option>
                      @endfor
                    </select>
                    <div id="children_help" class="form-text text-danger mt-2 fw-bold small"></div>
                    <div id="children_div" class="mt-3"></div>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group-modern">
                    <label for="sibling" class="form-label">Add Siblings</label>
                    <select class="form-select" id="sibling" name="sibling">
                      <option value="0" selected>0 Siblings</option>
                      @for ($i = 1; $i <= 10; $i++)
                        <option value="{{ $i }}">{{ $i }} {{ $i === 1 ? 'Sibling' : 'Siblings' }}</option>
                      @endfor
                    </select>
                    <div id="sibling_help" class="form-text text-danger mt-2 fw-bold small"></div>
                    <div id="sibling_div" class="mt-3"></div>
                  </div>
                </div>
              </div>

              <div class="form-group-modern mt-3">
                <label for="maritalStatus" class="form-label">Marital Status</label>
                <select class="form-select" id="maritalStatus" name="maritalStatus">
                  <option disabled selected>Select status...</option>
                  <option value="Yes - Add Husband">Yes - Add Husband</option>
                  <option value="Yes - Add Wife">Yes - Add Wife</option>
                </select>
              </div>

              <!-- Dynamic Spouse Section -->
              <div id="spouse" style="display: none;" class="bg-light p-4 rounded-3 border mt-3 animate-reveal">
                <h6 class="fw-bold mb-4 text-primary"><i class="bi bi-heart-fill me-2"></i>Spouse Details</h6>
                <div class="row g-3">
                  <div class="col-md-6">
                    <label for="spouse_name" class="form-label">Spouse's Name</label>
                    <input type="text" class="form-control" id="spouse_name" name="spouse_name" placeholder="Enter name">
                  </div>
                  <div class="col-md-6">
                    <label for="spouse_email" class="form-label">Spouse's Email</label>
                    <input type="email" class="form-control" id="spouse_email" name="spouse_email"
                      placeholder="Enter email">
                  </div>
                  <div class="col-md-6">
                    <label for="spouse_mobile" class="form-label">Spouse's Mobile</label>
                    <input type="text" class="form-control" id="spouse_mobile" name="spouse_mobile"
                      placeholder="Enter mobile number">
                  </div>

                  <div class="col-md-6" id="maiden_name_div" style="display: none;">
                    <label for="maiden_name" class="form-label">Maiden Name</label>
                    <input type="text" class="form-control" id="maiden_name" name="maiden_name"
                      placeholder="Enter maiden name">
                  </div>
                </div>
              </div>

              <div class="d-flex justify-content-end mt-5">
                <button type="button" id="button" class="btn btn-submit">
                  <span>Save Changes</span>
                  <i class="bi bi-arrow-right-circle ms-2"></i>
                </button>
              </div>

              {{-- Hidden CSRF / Token --}}
              <input type="hidden" name="token" value="{{ $_SESSION['token'] ?? '' }}">

            </form>
          </div>
        </div>

        <div class="text-center mt-4 text-muted small">
          <p>&copy; {{ date('Y') }} {{ $_ENV['APP_NAME'] ?? 'Family Platform' }}. All rights reserved.</p>
        </div>
      </div>
    </div>
  </div>
@endsection