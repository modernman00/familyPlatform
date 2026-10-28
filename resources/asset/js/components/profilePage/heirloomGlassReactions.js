/**
 * 🌟 Living Heirloom Glass 3D Reactions (Chamber 1 Sovereign Board & TAT Approved)
 * Replaces generic plastic emojis with volumetric, optical frosted-glass reactions
 * featuring internal caustics, radiant bioluminescent filaments, and specular highlights.
 *
 * 100% Vector SVG — Zero WebGL overhead, 60fps performance, crisp at all display resolutions.
 */

let _heirloomIdCounter = 0;

export const HEIRLOOM_REACTIONS = {
  love: {
    key: 'love',
    label: 'Love',
    icon: '❤️',
    glow: 'rgba(225, 29, 72, 0.45)',
    tone: '#e11d48',
    description: 'Heirloom Ruby Gem with warm bioluminescent core'
  },
  like: {
    key: 'like',
    label: 'Like',
    icon: '👍',
    glow: 'rgba(245, 158, 11, 0.45)',
    tone: '#f59e0b',
    description: 'Frosted Topaz Amber Thumb with refractive bevel'
  },
  haha: {
    key: 'haha',
    label: 'Haha',
    icon: '😂',
    glow: 'rgba(234, 179, 8, 0.45)',
    tone: '#eab308',
    description: 'Sunburst Honey Citrine Laughing Sun'
  },
  shock: {
    key: 'shock',
    label: 'Wow',
    icon: '😮',
    glow: 'rgba(2, 132, 199, 0.45)',
    tone: '#0284c7',
    description: 'Star-Sapphire Celestial Wonder'
  },
  sad: {
    key: 'sad',
    label: 'Empathy',
    icon: '😢',
    glow: 'rgba(13, 148, 136, 0.45)',
    tone: '#0d9488',
    description: 'Crystalline Aquamarine Dewdrop'
  },
  celebrate: {
    key: 'celebrate',
    label: 'Celebrate',
    icon: '✨',
    glow: 'rgba(147, 51, 234, 0.5)',
    tone: '#9333ea',
    description: 'Prismatic Diamond Starburst Milestone Super-Reaction'
  }
};

// Aliases for compatibility
HEIRLOOM_REACTIONS.likes = HEIRLOOM_REACTIONS.like;
HEIRLOOM_REACTIONS.wow = HEIRLOOM_REACTIONS.shock;

/**
 * Generates an ultra-crisp, self-contained 3D Living Heirloom Glass vector SVG.
 * @param {string} type - Reaction type: 'love' | 'like' | 'haha' | 'shock' | 'sad' | 'celebrate'
 * @param {number} size - Pixel dimensions (width and height)
 * @returns {string} Safe inline SVG markup
 */
