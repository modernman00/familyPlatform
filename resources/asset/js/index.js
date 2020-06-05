
import { Personal } from './data/Personal.js'
import { Contact } from './data/Contact.js'

//import { personal } from "./views/Personal.js";
import { basicView } from "./views/basicView.js";


try {

   
    basicView(Contact, 'contact')
    basicView(Personal, 'personal')
  //  basicViewSelect(Contact, 'contact')


  
    
} catch (e) {

    console.log(e)
    
}



