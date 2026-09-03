import { id } from "@modernman00/shared-js-lib";
import { esc } from '../global';
import { initTree, centerOnElement, fitToScreen } from './events';
import { showPersonDetails } from './showModal';
import { initOnboardingTour } from './onboardingTour';

// Initialize zoom & pan engine
initTree();

// Initialize first-time onboarding app tour
initOnboardingTour();

const memberSearchInput = id('memberSearchInput');
const searchDropdown = id('searchDropdown');

// 1. Delegated Click Listener for Any Tree Node
document.addEventListener('click', (e) => {
  const node = e.target.closest('.tree-node');
  if (!node) return;

  e.preventDefault();
  e.stopPropagation();

  const nodeFn = (nodeClass) => {
    const first = node.querySelector(nodeClass);
    return first?.dataset?.id || null;
  };

  const name = node.dataset.name || node.querySelector('.node-name')?.textContent?.trim() || nodeFn('.node-fullName') || 'Family Member';
  const role = node.dataset.role || node.querySelector('.node-title')?.textContent?.trim() || nodeFn('.node-relation-hidden') || 'Relative';
  const img = node.dataset.img || node.querySelector('.profile-image')?.src || nodeFn('.node-img') || '/resources/images/profile/avatarM.png';
  const email = node.dataset.email || nodeFn('.node-email') || '';
  const maritalStatus = node.dataset.maritalStatus || nodeFn('.node-maritalStatus') || '';
  const spouseName = node.dataset.spouseName || nodeFn('.node-spouseName') || '';
  const occupation = node.dataset.occupation || nodeFn('.node-occupation') || '';
  const country = node.dataset.country || nodeFn('.node-country') || '';
  const bio = node.dataset.bio || nodeFn('.node-bio') || '';
  const personId = node.dataset.personid || nodeFn('.node-id') || null;

  const nodeData = {
    fullName: name,
    personId: (personId && personId !== '') ? personId : null,
    email: email,
    relation: role,
    img: img,
    maritalStatus: maritalStatus,
    spouseName: spouseName,
    occupation: occupation,
    country: country,
    bio: bio,
    isDeceased: node.dataset.deceased === '1' || node.classList.contains('deceased'),
    isRegistered: node.dataset.registered === 'true',
    familyCode: window.__FAMILY_CODE__ || node.dataset.familycode || ''
  };

  showPersonDetails(nodeData);
});

// 2. Search Box & Instant Spotlight (Supports Balkan FamilyTree and DOM nodes)
memberSearchInput?.addEventListener('input', (e) => {
  const query = e.target.value.trim().toLowerCase();
  if (!searchDropdown) return;

  if (query.length === 0) {
    searchDropdown.style.display = 'none';
    searchDropdown.innerHTML = '';
    return;
  }

  let matches = [];

  // 1. Search Balkan FamilyTree node data
  if (Array.isArray(window.familyTreeNodes) && window.familyTreeNodes.length > 0) {
    matches = window.familyTreeNodes.filter((n) => {
      const name = (n.name || '').toLowerCase();
      const title = (n.title || '').toLowerCase();
      return name.includes(query) || title.includes(query);
    }).map((n) => ({
      id: n.id,
      name: n.name || 'Relative',
      role: n.title || 'Family Member',
      img: n.img || '/resources/images/profile/avatarM.png',
      nodeType: 'balkan'
    }));
  } else if (window.graphData?.nodes && Array.isArray(window.graphData.nodes)) {
    matches = window.graphData.nodes.filter((n) => {
      const name = (n.full_name || `${n.first_name || ''} ${n.last_name || ''}`).toLowerCase();
      const bio = (n.bio || '').toLowerCase();
      return name.includes(query) || bio.includes(query);
    }).map((n) => ({
      id: n.id,
      name: n.full_name || `${n.first_name || ''} ${n.last_name || ''}`.trim() || 'Relative',
      role: n.bio || 'Family Member',
      img: n.avatar_url || '/resources/images/profile/avatarM.png',
      nodeType: 'balkan'
    }));
  }

  // 2. Fallback to DOM elements
  if (matches.length === 0) {
    const allNodes = Array.from(document.querySelectorAll('.tree-node, [data-n-id]'));
    matches = allNodes.filter((n) => {
      const text = n.textContent.toLowerCase();
      return text.includes(query);
    }).map((n) => {
      const name = n.querySelector('.node-name')?.textContent?.trim() || n.dataset.name || 'Relative';
      const role = n.querySelector('.node-title')?.textContent?.trim() || n.dataset.role || '';
      const img = n.querySelector('.profile-image')?.src || n.dataset.img || '/resources/images/profile/avatarM.png';
      const id = n.dataset.nId || n.dataset.id || '';
      return { id, name, role, img, nodeType: 'dom' };
    });
  }

  if (matches.length === 0) {
    searchDropdown.innerHTML = '<div style="padding: 12px 16px; color: #64748b; font-size: 0.85rem;">No relatives found</div>';
    searchDropdown.style.display = 'block';
    return;
  }

  let resultsHtml = '';
  matches.slice(0, 6).forEach((item) => {
    // Every field here is user-controlled family-member data — escape before
    // it goes into innerHTML (SEC-2).
    resultsHtml += `
      <div class="search-result-item" data-id="${esc(item.id)}" data-name="${esc(item.name)}">
        <img src="${esc(item.img)}" alt="${esc(item.name)}">
        <div class="search-result-info">
          <div class="search-result-name">${esc(item.name)}</div>
          <div class="search-result-meta">${esc(item.role)}</div>
        </div>
      </div>
    `;
  });

  searchDropdown.innerHTML = resultsHtml;
  searchDropdown.style.display = 'block';
});

