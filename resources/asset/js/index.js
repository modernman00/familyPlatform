import { id, idValue, idInnerHTML, qSel, log } from "./global"
import "./components/register/index"
//import "./components/login/index"
//import "./components/small-Input"
import "./components/register/processForm"
import "./components/register/modal/kids"
import "./components/register/modal/siblings"
import "./cust/main"
import "./components/profilePage"
//import "./components/FilePreview"
//import "./components/organogram"
import "./components/modal/profile"
import "./components/hidden"
//import "./components/login/submitFormLogin"



// if (id('profilePage')) {
//     const loadProfile = await import("./components/modal/profile")

// } 
async function loadSmallInput() {
    await import( 
        /* webpackChunkName: '/components/small-input' */ 
        /* webpackPrefetch: true */
        './components/smallInput')
}

if(window.location.pathname === '/register') {
    console.log("it worked well")
     import(/* webpackChunkName: '/codeSplit/smallInput' */ 
        /* webpackPrefetch: true */
        './components/smallInput')
        .then((module)=> module.default())
        .catch((err)=> console.log("MAD ERROR!! " + err))
}



// CODE SPLITTING BASED ON ROUTE




