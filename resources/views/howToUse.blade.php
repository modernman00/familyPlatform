@extends('layouts.landing_layout')

@section('title', 'Living Product Guide — How to Use FamilyPlatform')
@section('data-page-id', 'how-to-use')

@section('content')
  <!-- Header Banner -->
  <header class="doc-center-header" style="background: linear-gradient(135deg, #004182 0%, #00bfa5 100%); padding: 60px 0; color: #ffffff; text-align: center;">
    <div class="container">
      <h1 class="display-5 fw-bold mb-2">Living Product Guide</h1>
      <p class="fs-5 text-white-50 mb-0">Master your family tree, preserve oral heirlooms, and connect generations.</p>
    </div>
  </header>

  <!-- Content Container -->
  <main class="container py-5">
    <div class="row justify-content-center">
      <div class="col-lg-10">

        <!-- Pillar 1: 7-Generation Dynasty Navigation -->
        <div class="card border-0 shadow-sm rounded-4 mb-5 overflow-hidden">
          <div class="card-body p-4 p-md-5">
            <div class="d-flex align-items-center gap-3 mb-3">
              <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: rgba(0, 191, 165, 0.12); color: #00bfa5;">
                <i class="bi bi-diagram-3-fill fs-4"></i>
              </div>
              <div>
                <h3 class="fw-bold mb-0 text-dark">1. Navigating Your 7-Generation Dynasty Tree</h3>
                <span class="badge bg-teal text-white" style="background: #00bfa5;">Core Architecture</span>
              </div>
            </div>

            <p class="lead text-secondary">
              FamilyPlatform provides a panoramic, ego-centric organogram canvas rendering up to 7 generations upwards (-1 to -7) and downwards (+1 to +7) with complete collateral and spouse integration.
            </p>

            <div class="row g-4 mt-2">
              <div class="col-md-6">
                <div class="p-3 rounded-3 bg-light border h-100">
                  <h6 class="fw-bold text-dark"><i class="bi bi-arrow-up-circle-fill text-primary me-2"></i>Upwards Ancestry (-1 to -7)</h6>
                  <p class="small text-muted mb-0">
                    Traverse from Parents (-1) through Grandparents (-2), Great-Grandparents (-3), up to 5th Great-Grandparents (-7). Both paternal and maternal unions are rendered symmetrically without node overlap.
                  </p>
                </div>
              </div>
              <div class="col-md-6">
                <div class="p-3 rounded-3 bg-light border h-100">
                  <h6 class="fw-bold text-dark"><i class="bi bi-arrow-down-circle-fill text-success me-2"></i>Downwards Descendants (+1 to +7)</h6>
                  <p class="small text-muted mb-0">
                    Explore your Children (+1), Grandchildren (+2), Great-Grandchildren (+3), down to 5th Great-Grandchildren (+7), including all partners, spouses, and collateral sibling downlines.
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Pillar 2: Kinship Auto-Matching Resolution -->
        <div class="card border-0 shadow-sm rounded-4 mb-5 overflow-hidden">
          <div class="card-body p-4 p-md-5">
            <div class="d-flex align-items-center gap-3 mb-3">
              <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: rgba(59, 130, 246, 0.12); color: #3b82f6;">
                <i class="bi bi-person-check-fill fs-4"></i>
              </div>
              <div>
                <h3 class="fw-bold mb-0 text-dark">2. Intelligent Kinship Auto-Matching ("Is This You?")</h3>
                <span class="badge bg-primary">New in v9.2</span>
              </div>
            </div>

            <p class="lead text-secondary">
              When a relative registers independently or readjusts their Family Code, our multi-vector entity resolution engine automatically pairs them with their position on the live tree.
            </p>

            <div class="alert alert-info border-0 rounded-3 mb-4" style="background: rgba(59, 130, 246, 0.08);">
              <h6 class="fw-bold text-primary mb-1"><i class="bi bi-magic me-2"></i>How the Auto-Matching Engine Works:</h6>
              <ul class="mb-0 small text-dark ps-3">
                <li><strong>Yoruba & Anglo Diminutive AI:</strong> Recognizes nicknames like <em>Femi &harr; Olufemi</em>, <em>Wale &harr; Adewale</em>, <em>Segun &harr; Olusegun</em>, and <em>Kenny &harr; Kehinde</em>.</li>
                <li><strong>Kinship Context Prompt:</strong> Displays the exact family relation: <em>"We found Femi Olaogun (Brother of Wale • Son of Adesanya & Iyabo). Is this you?"</em></li>
                <li><strong>Single-Click Grafting:</strong> If you already added children or a spouse, clicking <strong>"Yes, Connect My Account"</strong> automatically grafts your entire subtree onto the family tree without duplication.</li>
                <li><strong>Adversarial IDOR & Concurrency Guards:</strong> Row-level pessimistic locks (<code>FOR UPDATE</code>) and tenant isolation guarantee zero duplicate claims or cross-family access.</li>
              </ul>
            </div>
          </div>
        </div>

        <!-- Pillar 3: Living Voice Capsules -->
        <div class="card border-0 shadow-sm rounded-4 mb-5 overflow-hidden">
          <div class="card-body p-4 p-md-5">
            <div class="d-flex align-items-center gap-3 mb-3">
              <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: rgba(245, 158, 11, 0.12); color: #f59e0b;">
                <i class="bi bi-mic-fill fs-4"></i>
              </div>
              <div>
                <h3 class="fw-bold mb-0 text-dark">3. Living Voice Capsules (Oral Heritage)</h3>
                <span class="badge bg-warning text-dark">Audio Preservation</span>
              </div>
            </div>

            <p class="lead text-secondary">
              Photographs show how ancestors looked; voice capsules preserve who they were. Attach high-fidelity spoken stories directly to any family node.
            </p>

            <ol class="text-secondary ps-3 mb-4">
              <li class="mb-2">Click on any relative's card on the family tree canvas.</li>
              <li class="mb-2">Click <strong>"Record / Upload Voice Capsule"</strong> in the slide-out dossier.</li>
              <li class="mb-2">Record oral histories, lullabies, ancestral blessings, or life advice.</li>
              <li>Recordings are encrypted and streamable exclusively by authenticated family members.</li>
            </ol>
          </div>
        </div>

        <!-- Pillar 4: Multi-Touch Onboarding Lifecycle -->
        <div class="card border-0 shadow-sm rounded-4 mb-5 overflow-hidden">
          <div class="card-body p-4 p-md-5">
            <div class="d-flex align-items-center gap-3 mb-3">
              <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: rgba(16, 185, 129, 0.12); color: #10b981;">
                <i class="bi bi-envelope-heart-fill fs-4"></i>
              </div>
              <div>
                <h3 class="fw-bold mb-0 text-dark">4. Family Code Sharing & Lifecycle Flywheel</h3>
                <span class="badge bg-success">Automated Growth</span>
              </div>
            </div>

            <p class="lead text-secondary">
              Every family is assigned a unique, prestigious <strong>Family Code</strong>. Sharing this code is the fastest way to assemble your dynasty.
            </p>

            <div class="row g-3">
              <div class="col-md-4">
                <div class="p-3 border rounded-3 bg-light h-100">
                  <span class="badge bg-teal text-white mb-2" style="background: #00bfa5;">Touch 1 (Day 1)</span>
                  <h6 class="fw-bold text-dark">Lineage Kickstart</h6>
                  <p class="small text-muted mb-0">Helpful tips via Email and WebPush guiding new members to add parents and siblings in under 60 seconds.</p>
                </div>
              </div>
              <div class="col-md-4">
                <div class="p-3 border rounded-3 bg-light h-100">
                  <span class="badge bg-warning text-dark mb-2">Touch 2 (Day 3)</span>
                  <h6 class="fw-bold text-dark">Voice Heirlooms</h6>
                  <p class="small text-muted mb-0">Curated interview questions to ask elders, preserving authentic oral stories before memories fade.</p>
                </div>
              </div>
              <div class="col-md-4">
                <div class="p-3 border rounded-3 bg-light h-100">
                  <span class="badge bg-primary mb-2">Touch 3 (Day 7)</span>
                  <h6 class="fw-bold text-dark">WhatsApp Flywheel</h6>
                  <p class="small text-muted mb-0">One-click WhatsApp invite generator enabling relatives to join and claim their spot on your family tree.</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Back to Organogram CTA -->
        <div class="text-center mt-5">
          <a href="/organogram" class="btn btn-lg fw-bold px-5 py-3 text-white shadow-sm" style="background: linear-gradient(135deg, #00bfa5 0%, #004182 100%); border-radius: 50px;">
            <i class="bi bi-diagram-3-fill me-2"></i>Explore Your Family Tree
          </a>
        </div>

      </div>
    </div>
  </main>
@endsection
