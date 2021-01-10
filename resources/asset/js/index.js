import {id, idValue, idInnerHTML, qSel, log} from "./global"
import "./components/register/index"
import "./components/login/index"
//import "./components/small-Input"
import "./components/register/processForm"
import "./components/register/modal/kids"
import "./components/register/modal/siblings"
import "./cust/main"
import "./components/profilePage"
import "./components/FilePreview"
import "./components/organogram"
// import "./components/modal/profile"

if (id('profilePage')) {
    import("./components/modal/profile")
    .then(result => result);
} else if (id('registration')) {
    import ('./components/small-Input')
    .then(result => result)
}


