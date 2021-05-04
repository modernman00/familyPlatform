"use strict";
import {log, showError} from "./components/global"


// const splitCode = (path) => {
//     import(
//         /* webpackChunkName: 'codeSplit/small_input' */
//         /* webpackPrefetch: true */
//         path
//     )
//         .then((module) => module.default())
//         .catch((err) => log("MAD ERROR!! " + err.message))
// }




if (window.location.pathname === '/register') {

    import(
        /* webpackChunkName: 'codeSplit/register' */
        /* webpackPrefetch: true */
        './components/register/index'
    )
        .then((module) => module.default)
        .catch((err) => showError(err))

} else if (window.location.pathname === '/allMembers') {
    log("it worked allMembers")
    import(
        /* webpackChunkName: 'codeSplit/all_members' */
        /* webpackPrefetch: true */
        './components/allMembers/index'
    )
        .then((module) => module.default)
        .catch((err) => showError(err))
} else if (window.location.pathname === '/login') {
    log("it worked login")
    import(
        /* webpackChunkName: 'codeSplit/login' */
        /* webpackPrefetch: true */
        './components/login/index'
    )
        .then((module) => module.default)
        .catch((err) => showError(err))
}



// CODE SPLITTING BASED ON ROUTE




