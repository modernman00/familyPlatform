<nav class="navbar navbar-expand-lg public-navbar fixed-top">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center" href="/">
      <img src="{{ getenv('APP_LOGO') }}" alt="Logo" class="me-2">
      <span class="fw-bold" style="color: var(--brand-primary); font-family: 'Inter', sans-serif;">Family Platform</span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
      <ul class="navbar-nav align-items-center">
        <li class="nav-item">
          <a class="nav-link {{ $_SERVER['REQUEST_URI'] == '/' ? 'active' : '' }}" href="/">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ $_SERVER['REQUEST_URI'] == '/aboutus' ? 'active' : '' }}" href="/aboutus">About Us</a>
        </li>
        <li class="nav-item ms-lg-3 mt-3 mt-lg-0 loginNav signup_login">
          <a class="btn btn-brand-outline w-100" href="/login">Log In</a>
        </li>
        <li class="nav-item ms-lg-2 mt-2 mt-lg-0 registerNav signup_login">
          <a class="btn btn-brand w-100" href="/register">Join Now</a>
        </li>
      </ul>
    </div>
  </div>
</nav>
