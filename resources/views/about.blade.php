@extends('layouts.landing_layout')

@section('title', 'About Us')
@section('data-page-id', 'about')

@section('content')
  <!-- Header Banner -->
  <header class="doc-center-header">
    <div class="container">
      <h1>About Our Family Network</h1>
      <p class="fs-5 text-white-50">Bridging distances, preserving legacies, and keeping families connected.</p>
    </div>
  </header>

  <!-- Content Container -->
  <main class="container">
    <div class="doc-center-content">
      
      <p class="lead fw-bold text-dark">We have developed a premium platform specifically designed for families to connect and keep their family trees dynamically updated. Our platform combines the best features of modern social media with an absolute focus on family privacy and deep connections.</p>
      
      <hr class="my-5">

      <h2>Why We Built This</h2>
      <p>In an age of public feeds and data harvesting, families need a private sanctuary. Our Family Network provides a secure, ad-free environment where your family's data, photos, and history belong strictly to you.</p>

      <h2>Core Features</h2>
      
      <h3><span style="color: var(--brand-primary);">01.</span> Family Profiles</h3>
      <p>Each family member creates their own detailed profile, providing information such as date of birth, contact details, and their exact relationship to the family tree. Profiles serve as a living directory for your entire extended family.</p>

      <h3><span style="color: var(--brand-primary);">02.</span> Dynamic Family Tree (Organogram)</h3>
      <p>Visualize and explore your family connections with our interactive organogram. See exactly how generations link together, making it easy for younger members to understand their heritage.</p>

      <h3><span style="color: var(--brand-primary);">03.</span> Private News Feed</h3>
      <p>A central hub free from algorithms and strangers. Share life updates, photos, and milestones securely. It ensures everyone stays informed about the moments that matter without broadcasting them to the public internet.</p>

      <h3><span style="color: var(--brand-primary);">04.</span> Robust Privacy Controls</h3>
      <p>Privacy is our foundational pillar. We utilize granular controls so you can choose exactly who sees what. Sensitive information remains locked securely within your approved family circle.</p>

      <h3><span style="color: var(--brand-primary);">05.</span> Photo Vault</h3>
      <p>A dedicated space for preserving precious memories. Upload and organize family photos so future generations can reminisce and celebrate your family's legacy together.</p>

      <hr class="my-5">

      <div class="text-center">
        <h3>Ready to start building your network?</h3>
        <a href="/register" class="btn btn-brand btn-lg mt-3">Create Your Family Code</a>
      </div>

    </div>
  </main>
@endsection







