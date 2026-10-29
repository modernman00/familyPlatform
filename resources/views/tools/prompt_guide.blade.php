@extends('layouts.landing_layout')

@section('title', ($guide['title'] ?? 'Family Memory Prompt Guide') . ' | FamilyPlatform')
@section('meta_description', $guide['meta_description'] ?? 'Oral history interview questions and memory recording guide for families.')
@section('data-page-id', 'prompt-guide-detail')

@section('content')
  <!-- Header Banner -->
  <header style="background: linear-gradient(135deg, #7a0e16 0%, #a31621 100%); padding: 50px 0; color: #ffffff; text-align: center;">
    <div class="container">
      <div class="d-flex justify-content-center align-items-center gap-2 mb-3">
        <a href="/prompts" class="text-decoration-none small d-inline-flex align-items-center gap-1 transition-colors" style="color: rgba(255,255,255,0.85); min-height: 44px; display: inline-flex; align-items: center;">
          <i class="bi bi-arrow-left"></i> All Prompt Studios
        </a>
        <span style="color: rgba(255,255,255,0.6);">/</span>
        <span class="badge rounded-pill px-3 py-1.5 fw-bold text-uppercase" style="background: rgba(255,255,255,0.22); color: #ffffff; font-size: 0.75rem; letter-spacing: 0.05em;">
          {{ $guide['category'] ?? 'Heritage' }}
        </span>
        <span class="small" style="color: rgba(255,255,255,0.85);">• {{ $guide['read_time'] ?? '5 min read' }}</span>
      </div>
      <h1 class="display-6 fw-bold mb-3 mx-auto text-white" style="max-width: 860px; line-height: 1.25;">
        {{ $guide['title'] }}
      </h1>
      <p class="fs-5 mb-0 mx-auto" style="max-width: 720px; line-height: 1.6; color: rgba(255, 255, 255, 0.92);">
        {{ $guide['summary'] }}
      </p>
    </div>
  </header>

  <!-- Main Content -->
  <main class="container py-5">
    <div class="row justify-content-center">
      <div class="col-lg-9">

        <!-- Pro-Tip Audio Callout (High Contrast & Immune to Dark-Mode Inversion) -->
        <div class="card border-0 shadow-sm rounded-4 mb-5 p-4 p-md-4" style="background: linear-gradient(135deg, #fef3c7 0%, #fef9c3 100%); border-left: 6px solid #d97706 !important;">
          <div class="d-flex align-items-start gap-3">
            <span class="fs-3 text-warning-emphasis" aria-hidden="true">💡</span>
            <div>
              <h2 class="h6 fw-bold text-uppercase mb-1" style="letter-spacing: 0.05em; color: #78350f !important;">Curator's Recording Tip</h2>
              <p class="mb-0" style="font-size: 0.95rem; line-height: 1.6; color: #92400e !important;">
                {{ $guide['audio_tip'] }}
              </p>
            </div>
          </div>
        </div>

        <!-- Question Phases -->
        <div class="space-y-5">
          @foreach ($guide['phases'] as $phaseIndex => $phase)
            <section class="card border border-slate-200 shadow-sm rounded-4 mb-4 bg-white overflow-hidden" aria-labelledby="phase-{{ $phaseIndex }}">
              <div class="card-header bg-light border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                <h3 id="phase-{{ $phaseIndex }}" class="h5 fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                  <span class="badge bg-danger rounded-pill" style="font-size: 0.75rem;">{{ $phaseIndex + 1 }}</span>
                  {{ $phase['phase_title'] }}
                </h3>
                <span class="text-secondary small fw-medium">{{ count($phase['questions']) }} prompts</span>
              </div>
              <div class="card-body p-4 p-md-4">
                <ol class="list-group list-group-flush list-group-numbered">
                  @foreach ($phase['questions'] as $qIndex => $question)
                    <li class="list-group-item d-flex justify-content-between align-items-start py-3 px-0 border-light-subtle">
                      <div class="ms-2 me-auto pe-3">
                        <p class="fw-semibold text-dark mb-1" style="font-size: 1.025rem; line-height: 1.55; color: #0f172a;">
                          {{ $question }}
                        </p>
                      </div>
                      <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1.5 d-inline-flex align-items-center gap-1.5 copy-prompt-btn transition-all shrink-0" data-question="{{ $question }}" aria-label="Copy prompt to clipboard" style="min-height: 44px; min-width: 44px;">
                        <i class="bi bi-clipboard"></i>
                        <span class="copy-text d-none d-sm-inline">Copy</span>
                      </button>
                    </li>
                  @endforeach
                </ol>
              </div>
            </section>
          @endforeach
        </div>

        <!-- High-Converting CTA Box -->
        <section class="card border-0 shadow-lg rounded-4 my-5 p-4 p-md-5 text-center text-white" style="background: linear-gradient(135deg, #111827 0%, #1f2937 100%);">
          <div class="max-w-600 mx-auto">
            <span class="badge rounded-pill bg-danger px-3 py-1.5 fw-bold text-uppercase mb-3" style="font-size: 0.75rem;">
              Preserve Forever
            </span>
            <h3 class="display-6 fw-bold mb-3 text-white">Record & Store These Audio Stories Securely</h3>
            <p class="text-slate-300 fs-5 mb-4" style="line-height: 1.6;">
              Don't leave voice recordings scattered across lost phones and WhatsApp threads. Upload them to your family's private, encrypted vault with automated voice transcription.
            </p>
            <div class="d-flex flex-column flex-sm-row justify-content-center gap-3">
              <a href="/register" class="btn btn-danger btn-lg rounded-pill px-4 py-3 fw-bold shadow-sm d-inline-flex align-items-center justify-content-center gap-2" style="min-height: 44px;">
                <i class="bi bi-shield-lock-fill"></i> Create Free Family Vault
              </a>
              <a href="/prompts" class="btn btn-outline-light btn-lg rounded-pill px-4 py-3 fw-semibold d-inline-flex align-items-center justify-content-center gap-2" style="min-height: 44px;">
                <i class="bi bi-grid-fill"></i> Explore All Prompts
              </a>
            </div>
            <p class="text-muted small mt-3 mb-0">100% private. No algorithms. Ad-free generational sanctuary.</p>
          </div>
        </section>

        <!-- Related Prompt Guides -->
        @if (!empty($related))
          <style>
            .line-clamp-3 {
              display: -webkit-box;
              -webkit-line-clamp: 3;
              -webkit-box-orient: vertical;
              overflow: hidden;
            }
          </style>
          <section class="mt-5 pt-3" aria-labelledby="related-guides-title">
            <h3 id="related-guides-title" class="h4 fw-bold text-dark mb-4">Explore More Oral History & Archive Guides</h3>
            <div class="row g-4">
              @foreach ($related as $relGuide)
                <div class="col-md-4">
                  <div class="card h-100 border border-slate-200 rounded-4 shadow-sm hover:shadow-md transition-shadow bg-white" style="border-radius: 1rem !important;">
                    <div class="card-body d-flex flex-column justify-content-between" style="padding: 1.75rem 1.75rem 2.25rem 1.75rem !important;">
                      <div>
                        <span class="badge bg-light text-danger border border-danger-subtle rounded-pill align-self-start mb-3 px-3 py-1 text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.04em; display: inline-block;">
                          {{ $relGuide['category'] }}
                        </span>
                        <h4 class="h6 fw-bold mb-2.5" style="line-height: 1.45;">
                          <a href="/prompts/{{ $relGuide['slug'] }}" class="text-decoration-none text-dark hover:text-danger stretched-link">
                            {{ $relGuide['title'] }}
                          </a>
                        </h4>
                        <p class="text-secondary small mb-0 line-clamp-3" style="font-size: 0.875rem; line-height: 1.6; padding-bottom: 0.25rem;">
                          {{ $relGuide['summary'] }}
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
              @endforeach
            </div>
          </section>
        @endif

      </div>
    </div>
  </main>

  <!-- Interactive Script for Copy Buttons -->
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const copyButtons = document.querySelectorAll('.copy-prompt-btn');
      copyButtons.forEach(button => {
        button.addEventListener('click', function () {
          const text = this.getAttribute('data-question') || '';
          if (!text) return;

          navigator.clipboard.writeText(text).then(() => {
            const copyText = this.querySelector('.copy-text');
            const icon = this.querySelector('i');
            const originalIcon = icon ? icon.className : 'bi bi-clipboard';
            const originalText = copyText ? copyText.textContent : 'Copy';

            if (icon) icon.className = 'bi bi-check2 text-success';
            if (copyText) copyText.textContent = 'Copied!';
            this.classList.add('border-success');

            setTimeout(() => {
              if (icon) icon.className = originalIcon;
              if (copyText) copyText.textContent = originalText;
              this.classList.remove('border-success');
            }, 2000);
          }).catch(err => {
            console.error('Copy failed: ', err);
          });
        });
      });
    });
  </script>

  <!-- Structured Data (JSON-LD) for Search & LLMs -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "Article",
    "headline": {!! json_encode($guide['title'] ?? '') !!},
    "description": {!! json_encode($guide['meta_description'] ?? '') !!},
    "articleSection": {!! json_encode($guide['category'] ?? '') !!},
    "publisher": {
      "@type": "Organization",
      "name": "FamilyPlatform",
      "url": "https://myfamilyplatform.com"
    }
  }
  </script>
@endsection
