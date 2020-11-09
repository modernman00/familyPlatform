
import { Personal } from '../../data/Personal.js'
import { Contact } from '../../data/Contact.js'
import { Work } from '../../data/Work.js'
import { Input } from "../formBuilder.js";
import { Interest } from "../../data/Interest";
import { Account } from "../../data/Account";

try {

   Input(Contact, 'contact')
    Input(Personal, 'personal')
    Input(Work, 'work')
    Input(Interest, 'interest')
    Input(Account, 'account')
  
    
} catch (e) {

    console.log(e)
    
}



