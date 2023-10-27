export const renderHtmlFamily = (family, no) => {

    if (no) {

        const kids_sib = (family == "addChildren") ? "kid" : "sibling"


        let optionsHtml = `
      <option value='Choose'>Choose</option>
      <option value='With Spouse'>With Spouse</option>
      <option value='Not With Spouse'>Not With Spouse</option>
    `;

        if (family === "addSiblings") {
            optionsHtml = `
                <option value='Choose'>Choose</option>
                <option value='Same_Mother_Father'>Same Mother & Father</option>
                <option value='Same_Father'>Only Same Father</option>
                <option value='Same_Mother'>Only Same Mother</option>`;
        }

        return `
            <div class="field-body">
                <div class="field">
                    <p class="control is-expanded">
                        <span class="select is-normal is-success is-fullwidth">
                            <select name="${kids_sib}_option${no}" id="${kids_sib}_option${no}">
                                ${optionsHtml}
                            </select>
                        </span>
                    </p>
                </div>

                <div class="field">
                    <p class="control is-expanded">
                        <input type="text" placeholder = "Enter ${kids_sib}'s full name - ${no}"  name =${kids_sib}_name${no} class="input input is-normal " id="${kids_sib}_name${no}">
                    </p>
                </div>          

                <div class="field">
                    <p class="control is-expanded has-icons-left">
                        <input type="email" placeholder = "Enter ${kids_sib}'s email - ${no}" value = "Please, provide email address" name=${kids_sib}_email${no} class="input input is-normal " id="${kids_sib}_email${no}">

                        <span class="icon is-small is-left">
                            <i class="fas fa-envelope"></i>
                        </span>
                    </p>
                    <p class="help is-danger" id="${kids_sib}_email${no}_help"></p>
                </div>

            </div>`
    }

}