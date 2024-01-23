"use strict";
import { qSel, showError } from "./components/global"


// to make the bulma navbar menu visible on mobile


    try {

        const $navbarBurgers = Array.prototype.slice.call(document.querySelectorAll('.navbar-burger'), 0);

        // Add a click event on each of them
        $navbarBurgers.forEach(el => {
            el.addEventListener('click', () => {

                // Get the target from the "data-target" attribute
                const target = el.dataset.target;
                const $target = document.getElementById(target);

                // Toggle the "is-active" class on both the "navbar-burger" and the "navbar-menu"
                el.classList.toggle('is-active');
                $target.classList.toggle('is-active');

            });
        });

    } catch (error) {
        showError(error)
    }

    // Get all "navbar-burger" elements




if (window.location.pathname === '/register') {
    qSel('.signUp').style.display = "none" // navbar mgt

    import (
        /* webpackChunkName: 'codeSplit/register' */
        /* webpackPrefetch: true */
        './components/register/'
    ).then((module) => module.default).catch((err) => showError(err))

} else if (window.location.pathname === '/allMembers') {
    qSel('.allMemberNav').style.display = "none" //allMemberNav

    import (
        /* webpackChunkName: 'codeSplit/all_members' */
        /* webpackPrefetch: true */
        './components/allMembers/'
    )
    .then((module) => module.default)
        .catch((err) => showError(err))

} else if (window.location.pathname === '/login') {
    qSel('.login').style.display = "none" // navbar mgt

    import (
        /* webpackChunkName: 'codeSplit/login' */
        /* webpackPrefetch: true */
        './components/login/'
    )
    .then((module) => module.default)
        .catch((err) => showError(err))

} else if (window.location.pathname === '/lasu') {
    qSel('.login').style.display = "none" // navbar mgt

    import (
        /* webpackChunkName: 'codeSplit/login' */
        /* webpackPrefetch: true */
        './components/login/admin'
    )
    .then((module) => module.default)
        .catch((err) => showError(err))

} else if (window.location.pathname === '/login/forgot') {
    qSel('.signup_login').style.display = "none" // navbar mgt

    import (
        /* webpackChunkName: 'codeSplit/forgotPwd' */
        /* webpackPrefetch: true */
        './components/forgotPwd/'
    )
    .then((module) => module.default)
        .catch((err) => showError(err))

} else if (window.location.pathname === '/login/code') {

    qSel('.signup_login').style.display = "none" // navbar mgt

    import (
        /* webpackChunkName: 'codeSplit/code' */
        /* webpackPrefetch: true */
        './components/generateCode/Code'
    )
    .then((module) => module.default)
        .catch((err) => showError(err))
} else if (window.location.pathname === '/member/ProfilePage') {
     qSel('.profilePageNav').style.display ="none" // navbar mgt

    import (
        /* webpackChunkName: 'codeSplit/profilePage' */
        /* webpackPrefetch: true */
        './components/profilePage/'
    )
    .then((module) => module.default)
        .catch((err) => showError(err))

} else if (window.location.pathname === '/login/changePW') {
    qSel('.login').style.display ="none" // navbar mgt
    qSel('.signUp').style.display ="none" // navbar mgt
    // qSel('#loader').style.display ="none" // loader
    import (
        /* webpackChunkName: 'codeSplit/changePW' */
        /* webpackPrefetch: true */
        './components/changePW/'
    )
    .then((module) => module.default)
        .catch((err) => showError(err))
} else if (window.location.pathname === '/profilepage/img') {
    // qSel('.login').style.display ="none" // navbar mgt
    import (
        /* webpackChunkName: 'codeSplit/img' */
        /* webpackPrefetch: true */
        './components/profilePage/imgViewer'
    )
    .then((module) => module.default)
        .catch((err) => showError(err))
} else if (window.location.pathname === '/createFamilyCode') {

    import (
        /* webpackChunkName: 'codeSplit/familyCode' */
        /* webpackPrefetch: true */
        './components/register/familyCode'
    )
    .then((module) => module.default)
        .catch((err) => showError(err))
}else if (window.location.pathname === '/register/nextStep') {
      qSel('.login').style.display ="none" // navbar mgt
    qSel('.signUp').style.display ="none" // navbar mgt
    
}else if (window.location.pathname === '/accountSetting') {

    import (
        /* webpackChunkName: 'codeSplit/accountSetting' */
        /* webpackPrefetch: true */
        './components/accountSetting'
    )
    .then((module) => module.default)
        .catch((err) => showError(err))
} else if (window.location.pathname === '/organogram') {
    qSel('.familyTreeNav').style.display ="none" // navbar mgt
  

    import (
        /* webpackChunkName: 'codeSplit/organogram' */
        /* webpackPrefetch: true */
        './components/familyTree/index.js'
    )
    .then((module) => module.default)
        .catch((err) => showError(err))
}else if (window.location.pathname === '/allMembers/getProfile') {
    qSel('.familyTreeNav').style.display ="none" // navbar mgt
    qSel('.notification_count').style.display ="none" // navbar mgt
  

    // import (
    //     /* webpackChunkName: 'codeSplit/getProfile' */
    //     /* webpackPrefetch: true */
    //     './components/familyTree/index.js'
    // )
    // .then((module) => module.default)
    //     .catch((err) => showError(err))
}