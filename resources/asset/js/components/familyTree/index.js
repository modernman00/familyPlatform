import { id } from '@shared'
import { initTree } from './events'
import { showPersonDetails } from './showModal'


const modalBody = id('modalBody');
const closeModal = id('closeModal');
const personModal = id('personModal');

initTree();

id('familyTree').addEventListener('click', (e) => {
  const node = e.target.closest('.tree-node');
  if (!node) return;
  const name = node.querySelector('.node-name')?.textContent?.trim();

  const nodeFn = (nodeClass) => {
    const first = node.querySelector(nodeClass);
    return first?.dataset?.id || null;
  }

  const personId = nodeFn('.node-id');

  if (personId !== null && personId !== '') {
    // then redirect to seeprofile page
    window.location.href = `/allMembers/seeProfile/${personId}`;
  } else {
    const email = nodeFn('.node-email');
    const relation = nodeFn('.node-relation');
    const img = nodeFn('.node-img');
    const maritalStatus = nodeFn('.node-maritalStatus');
    const spouseName = nodeFn('.node-spouseName');
    const occupation = nodeFn('.node-occupation');
    const country = nodeFn('.node-country');


    const detailData = {
      fullName: name,
      email: email,
      img: img,
      maritalStatus: maritalStatus,
      spouseName: spouseName,
      occupation: occupation,
      relation: relation,
      country: country,
      personId: personId,
    }

    showPersonDetails(detailData);
  }



})


// Close modal when "X" button is clicked
closeModal.addEventListener('click', () => {
  personModal.style.display = 'none';
});

// Close modal when clicking outside the modal content
window.addEventListener('click', (e) => {
  if (e.target === personModal) {
    personModal.style.display = 'none';
  }
});

// ===== INSTRUCTIONS TOGGLE =====
const instructionsToggle = id('instructionsToggle');
const instructions = id('instructions');

// Check localStorage for saved state
const instructionsState = localStorage.getItem('instructionsCollapsed');
if (instructionsState === 'false') {
  instructions.classList.remove('collapsed');
}

// Toggle instructions visibility
instructionsToggle?.addEventListener('click', () => {
  instructions.classList.toggle('collapsed');
  const isCollapsed = instructions.classList.contains('collapsed');
  localStorage.setItem('instructionsCollapsed', isCollapsed);

  // Update button icon
  const icon = instructionsToggle.querySelector('i');
  if (icon) {
    icon.className = isCollapsed ? 'bi bi-question-circle-fill' : 'bi bi-x-circle-fill';
  }
});
