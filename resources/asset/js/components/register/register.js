
import { Personal } from '../../data/Personal.js'
import { Contact } from '../../data/Contact.js'
import { Work } from '../../data/Work.js'
import { Input } from "../formBuilder.js";

try {

    Input(Contact, 'contact')
    Input(Personal, 'personal')
    Input(Work, 'work')




  
    
} catch (e) {

    console.log(e)
    
}



