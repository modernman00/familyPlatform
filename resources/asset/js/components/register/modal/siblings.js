import { id, qSel } from "./../../../global";

const show = (e) => {
    const siblingNo = e.target.value;
//    const checkAppend = qSel('.appendLabel')

//         if(checkAppend || id(`noSiblings${no}`) || id(`noSiblingsEmail${no}`)) {
//             checkAppend.remove()
//         }
    // use the loop to generate the number of input
    for (let i = 0; i < siblingNo; i++) {

     

    //    checkAppend && checkAppend.remove()

        const no = i + 1  
        const msg = (no > 1 ) ? "Please, enter their names and emails": "Please, enter your child name and email" 

        const getSelectHelp = id('noSiblings_help')

        getSelectHelp.innerHTML = msg 
        getSelectHelp.style.fontSize = '1rem'     

        const addnoSiblings = ` <div class="row appendLabel">
            <div class="col">
            <input type="text" placeholder = "Enter sibling's full name - ${no}" name ="sibling_name${no}" class="form-control" id="sibling_name${no}">
            </div>
            <div class="col">
           <input type="email" placeholder = "Enter sibling's email - ${no}" name="sibling_email${no}" class="form-control" id="sibling_email${no}">
           </div>
        </div><br>`


        if (!id(`noSiblings${no}`) || !id(`noSiblingsEmail${no}`))
            
            id('noSiblings_div').insertAdjacentHTML('afterend', addnoSiblings)
      
            
    }

}
// this is to activate the onchange event
id('noSiblings').addEventListener('change', show)

