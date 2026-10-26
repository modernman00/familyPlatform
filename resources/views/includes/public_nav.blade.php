<nav class="navbar navbar-expand-lg public-navbar fixed-top">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center" href="/">
      <img src="{{ getenv('APP_LOGO') }}" alt="Logo" class="me-2">
      <span style="font-weight: 800; letter-spacing: -0.02em; font-size: 1.25rem; font-family: 'Inter', sans-serif;"><span style="color: #2a0a0d;">Family</span><span style="color: var(--brand-primary);">Platform</span></span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
      <ul class="navbar-nav align-items-center">
        <li class="nav-item">
          <a class="nav-link {{ ($_SERVER['REQUEST_URI'] ?? '') == '/' ? 'active' : '' }}" href="/">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ ($_SERVER['REQUEST_URI'] ?? '') == '/aboutus' ? 'active' : '' }}" href="/aboutus">About Us</a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/why-familyplatform') ? 'active' : '' }}" href="/why-familyplatform">
            <i class="fas fa-shield-alt me-1" style="color: var(--brand-primary, #a31621);"></i>Why Us vs WhatsApp
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/playbook') ? 'active' : '' }}" href="/playbook">
            <i class="fas fa-book-open me-1" style="color: var(--brand-primary, #a31621);"></i>Playbook
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/blog') ? 'active' : '' }}" href="/blogs">
            <i class="fas fa-newspaper me-1" style="color: var(--brand-primary, #a31621);"></i>Blog
          </a>
        </li>
@if(\class_exists('\Src\functionality\SignIn') && \Src\functionality\SignIn::isLoggedIn('users'))
        <li class="nav-item ms-lg-3 mt-3 mt-lg-0">
          <a class="btn btn-brand w-100" href="/profilePage">Go to Family Dashboard</a>
        </li>
        <li class="nav-item ms-lg-2 mt-2 mt-lg-0">
          <a class="btn btn-brand-outline w-100" href="/signout">Sign Out</a>
        </li>
@else
        <li class="nav-item ms-lg-3 mt-3 mt-lg-0 loginNav signup_login">
          <a class="btn btn-brand-outline w-100" href="/login">Log In</a>
        </li>
        <li class="nav-item ms-lg-2 mt-2 mt-lg-0 registerNav signup_login">
          <a class="btn btn-brand w-100" href="/register">Join Now</a>
        </li>
@endif
      </ul>
    </div>
  </div>
</nav>
