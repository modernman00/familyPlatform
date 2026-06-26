@extends('layouts.landing_layout')

@section('title', 'Contact Us')
@section('data-page-id', 'contact')

@section('content')
  <!-- Header Banner -->
  <header class="doc-center-header">
    <div class="container">
      <h1>Get in Touch</h1>
      <p class="fs-5 text-white-50">We're here to help you connect your family.</p>
    </div>
  </header>

  <!-- Content Container -->
  <main class="container">
    <div class="doc-center-content text-center">
      
      <div class="mb-5">
        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--brand-primary);"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
      </div>

      <h2 class="mb-4">How can we assist you?</h2>
      <p class="lead text-muted mb-5">Whether you have a question about setting up your family tree, privacy controls, or need technical support, our team is ready to answer all your questions.</p>

      <div class="row justify-content-center">
        <div class="col-md-6">
          <div class="card shadow-sm border-0 bg-light p-4 rounded-4 text-start">
            <h4 class="fw-bold mb-3">Support Contact Info</h4>
            
            <div class="d-flex align-items-center mb-3">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-3 text-secondary"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
              <div>
                <strong>Email Us</strong><br>
                <a href="mailto:{{ getenv('CUST_EMAIL') ?: getenv('ADMIN_EMAIL') }}" class="text-decoration-none">{{ getenv('CUST_EMAIL') ?: getenv('ADMIN_EMAIL') ?: 'enquiries@myfamilyplatform.com' }}</a>
              </div>
            </div>

            <div class="d-flex align-items-center">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-3 text-secondary"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
              <div>
                <strong>Headquarters</strong><br>
                <span class="text-muted">{{ getenv('businessAddress') ?: 'London, United Kingdom' }}</span>
              </div>
            </div>

          </div>
        </div>
      </div>

    </div>
  </main>
@endsection

