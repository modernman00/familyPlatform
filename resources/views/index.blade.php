@extends('layouts.landing_layout')

@section('title', 'FamilyPlatform | Private Family Social Network & Living Heritage Tree')
@section('meta_description', 'The private, secure social platform built exclusively for families. Preserve precious memories, build interactive family trees, and stay connected with relatives without ads.')
@section('data-page-id', 'home')

@section('content')
  <!-- Hero Section -->
  <section class="hero-section">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-lg-6 mb-5 mb-lg-0 pe-lg-5">
          <div class="badge rounded-pill px-3 py-2 mb-3 fw-bold text-uppercase tracking-wider" style="background: rgba(99, 102, 241, 0.12); color: var(--brand-primary, #4f46e5); font-size: 0.8rem; letter-spacing: 0.05em;">
            🛡️ 100% Private
          </div>
          <h1 class="hero-title">Private Family Social Network &amp; <br><span>Living Heritage Tree.</span></h1>
          <p class="hero-subtitle">Connect generations, preserve precious memories, and celebrate life milestones in a secure, private haven designed exclusively for your family.</p>
          <div class="d-flex gap-3 flex-column flex-sm-row">
@if(\class_exists('\Src\functionality\SignIn') && \Src\functionality\SignIn::isLoggedIn('users'))
            <a href="/profilePage" class="btn btn-brand btn-lg shadow-sm">Go to Family Dashboard</a>
@else
            <a href="/register" class="btn btn-brand btn-lg shadow-sm">Create Family Network</a>
            <a href="/login" class="btn btn-brand-outline btn-lg">Log In</a>
