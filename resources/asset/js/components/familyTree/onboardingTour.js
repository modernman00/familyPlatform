/**
 * Interactive Onboarding App Tour Component
 * Provides a guided spotlight walkthrough for first-time members landing on the family tree.
 */

const STORAGE_KEY = 'family_app_tour_completed';
let memoryStorage = {};

const safeGetStorage = (key) => {
  try {
    return window.localStorage ? window.localStorage.getItem(key) : memoryStorage[key];
  } catch (e) {
    return memoryStorage[key] || null;
  }
};

const safeSetStorage = (key, value) => {
  try {
    if (window.localStorage) {
      window.localStorage.setItem(key, value);
    }
  } catch (e) {
    // Fallback to in-memory state
  }
  memoryStorage[key] = value;
};

const TOUR_STEPS = [
  {
    target: '#treeHeaderTitle',
    fallbackTarget: '.organogram-header',
    title: 'Welcome to Your Family Hub',
    icon: 'bi-stars',
    badge: 'Step 1 of 5',
    text: 'Your private family platform brings together your entire lineage, stories, and connections in one secure space. Let\'s take a quick 30-second look around!',
    position: 'bottom'
  },
  {
    target: '#treeContainer',
    fallbackTarget: '#tree',
    title: 'Interactive Family Tree & Heritage',
    icon: 'bi-diagram-3-fill',
    badge: 'Step 2 of 5',
    text: 'Explore your heritage dynamically. Drag to pan, scroll to zoom in/out, and click any family node to view full profiles, memories, and voice audio capsules.',
    position: 'bottom'
  },
  {
    target: '#memberSearchWrapper',
    fallbackTarget: '#memberSearchInput',
    title: 'Instant Relative Search',
    icon: 'bi-search',
    badge: 'Step 3 of 5',
    text: 'Quickly find any ancestor, sibling, child, or cousin in your tree. Typing their name instantly spotlights their position on the canvas.',
    position: 'bottom'
  },
  {
    target: '#configureFamilyBtn',
    fallbackTarget: 'a[href*="accountSetting"]',
    title: 'Add & Manage Relatives',
    icon: 'bi-person-plus-fill',
    badge: 'Step 4 of 5',
    text: 'Add your parents, partner, children, and siblings. Customize maiden names, milestones, and send invitations directly to your family members.',
    position: 'bottom'
  },
  {
    target: '#mainNavbar',
    fallbackTarget: '.navbar',
    title: 'Stories, Events & Directory',
    icon: 'bi-grid-fill',
    badge: 'Step 5 of 5',
    text: 'Use the top navigation to view the Family Feed for posts, preserve generational stories, track birthdays in Events, or connect in All Members.',
    position: 'bottom'
  }
];

class OnboardingTour {
  constructor() {
    this.currentStep = 0;
    this.overlay = null;
    this.spotlightCutout = null;
    this.spotlightRing = null;
    this.card = null;
    this.active = false;
    this.onKeyDown = this.onKeyDown.bind(this);
    this.onResize = this.onResize.bind(this);
  }

  init() {
    const isCompleted = safeGetStorage(STORAGE_KEY) === 'true';
    const hasTourParam = new URLSearchParams(window.location.search).get('tour') === '1';

    // Auto-launch if first time or explicitly requested via query parameter
    if (!isCompleted || hasTourParam) {
      setTimeout(() => this.start(), 800);
    }

    // Expose global controller for manual replay
    window.startAppTour = () => this.start();
  }

  start() {
    if (this.active) return;
    this.currentStep = 0;
    this.active = true;
    this.createDOM();
    this.renderStep(this.currentStep);

    window.addEventListener('keydown', this.onKeyDown);
    window.addEventListener('resize', this.onResize);
    window.addEventListener('scroll', this.onResize, true);
  }

  createDOM() {
    if (document.getElementById('appTourOverlay')) {
      document.getElementById('appTourOverlay').remove();
    }

    // Overlay with SVG mask
    this.overlay = document.createElement('div');
    this.overlay.id = 'appTourOverlay';
    this.overlay.className = 'app-tour-overlay';
    this.overlay.innerHTML = `
      <svg class="tour-mask" xmlns="http://www.w3.org/2000/svg">
        <defs>
          <mask id="tourSpotlightMask">
            <rect x="0" y="0" width="100%" height="100%" fill="white" />
            <rect id="tourSpotlightCutout" class="tour-spotlight-cutout" x="0" y="0" width="0" height="0" rx="12" ry="12" fill="black" />
          </mask>
        </defs>
        <rect class="tour-backdrop" x="0" y="0" width="100%" height="100%" mask="url(#tourSpotlightMask)" />
      </svg>
      <div id="tourSpotlightRing" class="tour-spotlight-ring" style="display: none;"></div>
      <div id="tourPopoverCard" class="tour-popover-card" style="display: none;"></div>
    `;

    document.body.appendChild(this.overlay);
    this.spotlightCutout = document.getElementById('tourSpotlightCutout');
    this.spotlightRing = document.getElementById('tourSpotlightRing');
    this.card = document.getElementById('tourPopoverCard');

    requestAnimationFrame(() => {
      this.overlay?.classList.add('active');
    });
  }

  renderStep(index) {
    if (index < 0 || index >= TOUR_STEPS.length) {
      this.end(true);
      return;
    }

    const step = TOUR_STEPS[index];
    let el = document.querySelector(step.target);
    if (!el && step.fallbackTarget) {
      el = document.querySelector(step.fallbackTarget);
    }

    if (!el) {
      // If target missing, advance gracefully
      if (index + 1 < TOUR_STEPS.length) {
        this.renderStep(index + 1);
      } else {
        this.end(true);
      }
      return;
    }

    // Scroll element smoothly into view if needed
    el.scrollIntoView({ behavior: 'smooth', block: 'center', inline: 'center' });

    setTimeout(() => {
      this.positionSpotlightAndCard(el, step, index);
    }, 200);
  }

