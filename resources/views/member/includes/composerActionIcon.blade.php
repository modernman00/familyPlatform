@php
  $iconType = (string) ($type ?? 'photo');
  $p = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) ($prefix ?? 'composer')) ?: 'composer';
@endphp
@switch($iconType)
  @case('photo')
    <span class="composer-pill-icon composer-pill-icon--photo" aria-hidden="true">
      <span class="composer-pill-icon__sheen"></span>
      <span class="composer-pill-icon__spark"></span>
      <svg class="composer-pill-svg composer-pill-svg--photo" width="22" height="22" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg" focusable="false" aria-hidden="true">
        <defs>
          <linearGradient id="{{ $p }}-cam-top" x1="10" y1="6" x2="17" y2="9.5" gradientUnits="userSpaceOnUse">
            <stop offset="0%" stop-color="#ffffff"/>
            <stop offset="100%" stop-color="#ffe4e6"/>
          </linearGradient>
          <linearGradient id="{{ $p }}-cam-body" x1="4" y1="9" x2="23" y2="23" gradientUnits="userSpaceOnUse">
            <stop offset="0%" stop-color="#ffffff"/>
            <stop offset="68%" stop-color="#fff1f2"/>
            <stop offset="100%" stop-color="#fecdd3"/>
          </linearGradient>
          <linearGradient id="{{ $p }}-cam-ring" x1="9" y1="11" x2="18" y2="21" gradientUnits="userSpaceOnUse">
            <stop offset="0%" stop-color="#fef08a"/>
            <stop offset="52%" stop-color="#f59e0b"/>
            <stop offset="100%" stop-color="#b45309"/>
          </linearGradient>
          <radialGradient id="{{ $p }}-cam-lens" cx="38%" cy="34%" r="68%">
            <stop offset="0%" stop-color="#7dd3fc"/>
            <stop offset="48%" stop-color="#4f46e5"/>
            <stop offset="100%" stop-color="#1e1b4b"/>
          </radialGradient>
        </defs>
        <rect x="5.6" y="7.4" width="3.2" height="1.9" rx="0.95" fill="#fde047"/>
        <path d="M9.6 9.2L11 6.7C11.3 6.22 11.78 6 12.3 6H14.3C14.82 6 15.3 6.22 15.6 6.7L17 9.2H9.6Z" fill="url(#{{ $p }}-cam-top)"/>
        <rect x="3.5" y="9.2" width="19.6" height="13.4" rx="3.8" fill="url(#{{ $p }}-cam-body)" stroke="#ffffff" stroke-width="0.6"/>
        <rect x="3.8" y="13.8" width="19" height="4.2" fill="#fda4af" fill-opacity="0.28"/>
        <circle cx="6.6" cy="12" r="0.9" fill="#f59e0b"/>
        <circle cx="13.3" cy="15.9" r="5.1" fill="url(#{{ $p }}-cam-ring)"/>
        <circle cx="13.3" cy="15.9" r="3.95" fill="#1e1b4b"/>
        <circle cx="13.3" cy="15.9" r="3.05" fill="url(#{{ $p }}-cam-lens)"/>
        <circle cx="12.1" cy="14.6" r="1.1" fill="#ffffff" fill-opacity="0.94"/>
        <circle cx="14.6" cy="17.1" r="0.55" fill="#bae6fd" fill-opacity="0.85"/>
        <path class="composer-svg-flash" d="M22.3 3.8L23.05 5.95L25.2 6.7L23.05 7.45L22.3 9.6L21.55 7.45L19.4 6.7L21.55 5.95L22.3 3.8Z" fill="#fef08a" stroke="#f59e0b" stroke-width="0.45" stroke-linejoin="round"/>
      </svg>
    </span>
    @break

  @case('video')
    <span class="composer-pill-icon composer-pill-icon--video" aria-hidden="true">
      <span class="composer-pill-icon__sheen"></span>
      <span class="composer-pill-icon__spark"></span>
      <svg class="composer-pill-svg composer-pill-svg--video" width="22" height="22" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg" focusable="false" aria-hidden="true">
        <defs>
          <linearGradient id="{{ $p }}-vid-body" x1="4" y1="11" x2="24" y2="23" gradientUnits="userSpaceOnUse">
            <stop offset="0%" stop-color="#4c1d95"/>
            <stop offset="100%" stop-color="#2e1065"/>
          </linearGradient>
          <linearGradient id="{{ $p }}-vid-play" x1="12" y1="13.5" x2="18.5" y2="21" gradientUnits="userSpaceOnUse">
            <stop offset="0%" stop-color="#fef08a"/>
            <stop offset="100%" stop-color="#f59e0b"/>
          </linearGradient>
          <clipPath id="{{ $p }}-vid-clap-clip">
            <rect x="4.4" y="6.8" width="19.2" height="4" rx="1.5"/>
          </clipPath>
        </defs>
        <rect x="4.2" y="11.4" width="19.6" height="11.8" rx="3.2" fill="url(#{{ $p }}-vid-body)" stroke="#f5d0fe" stroke-width="0.9"/>
        <g class="composer-svg-clapper">
          <g transform="rotate(-8 4.8 11.2)">
            <rect x="4.4" y="6.8" width="19.2" height="4" rx="1.5" fill="#ffffff"/>
            <g clip-path="url(#{{ $p }}-vid-clap-clip)">
              <path d="M7.6 6.4H10.6L9.1 11.2H6.1L7.6 6.4Z" fill="#f59e0b"/>
              <path d="M13 6.4H16L14.5 11.2H11.5L13 6.4Z" fill="#9333ea"/>
              <path d="M18.4 6.4H21.4L19.9 11.2H16.9L18.4 6.4Z" fill="#f59e0b"/>
            </g>
          </g>
        </g>
        <path d="M12.1 14.5C12.1 13.85 12.82 13.45 13.38 13.79L17.75 16.49C18.28 16.82 18.28 17.58 17.75 17.91L13.38 20.61C12.82 20.95 12.1 20.55 12.1 19.9V14.5Z" fill="url(#{{ $p }}-vid-play)" stroke="#ffffff" stroke-width="0.55" stroke-linejoin="round"/>
        <path d="M20.8 12.7L21.2 13.7L22.2 14.1L21.2 14.5L20.8 15.5L20.4 14.5L19.4 14.1L20.4 13.7L20.8 12.7Z" fill="#fef08a"/>
      </svg>
    </span>
    @break

  @case('thennow')
    <span class="composer-pill-icon composer-pill-icon--thennow" aria-hidden="true">
      <span class="composer-pill-icon__sheen"></span>
      <span class="composer-pill-icon__spark"></span>
      <svg class="composer-pill-svg composer-pill-svg--thennow" width="22" height="22" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg" focusable="false" aria-hidden="true">
        <defs>
          <linearGradient id="{{ $p }}-tn-gold" x1="7" y1="4" x2="21" y2="24" gradientUnits="userSpaceOnUse">
            <stop offset="0%" stop-color="#fef08a"/>
            <stop offset="52%" stop-color="#f59e0b"/>
            <stop offset="100%" stop-color="#b45309"/>
          </linearGradient>
          <linearGradient id="{{ $p }}-tn-sand" x1="11" y1="9" x2="17" y2="21" gradientUnits="userSpaceOnUse">
            <stop offset="0%" stop-color="#fef08a"/>
            <stop offset="100%" stop-color="#f59e0b"/>
          </linearGradient>
        </defs>
        <g class="composer-svg-hourglass">
          <line x1="8.5" y1="6.6" x2="8.5" y2="21.4" stroke="#fde047" stroke-width="0.95" stroke-opacity="0.78" stroke-linecap="round"/>
          <line x1="19.5" y1="6.6" x2="19.5" y2="21.4" stroke="#fde047" stroke-width="0.95" stroke-opacity="0.78" stroke-linecap="round"/>
          <path d="M9.5 6.6H18.5C18.5 10.3 16.4 12.4 14.9 13.55C14.55 13.8 14.55 14.2 14.9 14.45C16.4 15.6 18.5 17.7 18.5 21.4H9.5C9.5 17.7 11.6 15.6 13.1 14.45C13.45 14.2 13.45 13.8 13.1 13.55C11.6 12.4 9.5 10.3 9.5 6.6Z" fill="#ffffff" fill-opacity="0.24" stroke="#fff7ed" stroke-width="1.15" stroke-linejoin="round"/>
          <path d="M11.1 8.8H16.9C16.4 10.8 15.3 12 14 12.9C12.7 12 11.6 10.8 11.1 8.8Z" fill="url(#{{ $p }}-tn-sand)"/>
          <line x1="14" y1="13.3" x2="14" y2="17.8" stroke="#fef08a" stroke-width="1.2" stroke-dasharray="1.1 1.3" stroke-linecap="round"/>
          <path d="M10.4 20.8C11.2 18.8 12.5 17.6 14 17.6C15.5 17.6 16.8 18.8 17.6 20.8H10.4Z" fill="url(#{{ $p }}-tn-sand)"/>
          <path d="M10.9 8.3C11.1 9.8 11.7 10.9 12.5 11.6" stroke="#ffffff" stroke-width="0.9" stroke-linecap="round" stroke-opacity="0.88"/>
          <rect x="6.8" y="4" width="14.4" height="2.6" rx="1.3" fill="url(#{{ $p }}-tn-gold)" stroke="#fffbeb" stroke-width="0.4"/>
          <rect x="6.8" y="21.4" width="14.4" height="2.6" rx="1.3" fill="url(#{{ $p }}-tn-gold)" stroke="#fffbeb" stroke-width="0.4"/>
        </g>
        <path class="composer-svg-sparkle" d="M4.5 11.4L5 12.9L6.5 13.4L5 13.9L4.5 15.4L4 13.9L2.5 13.4L4 12.9L4.5 11.4Z" fill="#fef08a"/>
        <path class="composer-svg-sparkle" d="M23.5 12.8L23.95 14.15L25.3 14.6L23.95 15.05L23.5 16.4L23.05 15.05L21.7 14.6L23.05 14.15L23.5 12.8Z" fill="#ffffff"/>
      </svg>
    </span>
    @break

  @case('poll')
    <span class="composer-pill-icon composer-pill-icon--poll" aria-hidden="true">
      <span class="composer-pill-icon__sheen"></span>
      <span class="composer-pill-icon__spark"></span>
      <svg class="composer-pill-svg composer-pill-svg--poll" width="22" height="22" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg" focusable="false" aria-hidden="true">
        <defs>
          <linearGradient id="{{ $p }}-poll-b1" x1="5.4" y1="13.8" x2="10" y2="21.8" gradientUnits="userSpaceOnUse">
            <stop offset="0%" stop-color="#fda4af"/>
            <stop offset="100%" stop-color="#e11d48"/>
          </linearGradient>
          <linearGradient id="{{ $p }}-poll-b2" x1="11.7" y1="9.6" x2="16.3" y2="21.8" gradientUnits="userSpaceOnUse">
            <stop offset="0%" stop-color="#fef08a"/>
            <stop offset="100%" stop-color="#f59e0b"/>
          </linearGradient>
          <linearGradient id="{{ $p }}-poll-b3" x1="18" y1="15.8" x2="22.6" y2="21.8" gradientUnits="userSpaceOnUse">
            <stop offset="0%" stop-color="#6ee7b7"/>
            <stop offset="100%" stop-color="#059669"/>
          </linearGradient>
        </defs>
        <rect class="composer-svg-bar-1" x="5.4" y="13.8" width="4.6" height="8" rx="1.7" fill="url(#{{ $p }}-poll-b1)" stroke="#fff1f2" stroke-width="0.5"/>
        <rect class="composer-svg-bar-2" x="11.7" y="9.6" width="4.6" height="12.2" rx="1.7" fill="url(#{{ $p }}-poll-b2)" stroke="#fefce8" stroke-width="0.5"/>
        <rect class="composer-svg-bar-3" x="18" y="15.8" width="4.6" height="6" rx="1.7" fill="url(#{{ $p }}-poll-b3)" stroke="#ecfdf5" stroke-width="0.5"/>
        <rect x="4.2" y="21.8" width="19.6" height="2" rx="1" fill="#e0f2fe"/>
        <path class="composer-svg-star" d="M14 2.8L14.95 4.88L17.2 5.18L15.55 6.72L15.96 8.95L14 7.86L12.04 8.95L12.45 6.72L10.8 5.18L13.05 4.88L14 2.8Z" fill="#fef08a" stroke="#ffffff" stroke-width="0.55" stroke-linejoin="round"/>
      </svg>
    </span>
    @break

  @case('announce')
    <span class="composer-pill-icon composer-pill-icon--announce" aria-hidden="true">
      <span class="composer-pill-icon__sheen"></span>
      <span class="composer-pill-icon__spark"></span>
      <svg class="composer-pill-svg composer-pill-svg--announce" width="22" height="22" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg" focusable="false" aria-hidden="true">
        <defs>
          <linearGradient id="{{ $p }}-ann-cone" x1="5" y1="8" x2="18" y2="20" gradientUnits="userSpaceOnUse">
            <stop offset="0%" stop-color="#ffffff"/>
            <stop offset="70%" stop-color="#fef3c7"/>
            <stop offset="100%" stop-color="#fde68a"/>
          </linearGradient>
          <linearGradient id="{{ $p }}-ann-bell" x1="17" y1="8" x2="20" y2="20" gradientUnits="userSpaceOnUse">
            <stop offset="0%" stop-color="#fb7185"/>
            <stop offset="55%" stop-color="#be123c"/>
            <stop offset="100%" stop-color="#881337"/>
          </linearGradient>
        </defs>
        <g class="composer-svg-horn">
          <path d="M8.6 16.4L10 21.4C10.22 22.15 11 22.55 11.72 22.3L12.4 22.05C13.08 21.8 13.42 21.05 13.2 20.35L11.9 16.2L8.6 16.4Z" fill="#9f1239" stroke="#fecdd3" stroke-width="0.5"/>
          <path d="M5.2 13C4.55 13.2 4.1 13.8 4.1 14.5V15.9C4.1 16.65 4.7 17.25 5.45 17.25L8.8 17L17.4 20.15C18.1 20.4 18.85 19.88 18.85 19.12V8.68C18.85 7.92 18.1 7.4 17.4 7.65L8.8 11.15L5.2 13Z" fill="url(#{{ $p }}-ann-cone)" stroke="#fffbeb" stroke-width="0.55" stroke-linejoin="round"/>
          <path d="M8.8 11.2V17" stroke="#f59e0b" stroke-width="1.35" stroke-linecap="round"/>
          <ellipse cx="18.5" cy="13.9" rx="2" ry="5.9" fill="url(#{{ $p }}-ann-bell)" stroke="#ffffff" stroke-width="0.7"/>
        </g>
        <path class="composer-svg-wave" d="M21.8 11.1C22.95 12.15 22.95 15.65 21.8 16.7" stroke="#ffffff" stroke-width="1.5" stroke-linecap="round"/>
        <path class="composer-svg-wave composer-svg-wave--outer" d="M23.7 8.9C25.45 10.85 25.45 16.95 23.7 18.9" stroke="#fef08a" stroke-width="1.35" stroke-linecap="round"/>
        <circle class="composer-svg-wave" cx="22.8" cy="6.8" r="1.05" fill="#fef08a"/>
      </svg>
    </span>
    @break

  @case('journal')
    <span class="composer-pill-icon composer-pill-icon--journal" aria-hidden="true">
      <span class="composer-pill-icon__sheen"></span>
      <span class="composer-pill-icon__spark"></span>
      <svg class="composer-pill-svg composer-pill-svg--journal" width="22" height="22" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg" focusable="false" aria-hidden="true">
        <defs>
          <linearGradient id="{{ $p }}-jrn-cover" x1="5.6" y1="4.6" x2="19.2" y2="21.2" gradientUnits="userSpaceOnUse">
            <stop offset="0%" stop-color="#10b981"/>
            <stop offset="55%" stop-color="#047857"/>
            <stop offset="100%" stop-color="#064e3b"/>
          </linearGradient>
          <linearGradient id="{{ $p }}-jrn-ribbon" x1="10.4" y1="20.8" x2="13.6" y2="25.4" gradientUnits="userSpaceOnUse">
            <stop offset="0%" stop-color="#fb7185"/>
            <stop offset="100%" stop-color="#be123c"/>
          </linearGradient>
          <linearGradient id="{{ $p }}-jrn-quill" x1="15" y1="15" x2="24" y2="5" gradientUnits="userSpaceOnUse">
            <stop offset="0%" stop-color="#f59e0b"/>
            <stop offset="60%" stop-color="#fde047"/>
            <stop offset="100%" stop-color="#ffffff"/>
          </linearGradient>
        </defs>
        <rect x="6.8" y="5.8" width="13.4" height="16.6" rx="2.2" fill="#fffbeb"/>
        <path d="M10.4 20.8H13.6V25.4L12 24L10.4 25.4V20.8Z" fill="url(#{{ $p }}-jrn-ribbon)" stroke="#fff1f2" stroke-width="0.45" stroke-linejoin="round"/>
        <rect x="5.6" y="4.6" width="13.6" height="16.6" rx="2.2" fill="url(#{{ $p }}-jrn-cover)" stroke="#a7f3d0" stroke-width="0.75"/>
        <line x1="8.4" y1="4.9" x2="8.4" y2="20.9" stroke="#6ee7b7" stroke-width="1" stroke-opacity="0.75"/>
        <path d="M13.6 11.7C13.6 10.9 14.4 10.35 15.15 10.75C15.9 11.15 16.05 12.1 15.5 12.85C14.95 13.6 13.6 14.65 13.6 14.65C13.6 14.65 12.25 13.6 11.7 12.85C11.15 12.1 11.3 11.15 12.05 10.75C12.8 10.35 13.6 10.9 13.6 11.7Z" fill="#fde047"/>
        <g class="composer-svg-quill">
          <path d="M24.2 4.6C20.8 4.9 17.8 7.5 16.6 11.2L15.4 14.8L18.7 13.5C22.1 12 24.3 8.8 24.2 4.6Z" fill="url(#{{ $p }}-jrn-quill)" stroke="#ffffff" stroke-width="0.55" stroke-linejoin="round"/>
          <path d="M15.4 14.8L22.5 6.8" stroke="#b45309" stroke-width="0.8" stroke-linecap="round"/>
        </g>
      </svg>
    </span>
    @break

  @case('backdrop')
    <span class="composer-pill-icon composer-pill-icon--backdrop" aria-hidden="true">
      <span class="composer-pill-icon__sheen"></span>
      <span class="composer-pill-icon__spark"></span>
      <svg class="composer-pill-svg composer-pill-svg--backdrop" width="22" height="22" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg" focusable="false" aria-hidden="true">
        <defs>
          <radialGradient id="{{ $p }}-bg-canvas" cx="32%" cy="28%" r="76%">
            <stop offset="0%" stop-color="#38bdf8"/>
            <stop offset="48%" stop-color="#0369a1"/>
            <stop offset="100%" stop-color="#020817"/>
          </radialGradient>
        </defs>
        <rect x="3.8" y="4.8" width="20.4" height="18.4" rx="4.2" fill="url(#{{ $p }}-bg-canvas)" stroke="#7dd3fc" stroke-width="0.9"/>
        <circle cx="8.2" cy="9.2" r="1.4" fill="#67e8f9" fill-opacity="0.85"/>
        <circle cx="20.2" cy="8.4" r="1.9" fill="#38bdf8" fill-opacity="0.55"/>
        <circle cx="19.4" cy="18.8" r="1.5" fill="#bae6fd" fill-opacity="0.8"/>
        <circle cx="7.6" cy="19.1" r="1.0" fill="#e0f2fe" fill-opacity="0.9"/>
        <text x="14" y="17.2" text-anchor="middle" fill="#ffffff" font-family="system-ui, -apple-system, sans-serif" font-size="9.6" font-weight="800" letter-spacing="-0.3" aria-hidden="true">Aa</text>
      </svg>
    </span>
    @break

  @case('header')
    <svg class="composer-header-svg" width="22" height="22" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg" focusable="false" aria-hidden="true">
      <defs>
        <linearGradient id="{{ $p }}-hdr-bubble" x1="5" y1="4" x2="23" y2="23" gradientUnits="userSpaceOnUse">
          <stop offset="0%" stop-color="#ffffff"/>
          <stop offset="100%" stop-color="#fff1f2"/>
        </linearGradient>
        <linearGradient id="{{ $p }}-hdr-heart" x1="10" y1="8" x2="18" y2="16" gradientUnits="userSpaceOnUse">
          <stop offset="0%" stop-color="#fde047"/>
          <stop offset="45%" stop-color="#fb7185"/>
          <stop offset="100%" stop-color="#be123c"/>
        </linearGradient>
      </defs>
      <path d="M5.4 7.8C5.4 5.9 7 4.4 9 4.4H19C21 4.4 22.6 5.9 22.6 7.8V16C22.6 17.9 21 19.4 19 19.4H12.4L8.1 23C7.48 23.52 6.55 23.08 6.55 22.28V19.15C5.82 18.45 5.4 17.3 5.4 16V7.8Z" fill="url(#{{ $p }}-hdr-bubble)" stroke="#fef08a" stroke-width="0.65" stroke-linejoin="round"/>
      <path d="M14 10.6C14 9.5 15.1 8.75 16.15 9.25C17.2 9.75 17.5 11.05 16.75 12.15C16 13.25 14 14.8 14 14.8C14 14.8 12 13.25 11.25 12.15C10.5 11.05 10.8 9.75 11.85 9.25C12.9 8.75 14 9.5 14 10.6Z" fill="url(#{{ $p }}-hdr-heart)"/>
      <path d="M23.5 3.6L24 5L25.4 5.5L24 6L23.5 7.4L23 6L21.6 5.5L23 5L23.5 3.6Z" fill="#fef08a"/>
    </svg>
    @break

  @case('prompt')
    <svg class="composer-prompt-svg" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" focusable="false" aria-hidden="true">
      <defs>
        <linearGradient id="{{ $p }}-prm-nib" x1="4" y1="20" x2="20" y2="4" gradientUnits="userSpaceOnUse">
          <stop offset="0%" stop-color="#a31621"/>
          <stop offset="55%" stop-color="#e11d48"/>
          <stop offset="100%" stop-color="#f59e0b"/>
        </linearGradient>
      </defs>
      <path d="M19.8 4.2C16.2 4.6 12.7 7.1 10.7 10.9L8.2 15.8L13.1 13.3C16.9 11.3 19.4 7.8 19.8 4.2Z" fill="url(#{{ $p }}-prm-nib)"/>
      <path d="M8.2 15.8L4.5 19.5" stroke="#a31621" stroke-width="1.6" stroke-linecap="round"/>
      <path d="M11 13L17.4 6.6" stroke="#fef08a" stroke-width="1" stroke-linecap="round" stroke-opacity="0.85"/>
      <path d="M6.4 5.4L6.9 6.8L8.3 7.3L6.9 7.8L6.4 9.2L5.9 7.8L4.5 7.3L5.9 6.8L6.4 5.4Z" fill="#f59e0b"/>
    </svg>
    @break
@endswitch
