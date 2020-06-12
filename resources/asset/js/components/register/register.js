
import { Personal } from '../../data/Personal.js'
import { Contact } from '../../data/Contact.js'
import { Input } from "../formBuilder.js";

try {

    Input(Contact, 'contact')
    Input(Personal, 'personal')
  //  InputSelect(Contact, 'contact')


  
    
} catch (e) {

    console.log(e)
    
}



