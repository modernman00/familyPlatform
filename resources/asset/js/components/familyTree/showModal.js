 import { id } from '@shared'
 import { familyData } from './familyData' // Import the family data from './familydata'

 
    const personModal = id('personModal');
      const modalBody = id('modalBody');
 
 // Displays detailed info about a person in the modal
export const  showPersonDetails = (personData) => {
  // destroy the modal if it's already open
  if (personModal.classList.contains('show')) {
    personModal.classList.remove('show');
  }

        // deconstruct the data object and assign it to variables
  const { img, relation, fullName, spouseName,  occupation,  country } = personData;

  // Inject HTML into modal body with person's details
  modalBody.innerHTML = `
    <div class="person-detail">
      <img src="${img}" alt="${fullName}" class="person-image">
      <div class="person-info">
        <h2 class="person-name">${fullName}</h2>
        <div class="person-relation">${relation}</div>
      </div>
    </div>
    <div class="detail-grid">
      <div class="detail-item">
        <div class="detail-label">Spouse</div>
        <div class="detail-value">${spouseName || 'N/A'}</div>
      </div>
 
      <div class="detail-item">
        <div class="detail-label">Occupation</div>
        <div class="detail-value">${occupation}</div>
      </div>
      ${country ? `
        <div class="detail-item">
          <div class="detail-label">country</div>
          <div class="detail-value">${country}</div>
        </div>
      ` : ''}
    </div>
  `;

   // Show the modal
  personModal.style.display = 'flex';
}