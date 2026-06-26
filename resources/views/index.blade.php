@extends('layouts.landing_layout')

@section('title', 'Connect, Share, and Grow Together')
@section('data-page-id', 'home')

@section('content')
  <!-- Hero Section -->
  <section class="hero-section">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-lg-6 mb-5 mb-lg-0 pe-lg-5">
          <h1 class="hero-title">Connect. Share. <br><span>Grow Together.</span></h1>
          <p class="hero-subtitle">The ultimate private social network designed exclusively for your family. Strengthen bonds, preserve memories, and explore your family tree in a secure, ad-free environment.</p>
          <div class="d-flex gap-3 flex-column flex-sm-row">
            <a href="/register" class="btn btn-brand btn-lg">Create Family Network</a>
            <a href="/aboutus" class="btn btn-brand-outline btn-lg">Learn How It Works</a>
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

  <!-- CTA Section -->
  <section class="py-5" style="background-color: var(--brand-secondary); color: white;">
    <div class="container text-center py-5">
      <h2 class="fw-bold mb-3">Ready to bring your family closer?</h2>
      <p class="fs-5 mb-4 text-white-50">Join thousands of families who have already made the switch to a better network.</p>
      <a href="/register" class="btn btn-light btn-lg px-5 fw-bold" style="color: var(--brand-secondary); border-radius: 24px;">Get Started Free</a>
    </div>
  </section>
@endsection

