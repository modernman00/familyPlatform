
export const renderHtmlFamilyBootstrap = (family, no) => {
   if (!no) return "";

   const kids_sib = (family === "children") ? "children" : "sibling";
   const labelText = (family === "children") ? "Child" : "Sibling";

   let optionsHtmlText = (family === "children") ? "With Spouse?" : "Relationship";

   let optionsHtml = `
    <option value="select" disabled selected>Select status</option>
    <option value="With Spouse">With Spouse</option>
    <option value="Not With Spouse">Single / Other</option>
  `;

   if (family === "sibling") {
      optionsHtml = `
      <option value="select" disabled selected>Select type</option>
      <option value="Same_Mother_Father">Same Father & Mother</option>
      <option value="Same_Father">Same Father Only</option>
      <option value="Same_Mother">Same Mother Only</option>
    `;
   }

   // Match the new Account Settings "Spouse Details" container style
   return `
    <div class="p-4 rounded-3 mb-4 wrapper-${kids_sib}-${no}" style="background-color: #f8fafc; border: 1px solid #e2e8f0;">
      
      <h6 class="fw-bold mb-3 text-dark text-uppercase" style="letter-spacing: 0.5px; font-size: 0.9rem;">
        #${no} ${labelText} Information
      </h6>

      <div class="row g-3">
      
        <!-- Relationship Select -->
        <div class="col-md-5">
           <label class="form-label" for="${kids_sib}_option${no}">
               ${optionsHtmlText}
           </label>
           <select class="form-select" name="${kids_sib}_option${no}" id="${kids_sib}_option${no}">
              ${optionsHtml}
           </select>
        </div>

        <!-- Full Name Input -->
        <div class="col-md-7">
           <label class="form-label" for="${kids_sib}_name${no}">
               Full Name
           </label>
           <input 
              type="text" 
              class="form-control" 
              placeholder="e.g. John Doe" 
              name="${kids_sib}_name${no}" 
              id="${kids_sib}_name${no}"
              autocomplete="off"
           >
        </div>

        <!-- Email Input (Full Width) -->
        <div class="col-12">
           <label class="form-label" for="${kids_sib}_email${no}">
               Email Address
           </label>
           <input 
              type="email" 
              class="form-control" 
              placeholder="e.g. john.doe@example.com" 
              name="${kids_sib}_email${no}" 
              id="${kids_sib}_email${no}"
              autocomplete="off"
           >
           <!-- Dynamic help text area -->
           <div class="form-text text-danger mt-1" id="${kids_sib}_email${no}_help" style="min-height: 20px; font-size: 0.8rem;"></div>
        </div>

      </div>
    </div>
  `;
};
