@extends('layouts.landing_layout')

@section('title', '7-Generation Kinship & Relationship Degree Calculator | FamilyPlatform')
@section('meta_description', 'Calculate exact genealogical relationships, degrees of kinship, and consanguinity percentage across your family tree. Free visual kinship calculator by FamilyPlatform.')
@section('data-page-id', 'kinship-calculator-tool')

@section('content')
  <!-- Header Banner -->
  <header style="background: linear-gradient(135deg, #7a0e16 0%, #a31621 100%); padding: 50px 0; color: #ffffff; text-align: center;">
    <div class="container">
      <div class="badge rounded-pill px-3 py-2 mb-3 fw-bold text-uppercase" style="background: rgba(255,255,255,0.18); color: #fff; font-size: 0.8rem; letter-spacing: 0.05em;">
        🌳 Free Genealogical Intelligence Tool
      </div>
      <h1 class="display-5 fw-bold mb-2">7-Generation Kinship &amp; Consanguinity Calculator</h1>
      <p class="fs-5 mb-0 max-w-700 mx-auto" style="color: rgba(255, 255, 255, 0.92) !important; line-height: 1.6;">
        Wondering what a "second cousin once removed" actually means? Calculate exact relationship degrees and shared ancestry paths instantly.
      </p>
    </div>
  </header>

  <main class="container py-5">
    <div class="row justify-content-center">
      <div class="col-lg-10">

        <!-- GEO Answer Capsule -->
        <section class="card border border-light-subtle shadow-sm rounded-4 mb-4 bg-white overflow-hidden" aria-labelledby="kinship-faq">
          <div class="card-body p-4 p-md-5">
            <div class="d-flex align-items-center gap-2 mb-2 text-uppercase tracking-wider fw-bold text-danger small">
              <i class="bi bi-patch-check-fill"></i> Verified Genealogy Standard
            </div>
            <h2 id="kinship-faq" class="fw-bold fs-4 text-dark mb-3">What does "second cousin once removed" mean in a family tree?</h2>
            <p class="lead text-secondary mb-3" style="line-height: 1.7; font-size: 1.05rem;">
              <strong>"Second cousins" share the same great-grandparents. The term "once removed" means there is a one-generation difference between you: they are either the child of your second cousin, or the second cousin of your parent. They share approximately 3.125% of your DNA.</strong>
            </p>
            <div class="row g-2 pt-2">
              <div class="col-sm-4">
                <div class="p-2 px-3 rounded-3 bg-light border small text-secondary">
                  <strong>First Cousins:</strong> Share Grandparents (12.5% DNA)
                </div>
              </div>
              <div class="col-sm-4">
                <div class="p-2 px-3 rounded-3 bg-light border small text-secondary">
                  <strong>Second Cousins:</strong> Share Great-Grandparents (3.125% DNA)
                </div>
              </div>
              <div class="col-sm-4">
                <div class="p-2 px-3 rounded-3 bg-light border small text-secondary">
                  <strong>Removed:</strong> Difference in generations (+1 or -1)
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- Interactive Kinship Calculator Card -->
        <div class="card border-0 shadow-sm rounded-4 mb-5 overflow-hidden">
          <div class="card-body p-4 p-md-5">
            <h3 class="fw-bold text-dark mb-4 d-flex align-items-center gap-2">
              <i class="bi bi-diagram-3 text-danger"></i> Calculate Your Relationship
            </h3>

            <div class="row g-4 mb-4">
              <!-- Common Ancestor -->
              <div class="col-md-4">
                <label for="ancestorSelect" class="form-label fw-semibold text-dark">Closest Common Ancestor</label>
                <select id="ancestorSelect" class="form-select form-select-lg border-slate-300" style="min-height: 48px;">
                  <option value="parent">Parent (Mother / Father)</option>
                  <option value="grandparent" selected>Grandparent (Grandmother / Grandfather)</option>
                  <option value="great_grandparent">Great-Grandparent</option>
                  <option value="2nd_great_grandparent">2nd Great-Grandparent</option>
                  <option value="3rd_great_grandparent">3rd Great-Grandparent</option>
                  <option value="4th_great_grandparent">4th Great-Grandparent</option>
                </select>
              </div>

              <!-- Generation of Person 1 -->
              <div class="col-md-4">
                <label for="person1Gen" class="form-label fw-semibold text-dark">Your Generational Distance</label>
                <select id="person1Gen" class="form-select form-select-lg border-slate-300" style="min-height: 48px;">
                  <option value="1">Child (1st generation down)</option>
                  <option value="2" selected>Grandchild (2nd generation down)</option>
                  <option value="3">Great-Grandchild (3rd generation down)</option>
                  <option value="4">2nd Great-Grandchild (4th generation)</option>
                  <option value="5">3rd Great-Grandchild (5th generation)</option>
                </select>
              </div>

              <!-- Generation of Person 2 -->
              <div class="col-md-4">
                <label for="person2Gen" class="form-label fw-semibold text-dark">Relative's Generational Distance</label>
                <select id="person2Gen" class="form-select form-select-lg border-slate-300" style="min-height: 48px;">
                  <option value="1">Child (1st generation down)</option>
                  <option value="2">Grandchild (2nd generation down)</option>
                  <option value="3" selected>Great-Grandchild (3rd generation down)</option>
                  <option value="4">2nd Great-Grandchild (4th generation)</option>
                  <option value="5">3rd Great-Grandchild (5th generation)</option>
                </select>
              </div>
            </div>

            <!-- Calculation Result Surface -->
            <div id="resultBox" class="p-4 p-md-5 rounded-4 border bg-light text-center">
              <div class="badge bg-danger text-white px-3 py-1 mb-2 text-uppercase fw-bold" style="font-size: 0.75rem;">
                Relationship Output
              </div>
              <h2 id="relationshipTitle" class="display-6 fw-bold text-dark mb-2">First Cousin Once Removed</h2>
              <p id="relationshipExplanation" class="fs-5 text-secondary mb-3 max-w-600 mx-auto">
                You and this relative share common grandparents. You are a grandchild, and they are a great-grandchild (one generation apart).
              </p>
              
              <div class="d-flex justify-content-center gap-4 flex-wrap mt-4 pt-3 border-top">
                <div>
                  <div class="text-muted small text-uppercase fw-semibold">Civil Law Degree</div>
                  <div id="civilDegree" class="fs-4 fw-bold text-dark">5th Degree</div>
                </div>
                <div>
                  <div class="text-muted small text-uppercase fw-semibold">Canon Law Degree</div>
                  <div id="canonDegree" class="fs-4 fw-bold text-dark">3rd Degree</div>
                </div>
                <div>
                  <div class="text-muted small text-uppercase fw-semibold">Estimated Shared DNA</div>
                  <div id="dnaShare" class="fs-4 fw-bold text-danger">~6.25%</div>
                </div>
              </div>
            </div>

          </div>
        </div>

        <!-- High-Conversion Platform Bridge CTA -->
        <section class="card border-0 shadow rounded-4 text-white p-4 p-md-5 mb-5" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);">
          <div class="row align-items-center">
            <div class="col-lg-8 mb-4 mb-lg-0">
              <div class="badge bg-danger text-white px-3 py-1 mb-2 text-uppercase fw-bold" style="font-size: 0.75rem;">
                🌳 7-Generation Dynasty Engine
              </div>
              <h3 class="fw-bold mb-2">Build Your Complete Dynasty Tree on FamilyPlatform</h3>
              <p class="mb-0" style="color: rgba(255, 255, 255, 0.9) !important; line-height: 1.6;">
                Don't calculate relationships by hand. FamilyPlatform automatically calculates ego-centric kinship titles for every single relative in your living 7-generation organogram with zero node overlaps.
              </p>
            </div>
            <div class="col-lg-4 text-lg-end">
              <a href="/register" class="btn btn-light btn-lg px-4 py-3 fw-bold shadow-sm" style="color: #a31621; border-radius: 12px; min-height: 44px; white-space: nowrap;">
                Explore Dynasty Tree <i class="bi bi-arrow-right ms-1"></i>
              </a>
            </div>
          </div>
        </section>

      </div>
    </div>
  </main>

  <!-- Interactive Kinship Logic Script -->
  <script>
    (function() {
      const TABLE = {
        // [g1, g2] relative to common ancestor
        "1,1": { title: "Siblings (Brother / Sister)", civil: 2, canon: 1, dna: "~50.0%", expl: "You share the exact same parents (1st generation down)." },
        "1,2": { title: "Aunt / Uncle or Niece / Nephew", civil: 3, canon: 2, dna: "~25.0%", expl: "One is a child of the common ancestor, and the other is a grandchild (1 generation apart)." },
        "1,3": { title: "Great-Aunt / Great-Uncle or Grandniece / Grandnephew", civil: 4, canon: 3, dna: "~12.5%", expl: "Two generations separation from the common ancestor." },
        "1,4": { title: "Great-Great-Aunt / Uncle", civil: 5, canon: 4, dna: "~6.25%", expl: "Three generations separation from the common ancestor." },
        "1,5": { title: "3rd Great-Aunt / Uncle", civil: 6, canon: 5, dna: "~3.125%", expl: "Four generations separation from the common ancestor." },
        
        "2,1": { title: "Aunt / Uncle or Niece / Nephew", civil: 3, canon: 2, dna: "~25.0%", expl: "One is a child of the common ancestor, and the other is a grandchild." },
        "2,2": { title: "First Cousins", civil: 4, canon: 2, dna: "~12.5%", expl: "You both share the same grandparents (2nd generation down from common ancestor)." },
        "2,3": { title: "First Cousin Once Removed", civil: 5, canon: 3, dna: "~6.25%", expl: "One is a grandchild and the other is a great-grandchild of the common grandparents." },
        "2,4": { title: "First Cousin Twice Removed", civil: 6, canon: 4, dna: "~3.125%", expl: "Two generations separation between first cousins." },
        "2,5": { title: "First Cousin Thrice Removed", civil: 7, canon: 5, dna: "~1.56%", expl: "Three generations separation between first cousins." },

        "3,1": { title: "Great-Aunt / Great-Uncle", civil: 4, canon: 3, dna: "~12.5%", expl: "Two generations separation from the common ancestor." },
        "3,2": { title: "First Cousin Once Removed", civil: 5, canon: 3, dna: "~6.25%", expl: "One is a grandchild and the other is a great-grandchild." },
        "3,3": { title: "Second Cousins", civil: 6, canon: 3, dna: "~3.125%", expl: "You both share the same great-grandparents (3rd generation down)." },
        "3,4": { title: "Second Cousin Once Removed", civil: 7, canon: 4, dna: "~1.56%", expl: "One is a great-grandchild and the other is a 2nd great-grandchild." },
        "3,5": { title: "Second Cousin Twice Removed", civil: 8, canon: 5, dna: "~0.78%", expl: "Two generations separation between second cousins." },

        "4,1": { title: "Great-Great-Aunt / Uncle", civil: 5, canon: 4, dna: "~6.25%", expl: "Three generations separation from the common ancestor." },
        "4,2": { title: "First Cousin Twice Removed", civil: 6, canon: 4, dna: "~3.125%", expl: "Two generations separation between first cousins." },
        "4,3": { title: "Second Cousin Once Removed", civil: 7, canon: 4, dna: "~1.56%", expl: "One is a great-grandchild and the other is a 2nd great-grandchild." },
        "4,4": { title: "Third Cousins", civil: 8, canon: 4, dna: "~0.78%", expl: "You both share the same 2nd great-grandparents (4th generation down)." },
        "4,5": { title: "Third Cousin Once Removed", civil: 9, canon: 5, dna: "~0.39%", expl: "One is a 2nd great-grandchild and the other is a 3rd great-grandchild." },

        "5,1": { title: "3rd Great-Aunt / Uncle", civil: 6, canon: 5, dna: "~3.125%", expl: "Four generations separation from common ancestor." },
        "5,2": { title: "First Cousin Thrice Removed", civil: 7, canon: 5, dna: "~1.56%", expl: "Three generations separation between first cousins." },
        "5,3": { title: "Second Cousin Twice Removed", civil: 8, canon: 5, dna: "~0.78%", expl: "Two generations separation between second cousins." },
        "5,4": { title: "Third Cousin Once Removed", civil: 9, canon: 5, dna: "~0.39%", expl: "One is a 2nd great-grandchild and the other is a 3rd great-grandchild." },
        "5,5": { title: "Fourth Cousins", civil: 10, canon: 5, dna: "~0.20%", expl: "You both share the same 3rd great-grandparents (5th generation down)." }
      };

      function updateCalculation() {
        const g1 = document.getElementById('person1Gen').value;
        const g2 = document.getElementById('person2Gen').value;
        const key = `${g1},${g2}`;

        const data = TABLE[key] || {
          title: "Distant Relatives",
          civil: parseInt(g1) + parseInt(g2),
          canon: Math.max(parseInt(g1), parseInt(g2)),
          dna: "< 0.1%",
          expl: `Generational distance of ${g1} and ${g2} steps from the common ancestor.`
        };

        document.getElementById('relationshipTitle').innerText = data.title;
        document.getElementById('relationshipExplanation').innerText = data.expl;
        document.getElementById('civilDegree').innerText = `${data.civil}th Degree`;
        document.getElementById('canonDegree').innerText = `${data.canon}th Degree`;
        document.getElementById('dnaShare').innerText = data.dna;
      }

      document.getElementById('person1Gen').addEventListener('change', updateCalculation);
      document.getElementById('person2Gen').addEventListener('change', updateCalculation);
      document.getElementById('ancestorSelect').addEventListener('change', function() {
        // Auto adjust suggestions
        const val = this.value;
        if (val === 'parent') {
          document.getElementById('person1Gen').value = '1';
          document.getElementById('person2Gen').value = '1';
        } else if (val === 'grandparent') {
          document.getElementById('person1Gen').value = '2';
          document.getElementById('person2Gen').value = '2';
        } else if (val === 'great_grandparent') {
          document.getElementById('person1Gen').value = '3';
          document.getElementById('person2Gen').value = '3';
        }
        updateCalculation();
      });

      updateCalculation();
    })();
  </script>
@endsection

@section('extra_schema')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "WebApplication",
      "name": "7-Generation Kinship & Consanguinity Calculator",
      "applicationCategory": "EducationalApplication",
      "operatingSystem": "All",
      "url": "https://myfamilyplatform.com/tools/kinship-calculator",
      "description": "Calculate exact genealogical relationship titles, civil/canon law degrees of kinship, and shared DNA percentages.",
      "offers": {
        "@type": "Offer",
        "price": "0",
        "priceCurrency": "GBP"
      }
    }
  ]
}
</script>
@endsection
