"use strict";
import { qSel, showError } from "./components/global"


// to make the bulma navbar menu visible on mobile
document.addEventListener('DOMContentLoaded', () => {

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


});

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
    // qSel('.login').style.display ="none" // navbar mgt

    import (
        /* webpackChunkName: 'codeSplit/profilePage' */
        /* webpackPrefetch: true */
        './components/profilePage/'
    )
    .then((module) => module.default)
        .catch((err) => showError(err))

} else if (window.location.pathname === '/login/changePW') {
    // qSel('.login').style.display ="none" // navbar mgt
    import (
        /* webpackChunkName: 'codeSplit/changePW' */
        /* webpackPrefetch: true */
        './components/forgotPwd/changePW'
    )
    .then((module) => module.default)
        .catch((err) => showError(err))
} else if (window.location.pathname === '/profilepage/img') {
    // qSel('.login').style.display ="none" // navbar mgt
    import (
        /* webpackChunkName: 'codeSplit/changePW' */
        /* webpackPrefetch: true */
        './components/profilePage/imgViewer'
    )
    .then((module) => module.default)
        .catch((err) => showError(err))
}