  positionSpotlightAndCard(el, step, index) {
    if (!this.spotlightCutout || !this.spotlightRing || !this.card) return;

    const rect = el.getBoundingClientRect();
    const pad = 8;
    const x = Math.max(0, rect.left - pad);
    const y = Math.max(0, rect.top - pad);
    const width = Math.min(window.innerWidth - x, rect.width + (pad * 2));
    const height = Math.min(window.innerHeight - y, rect.height + (pad * 2));

    // Update SVG Cutout and Pulsing Ring
    this.spotlightCutout.setAttribute('x', `${x}`);
    this.spotlightCutout.setAttribute('y', `${y}`);
    this.spotlightCutout.setAttribute('width', `${width}`);
    this.spotlightCutout.setAttribute('height', `${height}`);

    this.spotlightRing.style.display = 'block';
    this.spotlightRing.style.left = `${x}px`;
    this.spotlightRing.style.top = `${y}px`;
    this.spotlightRing.style.width = `${width}px`;
    this.spotlightRing.style.height = `${height}px`;

    // Render Card Content
    const isFirst = index === 0;
    const isLast = index === TOUR_STEPS.length - 1;

    const dots = TOUR_STEPS.map((_, i) => 
      `<div class="tour-dot ${i === index ? 'active' : ''}"></div>`
    ).join('');

    this.card.innerHTML = `
      <div class="tour-header">
        <span class="tour-badge">${step.badge}</span>
        <button type="button" class="tour-close-btn" id="btnTourClose" aria-label="Close tour">
          <i class="bi bi-x-lg"></i>
        </button>
      </div>
      <h3 class="tour-title"><i class="bi ${step.icon}"></i> ${step.title}</h3>
      <p class="tour-body">${step.text}</p>
      <div class="tour-footer">
        <div class="tour-steps-indicator">${dots}</div>
        <div class="tour-actions">
          <button type="button" class="btn-tour-skip" id="btnTourSkip">Skip</button>
          ${!isFirst ? '<button type="button" class="btn-tour-prev" id="btnTourPrev">Back</button>' : ''}
          <button type="button" class="btn-tour-next" id="btnTourNext">${isLast ? 'Get Started' : 'Next'}</button>
        </div>
      </div>
    `;

    this.card.style.display = 'block';
    this.card.style.width = '';
    this.card.style.maxWidth = '';

    // Calculate smart card position with mobile edge clamping
    const cardRect = this.card.getBoundingClientRect();
    const isMobile = window.innerWidth < 768;
    const margin = 14;
    let cardTop = y + height + margin;
    let cardLeft = x + (width / 2) - (cardRect.width / 2);

    if (isMobile && (height > window.innerHeight * 0.35 || cardTop + cardRect.height > window.innerHeight - 20)) {
      // Pin cleanly as floating bottom sheet on mobile when target is large
      cardTop = Math.max(16, window.innerHeight - cardRect.height - 24);
      cardLeft = 16;
      this.card.style.width = `${window.innerWidth - 32}px`;
      this.card.style.maxWidth = 'none';
    } else {
      // Desktop positioning
      if (cardTop + cardRect.height > window.innerHeight - 20) {
        cardTop = Math.max(20, y - cardRect.height - margin);
      }

      if (cardLeft < 16) {
        cardLeft = 16;
      } else if (cardLeft + cardRect.width > window.innerWidth - 16) {
        cardLeft = window.innerWidth - cardRect.width - 16;
      }
    }

    this.card.style.left = `${cardLeft}px`;
    this.card.style.top = `${cardTop}px`;

    // Bind Button Events
    document.getElementById('btnTourClose')?.addEventListener('click', () => this.end(true));
    document.getElementById('btnTourSkip')?.addEventListener('click', () => this.end(true));
    document.getElementById('btnTourPrev')?.addEventListener('click', () => {
      this.currentStep--;
      this.renderStep(this.currentStep);
    });
    document.getElementById('btnTourNext')?.addEventListener('click', () => {
      if (isLast) {
        this.end(true);
      } else {
        this.currentStep++;
        this.renderStep(this.currentStep);
      }
    });
  }

  onKeyDown(e) {
    if (!this.active) return;
    if (e.key === 'Escape') {
      this.end(true);
    } else if (e.key === 'ArrowRight') {
      if (this.currentStep < TOUR_STEPS.length - 1) {
        this.currentStep++;
        this.renderStep(this.currentStep);
      } else {
        this.end(true);
      }
    } else if (e.key === 'ArrowLeft') {
      if (this.currentStep > 0) {
        this.currentStep--;
        this.renderStep(this.currentStep);
      }
    }
  }

  onResize() {
    if (!this.active) return;
    this.renderStep(this.currentStep);
  }

  end(persist = true) {
    if (persist) {
      safeSetStorage(STORAGE_KEY, 'true');
    }
    this.active = false;
    window.removeEventListener('keydown', this.onKeyDown);
    window.removeEventListener('resize', this.onResize);
    window.removeEventListener('scroll', this.onResize, true);

    if (this.overlay) {
      this.overlay.classList.remove('active');
      setTimeout(() => {
        this.overlay?.remove();
        this.overlay = null;
      }, 300);
    }
  }
}

export const initOnboardingTour = () => {
  const tour = new OnboardingTour();
  tour.init();
  return tour;
};
