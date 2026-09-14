
export const renderHtmlFamilyBootstrap = (family, no) => {
   if (!no) return "";

   const kids_sib = (family === "children") ? "children" : "sibling";
   const labelText = (family === "children") ? "Child" : "Sibling";

   let optionsHtmlText = (family === "children") ? "With Spouse?" : "Relationship";

   let optionsHtml = `
    <option value="select" disabled selected style="color: #64748b; background-color: #ffffff;">Select status</option>
    <option value="With Spouse" style="color: #0f172a; background-color: #ffffff;">With Spouse</option>
    <option value="Not With Spouse" style="color: #0f172a; background-color: #ffffff;">Single / Other</option>
  `;

   if (family === "sibling") {
      optionsHtml = `
      <option value="select" disabled selected style="color: #64748b; background-color: #ffffff;">Select type</option>
      <option value="Same_Mother_Father" style="color: #0f172a; background-color: #ffffff;">Same Father & Mother</option>
      <option value="Same_Father" style="color: #0f172a; background-color: #ffffff;">Same Father Only</option>
      <option value="Same_Mother" style="color: #0f172a; background-color: #ffffff;">Same Mother Only</option>
    `;
   }

   // Match the new Account Settings "Spouse Details" container style with high contrast fonts
   return `
    <div class="p-4 rounded-3 mb-4 wrapper-${kids_sib}-${no}" style="background-color: #ffffff; border: 1.5px solid #cbd5e1; box-shadow: 0 2px 4px rgba(0,0,0,0.03);">
      
      <h6 class="fw-bold mb-3 text-uppercase" style="color: #0f172a; letter-spacing: 0.5px; font-size: 0.9rem;">
        <i class="bi bi-${family === 'children' ? 'person-heart text-primary' : 'person-lines-fill text-indigo'} me-1"></i> #${no} ${labelText} Information
      </h6>

      <div class="row g-3">
      
        <!-- Relationship Select -->
        <div class="col-md-4">
           <label class="form-label text-dark fw-bold" for="${kids_sib}_option${no}">
               ${optionsHtmlText}
           </label>
           <select class="form-select text-dark bg-white border" name="${kids_sib}_option${no}" id="${kids_sib}_option${no}">
              ${optionsHtml}
           </select>
        </div>

        <!-- First Name Input -->
        <div class="col-md-4">
           <label class="form-label text-dark fw-bold" for="${kids_sib}_first_name${no}">
               First Name
           </label>
           <input 
              type="text" 
              class="form-control text-dark bg-white border" 
              placeholder="e.g. John" 
              name="${kids_sib}_first_name${no}" 
              id="${kids_sib}_first_name${no}"
              autocomplete="off"
           >
        </div>

        <!-- Last Name Input -->
        <div class="col-md-4">
           <label class="form-label text-dark fw-bold" for="${kids_sib}_last_name${no}">
               Last Name
           </label>
           <input 
              type="text" 
              class="form-control text-dark bg-white border" 
              placeholder="e.g. Doe" 
              name="${kids_sib}_last_name${no}" 
              id="${kids_sib}_last_name${no}"
              autocomplete="off"
           >
        </div>

        <!-- Email Input (Full Width) -->
        <div class="col-12">
           <label class="form-label text-dark fw-bold" for="${kids_sib}_email${no}">
               Email Address
           </label>
           <input 
              type="email" 
              class="form-control text-dark bg-white border" 
              placeholder="e.g. john.doe@example.com" 
              name="${kids_sib}_email${no}" 
              id="${kids_sib}_email${no}"
              autocomplete="off"
           >
           <!-- Dynamic help text area -->
           <div class="form-text text-danger mt-1 fw-semibold" id="${kids_sib}_email${no}_help" style="min-height: 20px; font-size: 0.82rem;"></div>
        </div>

      </div>
    </div>
  `;
};
