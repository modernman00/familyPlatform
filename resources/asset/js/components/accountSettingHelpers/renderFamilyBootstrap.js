
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

   // Premium, Senior Designer Card Layout
   return `
    <div class="family-member-card animate-reveal wrapper-${kids_sib}-${no}">
      
      <!-- Card Header -->
      <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-2" style="border-color: #f0f2f5 !important;">
          <div class="d-flex align-items-center">
             <div class="member-badge me-2">
                #${no}
             </div>
             <h6 class="mb-0 fw-bold text-dark text-uppercase" style="letter-spacing: 0.5px; font-size: 0.9rem;">
                ${labelText} Information
             </h6>
          </div>
          <!-- Optional: Icon or action could go here -->
          <i class="bi bi-person-badge text-muted opacity-50"></i>
      </div>

      <!-- Form Grid -->
      <div class="row g-4 pl-2">
      
        <!-- Relationship Select -->
        <div class="col-md-5">
           <label class="label-premium" for="${kids_sib}_option${no}">
               ${optionsHtmlText}
           </label>
           <div class="input-group-modern">
               <span class="input-icon"><i class="bi bi-diagram-3"></i></span>
               <select class="form-select border-0 shadow-none bg-transparent ps-0" name="${kids_sib}_option${no}" id="${kids_sib}_option${no}">
                  ${optionsHtml}
               </select>
           </div>
        </div>

        <!-- Full Name Input -->
        <div class="col-md-7">
           <label class="label-premium" for="${kids_sib}_name${no}">
               Full Name
           </label>
           <div class="input-group-modern">
               <span class="input-icon"><i class="bi bi-person"></i></span>
               <input 
                  type="text" 
                  class="form-control-modern" 
                  placeholder="e.g. John Doe" 
                  name="${kids_sib}_name${no}" 
                  id="${kids_sib}_name${no}"
                  autocomplete="off"
               >
           </div>
        </div>

        <!-- Email Input (Full Width) -->
        <div class="col-12">
           <label class="label-premium" for="${kids_sib}_email${no}">
               Email Address
           </label>
           <div class="input-group-modern">
               <span class="input-icon"><i class="bi bi-envelope"></i></span>
               <input 
                  type="email" 
                  class="form-control-modern" 
                  placeholder="e.g. john.doe@example.com" 
                  name="${kids_sib}_email${no}" 
                  id="${kids_sib}_email${no}"
                  autocomplete="off"
               >
           </div>
           <!-- Dynamic help text area -->
           <div class="form-text mt-2 ps-1" id="${kids_sib}_email${no}_help" style="min-height: 20px; font-size: 0.8rem;"></div>
        </div>

      </div>
    </div>
  `;
};
