<nav class="navbar navbar-expand-lg public-navbar fixed-top">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center" href="/">
      <img src="{{ getenv('APP_LOGO') }}" alt="Logo" class="me-2">
      <span style="font-weight: 800; letter-spacing: -0.02em; font-size: 1.25rem; font-family: var(--font-heading, 'Outfit', sans-serif);"><span style="color: #2a0a0d;">Family</span><span style="color: var(--brand-primary);">Platform</span></span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
      <ul class="navbar-nav align-items-center">
        <li class="nav-item">
          <a class="nav-link {{ ($_SERVER['REQUEST_URI'] ?? '') == '/' ? 'active' : '' }}" href="/">{{ __('nav.home', 'Home') }}</a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ ($_SERVER['REQUEST_URI'] ?? '') == '/aboutus' ? 'active' : '' }}" href="/aboutus">{{ __('nav.about_us', 'About Us') }}</a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/why-familyplatform') ? 'active' : '' }}" href="/why-familyplatform">
            <i class="fas fa-shield-alt me-1" style="color: var(--brand-primary, #a31621);"></i>{{ __('nav.why_us', 'Why Us vs WhatsApp') }}
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/playbook') ? 'active' : '' }}" href="/playbook">
            <i class="fas fa-book-open me-1" style="color: var(--brand-primary, #a31621);"></i>{{ __('nav.playbook', 'Playbook') }}
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/blog') ? 'active' : '' }}" href="/blogs">
            <i class="fas fa-newspaper me-1" style="color: var(--brand-primary, #a31621);"></i>{{ __('nav.blog', 'Blog') }}
          </a>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle @if(str_contains($_SERVER['REQUEST_URI'] ?? '', 'calculator') || str_contains($_SERVER['REQUEST_URI'] ?? '', 'prompts')) active @endif" href="#" id="publicToolsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-toolbox me-1" style="color: var(--brand-primary, #a31621);"></i>Free Tools
          </a>
          <ul class="dropdown-menu shadow-lg border-0 py-2 rounded-3" aria-labelledby="publicToolsDropdown">
            <li>
              <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="/kinship-calculator">
                <i class="fas fa-sitemap text-danger"></i>
                <span>Kinship Calculator</span>
              </a>
            </li>
            <li>
              <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="/memory-prompts">
                <i class="fas fa-microphone text-danger"></i>
                <span>Oral History Studio</span>
              </a>
            </li>
            <li>
              <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="/prompts">
                <i class="fas fa-book-reader text-danger"></i>
                <span>Family Archive Guides</span>
              </a>
            </li>
          </ul>
        </li>
@if(\class_exists('\Src\functionality\SignIn') && \Src\functionality\SignIn::isLoggedIn('users'))
        <li class="nav-item ms-lg-3 mt-3 mt-lg-0">
          <a class="btn btn-brand w-100" href="/profilePage">{{ __('nav.dashboard', 'Go to Family Dashboard') }}</a>
        </li>
        <li class="nav-item ms-lg-2 mt-2 mt-lg-0">
          <a class="btn btn-brand-outline w-100" href="/signout">{{ __('nav.logout', 'Sign Out') }}</a>
        </li>
@else
        <li class="nav-item ms-lg-3 mt-3 mt-lg-0 loginNav signup_login">
          <a class="btn btn-brand-outline w-100" href="/login">{{ __('nav.login', 'Log In') }}</a>
        </li>
        <li class="nav-item ms-lg-2 mt-2 mt-lg-0 registerNav signup_login">
          <a class="btn btn-brand w-100" href="/register">{{ __('nav.join_now', 'Join Now') }}</a>
        </li>
@endif
        <!-- Language Selector Dropdown -->
        <li class="nav-item dropdown ms-lg-2 mt-2 mt-lg-0">
          @php
            $activeLocale = \App\classes\I18n::getLocale();
            $supportedLocales = \App\classes\I18n::getSupportedLocales();
            $currLocale = $supportedLocales[$activeLocale] ?? $supportedLocales['en'];
          @endphp
          <button class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1 px-2 py-1 rounded-pill" type="button" id="publicLangMenu" data-bs-toggle="dropdown" aria-expanded="false" title="{{ __('nav.change_language', 'Change Language') }}">
            <span>{{ $currLocale['flag'] }}</span>
            <span class="fw-semibold text-uppercase" style="font-size: 0.78rem;">{{ $activeLocale }}</span>
          </button>
          <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 py-1" aria-labelledby="publicLangMenu" style="border-radius: 12px; min-width: 140px;">
            @foreach ($supportedLocales as $locKey => $locMeta)
              <li>
                <a class="dropdown-item py-1.5 px-3 d-flex align-items-center gap-2 {{ $locKey === $activeLocale ? 'active fw-bold' : '' }}" href="/locale/{{ $locKey }}">
                  <span>{{ $locMeta['flag'] }}</span>
                  <span>{{ $locMeta['native'] }}</span>
                </a>
              </li>
            @endforeach
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>
