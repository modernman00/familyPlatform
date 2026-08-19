import { id } from '@shared';
import { initTree, centerOnElement, fitToScreen } from './events';
import { showPersonDetails } from './showModal';

// Initialize zoom & pan engine
initTree();

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

// 2. Search Box & Instant Spotlight
memberSearchInput?.addEventListener('input', (e) => {
  const query = e.target.value.trim().toLowerCase();
  if (!searchDropdown) return;

  if (query.length === 0) {
    searchDropdown.style.display = 'none';
    searchDropdown.innerHTML = '';
    return;
  }

  const allNodes = Array.from(document.querySelectorAll('.tree-node'));
  const matches = allNodes.filter((n) => {
    const text = n.textContent.toLowerCase();
    return text.includes(query);
  });

  if (matches.length === 0) {
    searchDropdown.innerHTML = '<div style="padding: 12px 16px; color: var(--text-muted); font-size: 0.85rem;">No relatives found</div>';
    searchDropdown.style.display = 'block';
    return;
  }

  let resultsHtml = '';
  matches.slice(0, 6).forEach((n) => {
    const name = n.querySelector('.node-name')?.textContent?.trim() || 'Relative';
    const role = n.querySelector('.node-title')?.textContent?.trim() || '';
    const img = n.querySelector('.profile-image')?.src || '/resources/images/profile/avatarM.png';

    resultsHtml += `
      <div class="search-result-item" data-name="${name}">
        <img src="${img}" alt="${name}">
        <div class="search-result-info">
          <div class="search-result-name">${name}</div>
          <div class="search-result-meta">${role}</div>
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

  const targetName = item.dataset.name;
  searchDropdown.style.display = 'none';
  if (memberSearchInput) memberSearchInput.value = targetName;

  const targetElem = Array.from(document.querySelectorAll('.tree-node')).find((el) => {
    return el.textContent.includes(targetName);
  });

  if (targetElem) {
    centerOnElement(targetElem);
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
