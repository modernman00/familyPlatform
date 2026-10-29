@extends('layouts.landing_layout')

@section('title', 'Oral History & Family Journal Prompt Studio — Chronicle Your Elders | FamilyPlatform')
@section('meta_description', 'Generate sensory oral storytelling prompts to record grandparents, parents, and relatives before memories fade. Free interactive family journal tool with 1-click WhatsApp sharing.')
@section('data-page-id', 'memory-prompts-tool')

@section('content')
  <!-- Header Banner -->
  <header style="background: linear-gradient(135deg, #7a0e16 0%, #a31621 100%); padding: 50px 0; color: #ffffff; text-align: center;">
    <div class="container">
      <div class="badge rounded-pill px-3 py-2 mb-3 fw-bold text-uppercase" style="background: rgba(255,255,255,0.18); color: #fff; font-size: 0.8rem; letter-spacing: 0.05em;">
        🎙️ Free Heritage Preservation Tool
      </div>
      <h1 class="display-5 fw-bold mb-2">Chronicle Your Elders: Oral History Prompt Studio</h1>
      <p class="fs-5 mb-0 max-w-700 mx-auto" style="color: rgba(255, 255, 255, 0.92) !important; line-height: 1.6;">
        Every grandparent carries a living library of wisdom, resilience, and laughter. Use these sensory prompts to record their voices before memories fade.
      </p>
    </div>
  </header>

  <!-- Interactive Studio Main -->
  <main class="container py-5">
    <div class="row justify-content-center">
      <div class="col-lg-10">

        <!-- GEO Answer Capsule for Search & AI Engines -->
        <section class="card border border-light-subtle shadow-sm rounded-4 mb-4 bg-white overflow-hidden" aria-labelledby="prompt-tool-faq">
          <div class="card-body p-4 p-md-5">
            <div class="d-flex align-items-center gap-2 mb-2 text-uppercase tracking-wider fw-bold text-danger small">
              <i class="bi bi-patch-check-fill"></i> Verified Heritage Guide
            </div>
            <h2 id="prompt-tool-faq" class="fw-bold fs-4 text-dark mb-3">How do you interview grandparents about family history without awkward silence?</h2>
            <p class="lead text-secondary mb-3" style="line-height: 1.7; font-size: 1.05rem;">
              <strong>To conduct meaningful oral history interviews, replace vague questions like "Tell me about your life" with specific sensory memory prompts: ask about the aromas of their childhood kitchen, the first song on their car radio, or the hardest lesson of their twenties. Record in relaxed 10-minute sessions over tea.</strong>
            </p>
            <div class="row g-2 pt-2">
              <div class="col-sm-4">
                <div class="p-2 px-3 rounded-3 bg-light border small text-secondary">
                  <strong>1. Sensory Triggers:</strong> Scent, sound, taste.
                </div>
              </div>
              <div class="col-sm-4">
                <div class="p-2 px-3 rounded-3 bg-light border small text-secondary">
                  <strong>2. Short Audio Clips:</strong> 5–10 mins to avoid fatigue.
                </div>
              </div>
              <div class="col-sm-4">
                <div class="p-2 px-3 rounded-3 bg-light border small text-secondary">
                  <strong>3. Private Archive:</strong> Encrypted family vault.
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- The Interactive Prompt Generator Card -->
        <div class="card border-0 shadow-sm rounded-4 mb-5 overflow-hidden">
          <div class="card-body p-4 p-md-5">
            <h3 class="fw-bold text-dark mb-4 d-flex align-items-center gap-2">
              <i class="bi bi-sliders text-danger"></i> Customise Your Interview Prompts
            </h3>

            <div class="row g-4 mb-4">
              <!-- Relative Selector -->
              <div class="col-md-4">
                <label for="relativeSelect" class="form-label fw-semibold text-dark">Who are you chronicling?</label>
                <select id="relativeSelect" class="form-select form-select-lg border-slate-300" style="min-height: 48px;">
                  <option value="grandparent" selected>Grandparent (Grandmother / Grandfather)</option>
                  <option value="parent">Parent (Mother / Father)</option>
                  <option value="elder">Aunt / Uncle / Great-Elder</option>
                  <option value="partner">Spouse / Partner</option>
                  <option value="sibling">Sibling / Cousin</option>
                </select>
              </div>

              <!-- Theme Selector -->
              <div class="col-md-4">
                <label for="themeSelect" class="form-label fw-semibold text-dark">Storytelling Theme</label>
                <select id="themeSelect" class="form-select form-select-lg border-slate-300" style="min-height: 48px;">
                  <option value="childhood" selected>Childhood, Roots &amp; Hometown</option>
                  <option value="kitchen">Kitchen Secrets &amp; Family Recipes</option>
                  <option value="resilience">Hardships, Courage &amp; Resilience</option>
                  <option value="romance">Love, Courtship &amp; Partnerships</option>
                  <option value="wisdom">Milestone Advice &amp; Life Lessons</option>
                  <option value="humour">Mischief, Celebrations &amp; Funny Moments</option>
                </select>
              </div>

              <!-- Session Length -->
              <div class="col-md-4">
                <label for="formatSelect" class="form-label fw-semibold text-dark">Session Format</label>
                <select id="formatSelect" class="form-select form-select-lg border-slate-300" style="min-height: 48px;">
                  <option value="quick" selected>5-Minute Quick Chats (3 Prompts)</option>
                  <option value="standard">Sunday Afternoon Session (6 Prompts)</option>
                  <option value="roundtable">Dinner Table Icebreaker (5 Prompts)</option>
                </select>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex flex-wrap gap-3 mb-4">
              <button id="generateBtn" class="btn btn-brand btn-lg px-4 py-2 fw-bold shadow-sm d-flex align-items-center gap-2" style="min-height: 44px;">
                <i class="bi bi-sparkles"></i> Generate Prompts
              </button>
              <button id="copyBtn" class="btn btn-outline-secondary btn-lg px-4 py-2 fw-semibold d-flex align-items-center gap-2" style="min-height: 44px;">
                <i class="bi bi-clipboard"></i> Copy Prompts
              </button>
              <button id="whatsappBtn" class="btn btn-success btn-lg px-4 py-2 fw-semibold d-flex align-items-center gap-2" style="min-height: 44px; background-color: #25D366; border-color: #25D366;">
                <i class="bi bi-whatsapp"></i> Send via WhatsApp
              </button>
            </div>

            <!-- Generated Prompts Stream -->
            <div id="promptsContainer" class="p-4 rounded-3 border bg-light">
              <!-- Dynamically populated via JavaScript -->
            </div>

          </div>
        </div>

        <!-- High-Conversion Platform Bridge CTA -->
        <section class="card border-0 shadow rounded-4 text-white p-4 p-md-5 mb-5" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);">
          <div class="row align-items-center">
            <div class="col-lg-8 mb-4 mb-lg-0">
              <div class="badge bg-danger text-white px-3 py-1 mb-2 text-uppercase fw-bold" style="font-size: 0.75rem;">
                🔒 Sovereign Family Archive
              </div>
              <h3 class="fw-bold mb-2">Preserve These Recordings in Your Private Living Heritage Tree</h3>
              <p class="mb-0" style="color: rgba(255, 255, 255, 0.9) !important; line-height: 1.6;">
                Don't leave recorded voice notes buried in chat apps where they expire. FamilyPlatform attaches your audio capsules directly to your ancestor nodes on an encrypted 7-generation dynasty organogram.
              </p>
            </div>
            <div class="col-lg-4 text-lg-end">
              <a href="/register" class="btn btn-light btn-lg px-4 py-3 fw-bold shadow-sm" style="color: #a31621; border-radius: 12px; min-height: 44px; white-space: nowrap;">
                Create Free Family Haven <i class="bi bi-arrow-right ms-1"></i>
              </a>
            </div>
          </div>
        </section>

      </div>
    </div>
  </main>

  <!-- Client-side Interactive Script -->
  <script>
    (function() {
      const PROMPT_DATABASE = {
        grandparent: {
          childhood: [
            "What is your very earliest memory of your childhood home, and what sounds woke you up in the morning?",
            "What games did you play with your siblings or neighbourhood friends before television was in every room?",
            "Can you describe your school journey and what you usually carried in your school satchel?",
            "What was the most mischievous thing you ever did as a child, and did your parents find out?",
            "What songs or radio broadcasts bring back the most vivid memories of your youth?",
            "Who was the kindest neighbour or teacher in your hometown, and what did they teach you?"
          ],
          kitchen: [
            "What was the signature dish your mother or grandmother made that you have never tasted anywhere else?",
            "What aromas immediately take you back to your childhood kitchen during festive celebrations?",
            "Was there a secret ingredient or technique your family used that was passed down through generations?",
            "How did your family store food and preserve produce before modern refrigeration was common?",
            "What was your favourite meal to celebrate a birthday or special milestone?",
            "What meal do you wish future generations in our family would keep cooking forever?"
          ],
          resilience: [
            "What was the most difficult decade or period you lived through, and how did your family pull through?",
            "Can you tell me about a time when you were genuinely afraid or uncertain, and where you found courage?",
            "What was the hardest job you ever had to work, and what was your first real pay-packet?",
            "What historical event (a war, an economic crisis, or migration) had the biggest impact on your family?",
            "What advice would you give to our family if we ever face seasons of hardship or uncertainty?",
            "Who was your pillar of strength when times were tough?"
          ],
          romance: [
            "Where and how did you first lay eyes on your spouse or partner? What was your very first impression?",
            "What was courting or dating like in your day? Where did you go on your first outing together?",
            "What song was playing at your wedding, and what do you remember feeling as you walked down the aisle?",
            "What is the secret to staying devoted through 30, 40, or 50 years of marriage and partnership?",
            "What little quirk or habit of your partner made you laugh the most over the years?",
            "If you could relive one single day together from your twenties, which day would you pick?"
          ],
          wisdom: [
            "Looking back over your life, what is the single biggest lesson you wish you knew at age twenty-five?",
            "What matters most in life when you reach your seventies and eighties, and what matters far less than people think?",
            "What is one principle or value you hope will never be forgotten by our family's descendants?",
            "What are you most grateful for today as you look at our family tree?",
            "What was the proudest moment of your entire life?",
            "If you could leave a one-sentence message for your great-great-grandchildren, what would you say?"
          ],
          humour: [
            "What was the funniest disaster or mishap that ever happened during a family holiday or reunion?",
            "Tell me about a fashion choice or hairstyle from your youth that you look back on and laugh at today.",
            "Who in our family has always had the loudest, most infectious laughter?",
            "What was a practical joke that someone pulled in your youth that people still talk about?",
            "What was your first experience with a piece of modern technology (like a colour TV, microwave, or computer)?",
            "What family story gets told at every dinner that makes everyone burst into laughter?"
          ]
        },
        parent: {
          childhood: [
            "What were your favourite weekend traditions with us when we were little that you cherish most?",
            "What was the hardest thing about bringing us up that you never told us at the time?",
            "What did your own parents say to you that you found yourself repeating verbatim to us as children?",
            "What was our family home like in the early years before we grew up?",
            "What was the proudest moment you felt watching us take our first independent steps in the world?",
            "What childhood song or bedtime story did you love reading to us the most?"
          ],
          kitchen: [
            "What dish did you learn to cook when you first left home that sustained you through university or early work?",
            "What meal always meant 'celebration' in our household when we were growing up?",
            "Where did you buy groceries and what was the shopping ritual like in our early family years?",
            "What family recipe would you like us to master so we can make it for your grandchildren?",
            "What was the worst culinary disaster you ever had in the kitchen that we still laugh about?",
            "What Sunday lunch ritual did you love the most?"
          ],
          resilience: [
            "What was the most stressful financial or career hurdle you overcame while raising a family?",
            "How did you handle balance between work, parenting, and your own personal dreams?",
            "What was the biggest sacrifice you made for our family that you would make again in a heartbeat?",
            "What kept you going during sleepless nights or periods of illness in our family?",
            "What lesson from your parents guided you when parenting got tough?",
            "What gives you peace of mind today?"
          ],
          romance: [
            "How did you and your partner know you were ready to start a family together?",
            "What made you fall in love, and how did that love evolve once children entered the picture?",
            "What was your honeymoon or first romantic holiday like?",
            "What is your favourite private memory with each other that we might not know about?",
            "How did you navigate disagreements and keep communication healthy?",
            "What do you admire most about each other after all these years?"
          ],
          wisdom: [
            "What have you learned about human nature that you only understand once you become a parent?",
            "What do you hope we never compromise on in our careers or personal relationships?",
            "If you could go back to the day I was born, what would you whisper to your younger self?",
            "What family tradition do you hope stays alive for the next 100 years?",
            "What has been your greatest personal joy in this lifetime?",
            "What legacy do you hope our family leaves in our community?"
          ],
          humour: [
            "What was the most embarrassing thing we ever did as kids in public that mortified you at the time?",
            "What was the worst DIY disaster or home improvement accident you ever had?",
            "Which family pet caused the absolute most chaos in our household?",
            "What was the strangest birthday present you ever received?",
            "Tell me about a family road trip where everything went completely wrong.",
            "What funny nickname did your parents or siblings have for you growing up?"
          ]
        },
        elder: {
          childhood: [
            "What was our family like in the generation before me? How did the siblings interact?",
            "Who was the family storyteller when you were young, and what was their favourite tale?",
            "What ancestral town, village, or country did our family journey from?",
            "What ancestral heirlooms or photographs did your parents guard most carefully?",
            "What was our family reputation or trade in the old neighbourhood?",
            "What was Sunday dinner like with the extended family gathering around one table?"
          ],
          kitchen: [
            "What regional spices, herbs, or traditions define our ancestral food heritage?",
            "Who held the family recipes, and were they ever written down or just taught by heart?",
            "What was the holiday feast that took three days of preparation in our family home?",
            "What was the smell of the family kitchen during harvest season or festive holidays?",
            "Can you walk me step-by-step through how your mother made her signature celebration bread or stew?",
            "What food tradition should our diaspora children never lose touch with?"
          ],
          resilience: [
            "What was the greatest journey of migration or transition our family undertook?",
            "How did our elders adapt to new languages, new cities, and unfamiliar cultures?",
            "What ancestral proverbs or mottos did our elders repeat when faced with discrimination or hardship?",
            "Who was the quiet hero of our extended family whose sacrifices paved the way for everyone else?",
            "What was the hardest goodbye your family ever had to say?",
            "What made our family survive when others fragmented?"
          ],
          romance: [
            "How did marriages and partnerships get arranged or formed in your generation?",
            "What was the grandest wedding our extended family ever celebrated?",
            "Who had the most romantic love story in our family tree?",
            "What advice did your parents give you on choosing a life partner?",
            "What was the secret to keeping extended families close even when spouses came from different backgrounds?",
            "What love letter or courtship memory stands out most in your mind?"
          ],
          wisdom: [
            "Why is knowing our family tree and kinship line so important for our children and grandchildren?",
            "What ancestral roots or naming traditions should we carry forward?",
            "What mistake did past generations make that our younger generation should avoid?",
            "What does our family name mean to you when you hear it spoken?",
            "What blessing or prayer do you speak over the newest generation of babies in our family?",
            "What gives you pride when you look at our family tree today?"
          ],
          humour: [
            "Who was the most colourful, eccentric character in our family tree who marched to their own beat?",
            "What famous family feud or argument started over something completely ridiculous?",
            "What was the biggest scandal at a family wedding that people still laugh about decades later?",
            "Who had the most outrageous stories that everyone suspected were half-exaggerated?",
            "Tell me about the most chaotic family celebration you ever attended.",
            "What funny superstition did your grandmother swear by?"
          ]
        }
      };

      // Fallback for partner/sibling
      PROMPT_DATABASE.partner = PROMPT_DATABASE.parent;
      PROMPT_DATABASE.sibling = PROMPT_DATABASE.grandparent;

      function renderPrompts() {
        const rel = document.getElementById('relativeSelect').value;
        const theme = document.getElementById('themeSelect').value;
        const fmt = document.getElementById('formatSelect').value;

        const count = fmt === 'quick' ? 3 : (fmt === 'roundtable' ? 5 : 6);
        const relData = PROMPT_DATABASE[rel] || PROMPT_DATABASE.grandparent;
        const themeList = relData[theme] || relData.childhood;
        const selected = themeList.slice(0, count);

        const container = document.getElementById('promptsContainer');
        let html = '<ol class="mb-0 ps-3 list-group list-group-numbered border-0">';
        selected.forEach((p, idx) => {
          html += `
            <li class="list-group-item d-flex align-items-start gap-3 bg-transparent border-0 py-2">
              <div class="fw-semibold text-dark fs-6" style="line-height: 1.6;">${p}</div>
            </li>
          `;
        });
        html += '</ol>';
        container.innerHTML = html;
      }

      document.getElementById('generateBtn').addEventListener('click', renderPrompts);
      document.getElementById('relativeSelect').addEventListener('change', renderPrompts);
      document.getElementById('themeSelect').addEventListener('change', renderPrompts);
      document.getElementById('formatSelect').addEventListener('change', renderPrompts);

      // Copy Button
      document.getElementById('copyBtn').addEventListener('click', function() {
        const items = Array.from(document.querySelectorAll('#promptsContainer li div')).map((d, i) => `${i + 1}. ${d.innerText}`);
        const text = items.join('\n\n') + '\n\n— Generated with FamilyPlatform Oral History Studio (https://myfamilyplatform.com/prompts)';
        navigator.clipboard.writeText(text).then(() => {
          const original = this.innerHTML;
          this.innerHTML = '<i class="bi bi-check2 text-success"></i> Copied!';
          setTimeout(() => this.innerHTML = original, 2000);
        });
      });

      // WhatsApp Button
      document.getElementById('whatsappBtn').addEventListener('click', function() {
        const relText = document.getElementById('relativeSelect').options[document.getElementById('relativeSelect').selectedIndex].text;
        const items = Array.from(document.querySelectorAll('#promptsContainer li div')).map((d, i) => `${i + 1}. ${d.innerText}`);
        const text = `Hi! I was using FamilyPlatform's Oral History Studio and thought of you. I would love to record your answers to these memories when we next have tea:\n\n${items.join('\n\n')}\n\nLet me know when suits! ❤️`;
        const url = `https://api.whatsapp.com/send?text=${encodeURIComponent(text)}`;
        window.open(url, '_blank');
      });

      // Initial Render
      renderPrompts();
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
      "name": "Chronicle Your Elders: Oral History Prompt Studio",
      "applicationCategory": "EducationalApplication",
      "operatingSystem": "All",
      "url": "https://myfamilyplatform.com/tools/family-memory-prompts",
      "description": "Interactive oral history and sensory memory prompt generator for chronicling grandparents and family elders before memories fade.",
      "offers": {
        "@type": "Offer",
        "price": "0",
        "priceCurrency": "GBP"
      }
    },
    {
      "@type": "HowTo",
      "name": "How to Interview Grandparents About Family History",
      "description": "A sensory oral history interview framework for capturing family stories and childhood memories.",
      "step": [
        {
          "@type": "HowToStep",
          "name": "Select a Sensory Storytelling Theme",
          "text": "Choose a specific sensory theme such as kitchen recipes, childhood games, or milestone resilience rather than broad open-ended questions."
        },
        {
          "@type": "HowToStep",
          "name": "Conduct a Relaxed 10-Minute Audio Session",
          "text": "Record the conversation on a smartphone or FamilyPlatform voice capsule over afternoon tea in a quiet room."
        },
        {
          "@type": "HowToStep",
          "name": "Archive to Your Living Heritage Tree",
          "text": "Save the audio recordings permanently to the elder's node on your private FamilyPlatform organogram."
        }
      ]
    }
  ]
}
</script>
@endsection
