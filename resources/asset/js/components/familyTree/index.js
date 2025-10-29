import { id, log, getApiData } from '@shared'
import { initTree } from './events'
import { showPersonDetails } from './showModal'
import axios from 'axios';


const modalBody = id('modalBody');
const closeModal = id('closeModal');
const personModal = id('personModal');


  log('tree loaded')
  initTree();

   id('familyTree').addEventListener('click', (e) => {
    const node = e.target.closest('.tree-node');
    if (!node) return;
    const name = node.querySelector('.node-name')?.textContent?.trim();

    const nodeFn = (nodeClass) => {
      const first = node.querySelector(nodeClass);
      return first?.dataset?.id || null;
      
    }

    const email = nodeFn('.node-email');
    const id  = nodeFn('.node-id');
    const img = nodeFn('.node-img');
    const fullName = nodeFn('.node-fullName');
    const maritalStatus = nodeFn('.node-maritalStatus');
    const spouseName = nodeFn('.node-spouseName');
    const occupation = nodeFn('.node-occupation');
  
   const detailData = {
     fullName: fullName,
     email: email,
     img: img,
     maritalStatus: maritalStatus,
     spouseName: spouseName,
     occupation: occupation,
     
   }

    showPersonDetails(detailData);


  })


// When a person node is clicked, show their details
// document.querySelectorAll('.tree-node').forEach(node => {
//   node.addEventListener('click', (e) => {
//     e.stopPropagation(); // Prevent bubbling to parent

//      // Get the name from the .node-name span
//   const name = node.querySelector('.node-name')?.textContent?.trim();

//         // Get the data-id from the .node-id span
//   const idSpan = node.querySelector('.node-id');
//   const dataId = idSpan?.dataset?.id || null;
//     showPersonDetails(name);
//   });
// });

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