export function getHeirloomGlassSvg(type, size = 32) {
  if (!type) return '';
  let normType = String(type).trim().toLowerCase();
  if (normType === 'likes') normType = 'like';
  if (normType === 'wow') normType = 'shock';

  _heirloomIdCounter = (_heirloomIdCounter + 1) % 100000;
  const uid = `hg_${normType}_${_heirloomIdCounter}`;

  switch (normType) {
    case 'love':
      return `<svg class="heirloom-glass-svg heirloom-love" viewBox="0 0 40 40" width="${size}" height="${size}" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Heirloom Love Gem">
        <defs>
          <radialGradient id="lgb_${uid}" cx="40%" cy="30%" r="70%">
            <stop offset="0%" stop-color="#ff7597"/>
            <stop offset="45%" stop-color="#e11d48"/>
            <stop offset="85%" stop-color="#be123c"/>
            <stop offset="100%" stop-color="#881337"/>
          </radialGradient>
          <linearGradient id="ls_${uid}" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0%" stop-color="#ffffff" stop-opacity="0.85"/>
            <stop offset="60%" stop-color="#ffffff" stop-opacity="0.15"/>
            <stop offset="100%" stop-color="#ffffff" stop-opacity="0"/>
          </linearGradient>
          <radialGradient id="lcg_${uid}" cx="50%" cy="60%" r="40%">
            <stop offset="0%" stop-color="#ffedd5" stop-opacity="0.95"/>
            <stop offset="50%" stop-color="#fb923c" stop-opacity="0.6"/>
            <stop offset="100%" stop-color="#e11d48" stop-opacity="0"/>
          </radialGradient>
          <filter id="lc_${uid}" x="-20%" y="-20%" width="140%" height="140%">
            <feDropShadow dx="0" dy="3" stdDeviation="3.5" flood-color="#be123c" flood-opacity="0.45"/>
          </filter>
        </defs>
        <g filter="url(#lc_${uid})" class="hg-heart-body">
          <path d="M20 34.5C19.3 34.5 18.6 34.2 18.1 33.7C11.2 27.5 5 21.8 5 14.5C5 9.2 9.2 5 14.5 5C16.8 5 18.8 5.8 20 7.2C21.2 5.8 23.2 5 25.5 5C30.8 5 35 9.2 35 14.5C35 21.8 28.8 27.5 21.9 33.7C21.4 34.2 20.7 34.5 20 34.5Z" fill="url(#lgb_${uid})"/>
          <circle cx="20" cy="18" r="9.5" fill="url(#lcg_${uid})" class="hg-heart-core"/>
          <path d="M20 32C14 26.5 7.5 21 7.5 14.5C7.5 10.5 10.5 7.5 14.5 7.5C16.8 7.5 18.8 8.5 20 10.2C21.2 8.5 23.2 7.5 25.5 7.5C29.5 7.5 32.5 10.5 32.5 14.5C32.5 21 26 26.5 20 32Z" stroke="#ffffff" stroke-width="0.75" stroke-opacity="0.4" fill="none"/>
          <path d="M10 13C10 9.8 12.2 7.5 15 7.2C13 8 11.5 9.8 11.5 12.5C11.5 14.2 12.2 16 13.5 17.5L11.8 18.5C10.6 16.8 10 14.8 10 13Z" fill="url(#ls_${uid})"/>
          <ellipse cx="27" cy="10" rx="3.5" ry="2" transform="rotate(-20 27 10)" fill="#ffffff" fill-opacity="0.55"/>
        </g>
        <!-- Rising Love Micro-Hearts -->
        <path class="hg-love-spark-1" d="M9 9C7.8 7.8 6.5 6.8 6.5 5.4C6.5 4.4 7.3 3.6 8.3 3.6C8.7 3.6 9.1 3.8 9.3 4.1C9.5 3.8 9.9 3.6 10.3 3.6C11.3 3.6 12.1 4.4 12.1 5.4C12.1 6.8 10.8 7.8 9.6 9H9Z" fill="#ff7597"/>
        <path class="hg-love-spark-2" d="M31 8C30 7 29 6.2 29 5C29 4.2 29.6 3.5 30.4 3.5C30.8 3.5 31.1 3.7 31.3 3.9C31.5 3.7 31.8 3.5 32.2 3.5C33 3.5 33.6 4.2 33.6 5C33.6 6.2 32.6 7 31.6 8H31Z" fill="#fda4af"/>
      </svg>`;

    case 'like':
    case 'likes':
      return `<svg class="heirloom-glass-svg heirloom-like" viewBox="0 0 40 40" width="${size}" height="${size}" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Heirloom Topaz Thumb">
        <defs>
          <radialGradient id="kgb_${uid}" cx="35%" cy="30%" r="75%">
            <stop offset="0%" stop-color="#fef08a"/>
            <stop offset="35%" stop-color="#f59e0b"/>
            <stop offset="80%" stop-color="#d97706"/>
            <stop offset="100%" stop-color="#b45309"/>
          </radialGradient>
          <radialGradient id="kcg_${uid}" cx="50%" cy="50%" r="45%">
            <stop offset="0%" stop-color="#ffffff" stop-opacity="0.9"/>
            <stop offset="50%" stop-color="#fef08a" stop-opacity="0.6"/>
            <stop offset="100%" stop-color="#f59e0b" stop-opacity="0"/>
          </radialGradient>
          <filter id="kc_${uid}" x="-20%" y="-20%" width="140%" height="140%">
            <feDropShadow dx="0" dy="3" stdDeviation="3.5" flood-color="#d97706" flood-opacity="0.45"/>
          </filter>
        </defs>
        <g filter="url(#kc_${uid})">
          <circle cx="20" cy="20" r="16.5" fill="url(#kgb_${uid})"/>
          <circle cx="18" cy="18" r="10" fill="url(#kcg_${uid})"/>
          <circle cx="20" cy="20" r="15" stroke="#ffffff" stroke-width="0.8" stroke-opacity="0.5" fill="none"/>
          <g class="hg-thumb-hand">
            <path d="M13.5 18.5V26.5C13.5 27.3 14.2 28 15 28H16.8V17H15C14.2 17 13.5 17.7 13.5 18.5ZM26.8 19.3C27.2 18.7 27.2 17.8 26.7 17.2C26.2 16.6 25.4 16.3 24.6 16.5H21.2L21.7 13.9L21.8 13.6C21.8 12.8 21.4 12.1 20.8 11.6L20.2 11L16.4 14.6C16 15 15.8 15.5 15.8 16V26C15.8 27.1 16.7 28 17.8 28H24C24.8 28 25.5 27.5 25.8 26.8L27.8 22.2C27.9 21.9 28 21.6 28 21.2V19.5C28 19.4 28 19.3 27.9 19.2C27.7 19.2 27.2 19.2 26.8 19.3Z" fill="#ffffff" fill-opacity="0.96"/>
          </g>
          <g class="hg-thumb-spark">
            <line x1="20" y1="6.5" x2="20" y2="9.2" stroke="#fef08a" stroke-width="1.8" stroke-linecap="round"/>
            <line x1="25.5" y1="8.5" x2="23.8" y2="10.5" stroke="#fef08a" stroke-width="1.8" stroke-linecap="round"/>
            <line x1="14.5" y1="8.5" x2="16.2" y2="10.5" stroke="#fef08a" stroke-width="1.8" stroke-linecap="round"/>
          </g>
          <path d="M10 15C11.5 10 16 7 21 7C23 7 24.5 7.4 26 8.2C23.5 7.8 20 8.5 16.5 11C13.5 13.2 11.8 16 11 18.5C10.5 17.2 10.1 16.1 10 15Z" fill="#ffffff" fill-opacity="0.65"/>
        </g>
      </svg>`;

    case 'haha':
      return `<svg class="heirloom-glass-svg heirloom-haha" viewBox="0 0 40 40" width="${size}" height="${size}" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Heirloom Citrine Laugh">
        <defs>
          <radialGradient id="hgb_${uid}" cx="35%" cy="30%" r="75%">
            <stop offset="0%" stop-color="#fffbeb"/>
            <stop offset="25%" stop-color="#fde047"/>
            <stop offset="65%" stop-color="#eab308"/>
            <stop offset="90%" stop-color="#ca8a04"/>
            <stop offset="100%" stop-color="#a16207"/>
          </radialGradient>
          <radialGradient id="hcg_${uid}" cx="50%" cy="50%" r="45%">
            <stop offset="0%" stop-color="#ffffff" stop-opacity="0.95"/>
            <stop offset="50%" stop-color="#fef08a" stop-opacity="0.5"/>
            <stop offset="100%" stop-color="#eab308" stop-opacity="0"/>
          </radialGradient>
          <filter id="hc_${uid}" x="-20%" y="-20%" width="140%" height="140%">
            <feDropShadow dx="0" dy="3" stdDeviation="3.5" flood-color="#ca8a04" flood-opacity="0.45"/>
          </filter>
        </defs>
        <g filter="url(#hc_${uid})" class="hg-haha-face">
          <circle cx="20" cy="20" r="16.5" fill="url(#hgb_${uid})"/>
          <circle cx="18" cy="18" r="10" fill="url(#hcg_${uid})"/>
          <circle cx="20" cy="20" r="15" stroke="#ffffff" stroke-width="0.8" stroke-opacity="0.5" fill="none"/>
          <!-- Squinting Joyful Eyes -->
          <path d="M11.5 15L16.5 17L11.5 18.8" stroke="#78350f" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
          <path d="M28.5 15L23.5 17L28.5 18.8" stroke="#78350f" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
          <!-- Animated Laughing Mouth -->
          <g class="hg-haha-mouth">
            <path d="M12 20.5C12 26.2 15.5 30 20 30C24.5 30 28 26.2 28 20.5H12Z" fill="#78350f"/>
            <path d="M13.2 20.5H26.8V22.2H13.2V20.5Z" fill="#ffffff"/>
            <path d="M16 26.2C17.2 28 18.8 28.8 20 28.8C21.2 28.8 22.8 28 24 26.2C22 25.4 18 25.4 16 26.2Z" fill="#f43f5e"/>
          </g>
          <!-- Joyful Laughing Tears -->
          <g class="hg-haha-tears">
            <path d="M9.5 19.5C9.5 19.5 6.8 21.2 7.2 22.8C7.5 24 8.8 24.4 9.8 23.6C10.5 22.9 9.5 19.5 9.5 19.5Z" fill="#38bdf8"/>
            <path d="M30.5 19.5C30.5 19.5 33.2 21.2 32.8 22.8C32.5 24 31.2 24.4 30.2 23.6C29.5 22.9 30.5 19.5 30.5 19.5Z" fill="#38bdf8"/>
          </g>
          <ellipse cx="20" cy="7.5" rx="7" ry="2.2" fill="#ffffff" fill-opacity="0.65"/>
        </g>
      </svg>`;

    case 'shock':
    case 'wow':
      return `<svg class="heirloom-glass-svg heirloom-shock" viewBox="0 0 40 40" width="${size}" height="${size}" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Heirloom Sapphire Wonder">
        <defs>
          <radialGradient id="sgb_${uid}" cx="35%" cy="30%" r="75%">
            <stop offset="0%" stop-color="#e0f2fe"/>
            <stop offset="25%" stop-color="#38bdf8"/>
            <stop offset="65%" stop-color="#0284c7"/>
            <stop offset="90%" stop-color="#0369a1"/>
            <stop offset="100%" stop-color="#075985"/>
          </radialGradient>
          <radialGradient id="scg_${uid}" cx="50%" cy="50%" r="45%">
            <stop offset="0%" stop-color="#ffffff" stop-opacity="0.95"/>
            <stop offset="50%" stop-color="#7dd3fc" stop-opacity="0.5"/>
            <stop offset="100%" stop-color="#0284c7" stop-opacity="0"/>
          </radialGradient>
          <filter id="sc_${uid}" x="-20%" y="-20%" width="140%" height="140%">
            <feDropShadow dx="0" dy="3" stdDeviation="3.5" flood-color="#0284c7" flood-opacity="0.45"/>
          </filter>
        </defs>
        <g filter="url(#sc_${uid})">
          <circle cx="20" cy="20" r="16.5" fill="url(#sgb_${uid})"/>
          <circle cx="18" cy="18" r="10" fill="url(#scg_${uid})"/>
          <circle cx="20" cy="20" r="15" stroke="#ffffff" stroke-width="0.8" stroke-opacity="0.5" fill="none"/>
          <g class="hg-wow-brows">
            <path d="M11 11.2C12.5 9.4 15.5 9.4 17 11.2" stroke="#082f49" stroke-width="1.9" stroke-linecap="round" fill="none"/>
            <path d="M23 11.2C24.5 9.4 27.5 9.4 29 11.2" stroke="#082f49" stroke-width="1.9" stroke-linecap="round" fill="none"/>
          </g>
          <g class="hg-wow-eyes">
            <circle cx="14" cy="15.8" r="3.3" fill="#082f49"/>
            <circle cx="13" cy="14.7" r="1.2" fill="#ffffff"/>
            <circle cx="26" cy="15.8" r="3.3" fill="#082f49"/>
            <circle cx="25" cy="14.7" r="1.2" fill="#ffffff"/>
          </g>
          <g class="hg-wow-mouth">
            <ellipse cx="20" cy="25" rx="4.4" ry="5.6" fill="#082f49"/>
            <ellipse cx="20" cy="25.5" rx="2.6" ry="3.5" fill="#38bdf8" fill-opacity="0.45"/>
          </g>
          <ellipse cx="20" cy="7.5" rx="7" ry="2.2" fill="#ffffff" fill-opacity="0.65"/>
        </g>
      </svg>`;

    case 'sad':
      return `<svg class="heirloom-glass-svg heirloom-sad" viewBox="0 0 40 40" width="${size}" height="${size}" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Heirloom Aquamarine Empathy">
        <defs>
          <radialGradient id="agb_${uid}" cx="35%" cy="30%" r="75%">
            <stop offset="0%" stop-color="#f0fdfa"/>
            <stop offset="30%" stop-color="#5eead4"/>
            <stop offset="65%" stop-color="#0d9488"/>
            <stop offset="90%" stop-color="#115e59"/>
            <stop offset="100%" stop-color="#134e4a"/>
          </radialGradient>
          <radialGradient id="atg_${uid}" cx="35%" cy="35%" r="65%">
            <stop offset="0%" stop-color="#ffffff"/>
            <stop offset="60%" stop-color="#38bdf8"/>
            <stop offset="100%" stop-color="#0284c7"/>
          </radialGradient>
          <filter id="ac_${uid}" x="-20%" y="-20%" width="140%" height="140%">
            <feDropShadow dx="0" dy="3" stdDeviation="3.5" flood-color="#0d9488" flood-opacity="0.45"/>
          </filter>
        </defs>
        <g filter="url(#ac_${uid})" class="hg-sad-face">
          <circle cx="20" cy="20" r="16.5" fill="url(#agb_${uid})"/>
          <circle cx="20" cy="20" r="15" stroke="#ffffff" stroke-width="0.8" stroke-opacity="0.5" fill="none"/>
          <g class="hg-sad-brows">
            <path d="M11.5 14C13.5 13.6 15.5 12.8 17 11.8" stroke="#042f2e" stroke-width="1.8" stroke-linecap="round" fill="none"/>
            <path d="M28.5 14C26.5 13.6 24.5 12.8 23 11.8" stroke="#042f2e" stroke-width="1.8" stroke-linecap="round" fill="none"/>
          </g>
          <circle cx="14" cy="17" r="2.6" fill="#042f2e"/>
          <circle cx="13.2" cy="16.2" r="0.9" fill="#ffffff"/>
          <circle cx="26" cy="17" r="2.6" fill="#042f2e"/>
          <circle cx="25.2" cy="16.2" r="0.9" fill="#ffffff"/>
          <path d="M14.8 26C17 23.5 23 23.5 25.2 26" stroke="#042f2e" stroke-width="2.1" stroke-linecap="round" fill="none"/>
          <!-- Animated Falling Crystalline Tear -->
          <g class="hg-sad-tear">
            <path d="M26.8 19.2C26.8 19.2 29.4 22.4 29.4 24C29.4 25.5 28.2 26.7 26.8 26.7C25.4 26.7 24.2 25.5 24.2 24C24.2 22.4 26.8 19.2 26.8 19.2Z" fill="url(#atg_${uid})"/>
            <circle cx="26" cy="23.5" r="0.8" fill="#ffffff"/>
          </g>
          <ellipse cx="20" cy="7.5" rx="7" ry="2.2" fill="#ffffff" fill-opacity="0.65"/>
        </g>
      </svg>`;

    case 'celebrate':
      return `<svg class="heirloom-glass-svg heirloom-celebrate" viewBox="0 0 40 40" width="${size}" height="${size}" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Heirloom Diamond Milestone">
        <defs>
          <radialGradient id="cgb_${uid}" cx="35%" cy="30%" r="75%">
            <stop offset="0%" stop-color="#faf5ff"/>
            <stop offset="25%" stop-color="#c084fc"/>
            <stop offset="65%" stop-color="#9333ea"/>
            <stop offset="90%" stop-color="#7e22ce"/>
            <stop offset="100%" stop-color="#581c87"/>
          </radialGradient>
          <radialGradient id="csg_${uid}" cx="50%" cy="50%" r="50%">
            <stop offset="0%" stop-color="#ffffff"/>
            <stop offset="45%" stop-color="#fde047"/>
            <stop offset="85%" stop-color="#eab308"/>
            <stop offset="100%" stop-color="#ca8a04"/>
          </radialGradient>
          <filter id="cc_${uid}" x="-30%" y="-30%" width="160%" height="160%">
            <feDropShadow dx="0" dy="3.5" stdDeviation="4.5" flood-color="#9333ea" flood-opacity="0.5"/>
          </filter>
        </defs>
        <g filter="url(#cc_${uid})">
          <circle cx="20" cy="20" r="16.5" fill="url(#cgb_${uid})"/>
          <circle cx="20" cy="20" r="15" stroke="#ffffff" stroke-width="0.8" stroke-opacity="0.6" fill="none"/>
          <g class="hg-celebrate-star">
            <path d="M20 7.5L22.2 15.5L29 13.5L24.5 19L31.5 21.5L24.5 24L28.5 29.5L22 26.5L20 33.5L18 26.5L11.5 29.5L15.5 24L8.5 21.5L15.5 19L11 13.5L17.8 15.5L20 7.5Z" fill="url(#csg_${uid})"/>
            <polygon points="20,15 23.5,20 20,25 16.5,20" fill="#ffffff" fill-opacity="0.95"/>
          </g>
          <g class="hg-celebrate-orbit">
            <circle cx="10" cy="11" r="1.5" fill="#fde047"/>
            <circle cx="30" cy="10" r="1.3" fill="#f472b6"/>
            <circle cx="31" cy="28" r="1.4" fill="#38bdf8"/>
            <circle cx="8" cy="27" r="1.3" fill="#fde047"/>
          </g>
          <ellipse cx="20" cy="7.2" rx="6.5" ry="1.8" fill="#ffffff" fill-opacity="0.7"/>
        </g>
      </svg>`;

    default:
      if (HEIRLOOM_REACTIONS[normType]?.icon) {
        return `<span style="font-size: ${size * 0.75}px; line-height: 1;">${HEIRLOOM_REACTIONS[normType].icon}</span>`;
      }
      return '';
  }
}