// Select from search dropdown
searchDropdown?.addEventListener('click', (e) => {
  const item = e.target.closest('.search-result-item');
  if (!item) return;

  const targetId = item.dataset.id;
  const targetName = item.dataset.name;
  searchDropdown.style.display = 'none';
  if (memberSearchInput) memberSearchInput.value = targetName;

  // If Balkan FamilyTree instance exists, zoom & center directly
  if (window.family && targetId) {
    try {
      window.family.center(targetId);
      if (typeof window.family.highlight === 'function') {
        window.family.highlight(targetId);
      }
    } catch (err) {
      console.log('[FamilyTree Search] Balkan center:', err);
    }
  }

  // Also add pulse highlight effect to the SVG node
  const targetElem = document.querySelector(`[data-n-id="${targetId}"]`) ||
                     Array.from(document.querySelectorAll('.tree-node, [data-n-id]')).find((el) => el.textContent.includes(targetName));

  if (targetElem) {
    targetElem.classList.add('highlighted');
    setTimeout(() => targetElem.classList.remove('highlighted'), 3500);
  }
});

// Close search dropdown on outside click
document.addEventListener('click', (e) => {
  if (!e.target.closest('.search-box-wrapper')) {
    if (searchDropdown) searchDropdown.style.display = 'none';
  }
});

// 3. Navigation Guide Toggle (Default to Collapsed)
const instructionsToggle = id('instructionsToggle');
const instructions = id('instructions');

// Always keep collapsed by default unless user explicitly opened it
const instructionsState = localStorage.getItem('instructionsCollapsed');
if (instructionsState === 'false' && instructions) {
  instructions.classList.remove('collapsed');
} else if (instructions) {
  instructions.classList.add('collapsed');
}

instructionsToggle?.addEventListener('click', () => {
  if (!instructions) return;
  instructions.classList.toggle('collapsed');
  const isCollapsed = instructions.classList.contains('collapsed');
  localStorage.setItem('instructionsCollapsed', isCollapsed);

  const icon = instructionsToggle.querySelector('i');
  if (icon) {
    icon.className = isCollapsed ? 'bi bi-question-circle' : 'bi bi-x-circle-fill';
  }
});

// Auto-fit to screen after initial render
setTimeout(() => {
  fitToScreen();
}, 200);

// Auto-spotlight node if ?highlight=ID or ?node=ID is in URL
setTimeout(() => {
  try {
    const urlParams = new URLSearchParams(window.location.search);
    const highlightId = urlParams.get('highlight') || urlParams.get('node');
    if (highlightId && window.family && typeof window.family.center === 'function') {
      window.family.center(highlightId);
      const nodeData = Array.isArray(window.familyTreeNodes) ? window.familyTreeNodes.find((n) => String(n.id) === String(highlightId)) : null;
      if (nodeData && typeof showPersonDetails === 'function') {
        const rawNode = window.graphData?.nodes?.find((n) => String(n.id) === String(highlightId));
        showPersonDetails({
          fullName: nodeData.name,
          img: nodeData.img,
          relation: nodeData.title,
          personId: nodeData.legacyId,
          familyCode: window.__FAMILY_CODE__ || '',
          email: rawNode ? rawNode.email : '',
          maritalStatus: rawNode ? rawNode.marital_status : '',
          occupation: rawNode ? rawNode.occupation : '',
          country: rawNode ? rawNode.country_of_residence : '',
          isDeceased: rawNode ? rawNode.is_deceased : false,
          isRegistered: !!nodeData.legacyId
        });
      }
    }
  } catch (e) {
    console.error('Error auto-highlighting node:', e);
  }
}, 500);
