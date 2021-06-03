"use strict";
import {qSel, showError} from "./components/global"


if (window.location.pathname === '/register') {
    qSel('.signUp').style.display ="none" // navbar mgt
   
    import(
        /* webpackChunkName: 'codeSplit/register' */
        /* webpackPrefetch: true */
        './components/register/'
    ).then((module) => module.default).catch((err) => showError(err))

} else if (window.location.pathname === '/allMembers') {
   
    import(
        /* webpackChunkName: 'codeSplit/all_members' */
        /* webpackPrefetch: true */
        './components/allMembers/'
    )
        .then((module) => module.default)
        .catch((err) => showError(err))

} else if (window.location.pathname === '/login') {
     qSel('.login').style.display ="none" // navbar mgt

    import(
        /* webpackChunkName: 'codeSplit/login' */
        /* webpackPrefetch: true */
        './components/login/'
    )
        .then((module) => module.default)
        .catch((err) => showError(err))

} else if (window.location.pathname === '/login/forgot') {
    qSel('.signup_login').style.display ="none" // navbar mgt
 
    import(
        /* webpackChunkName: 'codeSplit/forgotPwd' */
        /* webpackPrefetch: true */
        './components/forgotPwd/'
    )
        .then((module) => module.default)
        .catch((err) => showError(err))

} else if (window.location.pathname === '/login/code') {
    
    qSel('.signup_login').style.display ="none" // navbar mgt
 
    import(
        /* webpackChunkName: 'codeSplit/code' */
        /* webpackPrefetch: true */
        './components/generateCode/Code'
    )
        .then((module) => module.default)
        .catch((err) => showError(err))
} 


else if (window.location.pathname === '/member/ProfilePage') {
    // qSel('.login').style.display ="none" // navbar mgt

    import(
        /* webpackChunkName: 'codeSplit/profilePage' */
        /* webpackPrefetch: true */
        './components/profilePage/'
    )
        .then((module) => module.default)
        .catch((err) => showError(err))

} else if (window.location.pathname === '/login/changePW') {
    // qSel('.login').style.display ="none" // navbar mgt
    import(
        /* webpackChunkName: 'codeSplit/changePW' */
        /* webpackPrefetch: true */
        './components/forgotPwd/changePW'
    )
        .then((module) => module.default)
        .catch((err) => showError(err))
} 