/**
 * Spawns a living 3D particle burst of miniature Heirloom Glass gems when a user clicks a reaction.
 * @param {string} type - Reaction type ('love', 'like', 'haha', 'shock', 'sad', 'celebrate')
 * @param {Event|HTMLElement|null} source - Click event or target DOM element
 */
export function triggerHeirloomBurst(type, source = null) {
  if (typeof window === 'undefined' || typeof document === 'undefined') return;
  if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

  let x = window.innerWidth / 2;
  let y = window.innerHeight / 2;

  if (source) {
    if (typeof source.clientX === 'number' && source.clientX > 0) {
      x = source.clientX;
      y = source.clientY;
    } else if (source.target && typeof source.target.getBoundingClientRect === 'function') {
      const rect = source.target.getBoundingClientRect();
      x = rect.left + rect.width / 2;
      y = rect.top + rect.height / 2;
    } else if (typeof source.getBoundingClientRect === 'function') {
      const rect = source.getBoundingClientRect();
      x = rect.left + rect.width / 2;
      y = rect.top + rect.height / 2;
    }
  }

  const container = document.createElement('div');
  container.className = 'heirloom-burst-stage';
  container.style.cssText = `position:fixed;left:${x}px;top:${y}px;width:0;height:0;pointer-events:none;z-index:100060;`;
  document.body.appendChild(container);

  const count = 6;
  const svgMarkup = getHeirloomGlassSvg(type || 'love', 20);

  for (let i = 0; i < count; i++) {
    const particle = document.createElement('div');
    const angle = (Math.PI * 2 * i) / count - Math.PI / 2 + (Math.random() * 0.5 - 0.25);
    const distance = 36 + Math.random() * 34;
    const dx = Math.cos(angle) * distance;
    const dy = Math.sin(angle) * distance - 28; // Fountain upward bias
    const rot = Math.round(Math.random() * 50 - 25);
    const scale = (0.65 + Math.random() * 0.55).toFixed(2);

    particle.className = 'heirloom-burst-particle';
    particle.style.setProperty('--burst-dx', `${dx.toFixed(1)}px`);
    particle.style.setProperty('--burst-dy', `${dy.toFixed(1)}px`);
    particle.style.setProperty('--burst-rot', `${rot}deg`);
    particle.style.setProperty('--burst-scale', scale);
    particle.innerHTML = svgMarkup;
    container.appendChild(particle);
  }

  setTimeout(() => {
    if (container.parentNode) {
      container.parentNode.removeChild(container);
    }
  }, 850);
}