@endif
          </div>
          <div class="d-flex align-items-center gap-4 mt-4 pt-2 text-muted small">
            <div class="d-flex align-items-center gap-1">
              <i class="bi bi-shield-check text-success"></i> End-to-End Private
            </div>
            <div class="d-flex align-items-center gap-1">
              <i class="bi bi-heart-pulse-fill text-danger"></i> Milestone Memories
            </div>
            <div class="d-flex align-items-center gap-1">
              <i class="bi bi-diagram-3-fill text-primary"></i> Living Organogram
            </div>
          </div>
        </div>
        <div class="col-lg-6 text-center">
          <img src="/public/img/hero-family.png" alt="Family Connection Network" class="hero-image img-fluid">
        </div>
      </div>
    </div>
  </section>

  <!-- Features Section -->
  <section class="features-section">
    <div class="container">
      <div class="row text-center mb-5">
        <div class="col-12">
          <h2 class="fw-bold fs-1" style="color: var(--text-main);">Everything your family needs</h2>
          <p class="text-muted fs-5 mt-2">Built from the ground up to keep your family connected, no matter the distance.</p>
        </div>
      </div>
      
      <div class="row g-4">
        <div class="col-md-4">
          <div class="feature-card">
            <div class="feature-icon">
              <!-- SVG icon for Network -->
              <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
            </div>
            <h3 class="feature-title">Private Organograms</h3>
            <p class="text-muted">Visualize your family history with dynamic family trees. See exactly how everyone is connected across generations.</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="feature-card">
            <div class="feature-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
            </div>
            <h3 class="feature-title">Secure Photo Sharing</h3>
            <p class="text-muted">Upload and share your most precious memories in a space where only your approved family members can see them.</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="feature-card">
            <div class="feature-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
            </div>
            <h3 class="feature-title">Absolute Privacy</h3>
            <p class="text-muted">Unlike public social media, your data is yours. We don't sell your information or track you across the web. Period.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- FAQ Section -->
  <section class="py-5" style="background-color: #f8fafc; border-top: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0;">
    <div class="container py-4" style="max-width: 860px;">
      <div class="text-center mb-5">
        <div class="badge rounded-pill px-3 py-2 mb-2 fw-bold text-uppercase tracking-wider" style="background: rgba(16, 185, 129, 0.12); color: #059669; font-size: 0.8rem;">
          Frequently Asked Questions
        </div>
        <h2 class="fw-bold fs-1" style="color: var(--text-main);">Everything You Need to Know</h2>
        <p class="text-muted fs-5 mt-2">Clear, straightforward answers about privacy, family coordination, and features.</p>
      </div>

      <div class="d-flex flex-column gap-3" itemscope itemtype="https://schema.org/FAQPage">
        <details class="p-4 rounded-3 border bg-white shadow-sm" style="border-color: #e2e8f0;" itemscope itemprop="mainEntity" itemtype="https://schema.org/Question">
          <summary class="fw-bold fs-5 text-dark cursor-pointer d-flex justify-content-between align-items-center" style="min-height: 44px; list-style: none;">
            <span itemprop="name">How does FamilyPlatform keep our family coordination private?</span>
            <span class="text-muted ms-3 fs-6">▼</span>
          </summary>
          <div class="mt-3 text-secondary lh-lg" itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
            <div itemprop="text">Unlike ad-supported social networks, FamilyPlatform operates in a private, encrypted family vault. Your family photos, medical reminders, and schedules are never sold, tracked, or indexed by search engines.</div>
          </div>
        </details>

        <details class="p-4 rounded-3 border bg-white shadow-sm" style="border-color: #e2e8f0;" itemscope itemprop="mainEntity" itemtype="https://schema.org/Question">
          <summary class="fw-bold fs-5 text-dark cursor-pointer d-flex justify-content-between align-items-center" style="min-height: 44px; list-style: none;">
            <span itemprop="name">Can grandparents and kids use FamilyPlatform easily?</span>
            <span class="text-muted ms-3 fs-6">▼</span>
          </summary>
          <div class="mt-3 text-secondary lh-lg" itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
            <div itemprop="text">Absolutely. The interface features large high-contrast touch targets, one-tap voice notes, and simplified senior-friendly views so everyone from age 7 to 85 stays connected without technological friction.</div>
          </div>
        </details>

        <details class="p-4 rounded-3 border bg-white shadow-sm" style="border-color: #e2e8f0;" itemscope itemprop="mainEntity" itemtype="https://schema.org/Question">
          <summary class="fw-bold fs-5 text-dark cursor-pointer d-flex justify-content-between align-items-center" style="min-height: 44px; list-style: none;">
            <span itemprop="name">How does the shared family calendar sync with Google and Apple calendars?</span>
            <span class="text-muted ms-3 fs-6">▼</span>
          </summary>
          <div class="mt-3 text-secondary lh-lg" itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
            <div itemprop="text">FamilyPlatform provides two-way iCal synchronization, automatically reflecting school events, doctor appointments, and family dinners directly into your phone’s native calendar.</div>
          </div>
        </details>

        <details class="p-4 rounded-3 border bg-white shadow-sm" style="border-color: #e2e8f0;" itemscope itemprop="mainEntity" itemtype="https://schema.org/Question">
          <summary class="fw-bold fs-5 text-dark cursor-pointer d-flex justify-content-between align-items-center" style="min-height: 44px; list-style: none;">
            <span itemprop="name">Is FamilyPlatform free to get started?</span>
            <span class="text-muted ms-3 fs-6">▼</span>
          </summary>
          <div class="mt-3 text-secondary lh-lg" itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
            <div itemprop="text">Yes! You can create your private family space, invite members, share photos, and build your heritage tree completely free.</div>
          </div>
        </details>
      </div>
    </div>
  </section>

  <!-- CTA Section -->
  <section class="py-5" style="background-color: var(--brand-secondary); color: white;">
    <div class="container text-center py-5">
      <h2 class="fw-bold mb-3">Ready to bring your family closer?</h2>
      <p class="fs-5 mb-4 text-white-50">Join thousands of families who have already made the switch to a better network.</p>
      <a href="/register" class="btn btn-light btn-lg px-4 px-md-5 fw-bold shadow-sm" style="color: var(--brand-secondary); border-radius: 12px;">Get Started Free</a>
    </div>
  </section>
@endsection

@section('extra_schema')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
    {
      "@type": "Question",
      "name": "How does FamilyPlatform keep our family coordination private?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Unlike ad-supported social networks, FamilyPlatform operates in a private, encrypted family vault. Your family photos, medical reminders, and schedules are never sold, tracked, or indexed by search engines."
      }
    },
    {
      "@type": "Question",
      "name": "Can grandparents and kids use FamilyPlatform easily?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Absolutely. The interface features large high-contrast touch targets, one-tap voice notes, and simplified senior-friendly views so everyone from age 7 to 85 stays connected without technological friction."
      }
    },
    {
      "@type": "Question",
      "name": "How does the shared family calendar sync with Google and Apple calendars?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "FamilyPlatform provides two-way iCal synchronization, automatically reflecting school events, doctor appointments, and family dinners directly into your phone’s native calendar."
      }
    },
    {
      "@type": "Question",
      "name": "Is FamilyPlatform free to get started?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Yes! You can create your private family space, invite members, share photos, and build your heritage tree completely free."
      }
    }
  ]
}
</script>
@endsection

