'use strict';
import { qSel, showError } from './components/global';

import { log } from '@shared'; // to make the bulma navbar menu visible on mobile

/**
 * Tests if the current URL matches the given route.
 *
 * @param {string} url A URL route to test.
 *
 * @returns {boolean} True if the URL matches, otherwise false.
 */
const checkURL = (url) =>
  !!window.location.pathname.match(new RegExp(`^/${url}(?:/[^/]+)*$`));

try {
  const $navbarBurgers = Array.prototype.slice.call(
    document.querySelectorAll('.navbar-burger'),
    0,
  );

  // Add a click event on each of them
  $navbarBurgers.forEach((el) => {
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
  showError(error);
}

// Get all "navbar-burger" elements

if (window.location.pathname === '/register') {
  qSel('.signUp').style.display = 'none'; // navbar mgt

  import(
    /* webpackChunkName: 'register' */
    /* webpackPrefetch: true */
    './components/register/'
  )
    .then((module) => module.default)
    .catch((err) => showError(err));
} else if (window.location.pathname === '/allMembers') {
  qSel('.allMemberNav').style.display = 'none'; //allMemberNav

  import(
    /* webpackChunkName: 'all_members' */
    /* webpackPrefetch: true */
    './components/allMembers/'
  )
    .then((module) => module.default)
    .catch((err) => showError(err));
} else if (
  window.location.pathname === '/login' ||
  window.location.pathname === '/lasu'
) {
  qSel('.login').style.display = 'none'; // navbar mgt

  import(
    /* webpackChunkName: 'login' */
    /* webpackPrefetch: true */
    './components/login/'
  )
    .then((module) => module.default)
    .catch((err) => showError(err));
} else if (window.location.pathname === '/login/forgot') {
  qSel('.signup_login').style.display = 'none'; // navbar mgt

  import(
    /* webpackChunkName: 'forgotPwd' */
    /* webpackPrefetch: true */
    './components/forgotPwd/'
  )
    .then((module) => module.default)
    .catch((err) => showError(err));
} else if (window.location.pathname === '/login/code') {
  qSel('.signup_login').style.display = 'none'; // navbar mgt

  import(
    /* webpackChunkName: 'code' */
    /* webpackPrefetch: true */
    './components/generateCode/Code'
  )
    .then((module) => module.default)
    .catch((err) => showError(err));
} else if (window.location.pathname === '/member/ProfilePage') {
  qSel('.profilePageNav').style.display = 'none'; // navbar mgt

  import(
    /* webpackChunkName: 'profilePage' */
    /* webpackPrefetch: true */
    './components/profilePage/'
  )
    .then((module) => module.default)
    .catch((err) => showError(err));
} else if (window.location.pathname === '/login/changePW') {
  qSel('.login').style.display = 'none'; // navbar mgt
  qSel('.signUp').style.display = 'none'; // navbar mgt
  // qSel('#loader').style.display ="none" // loader
  import(
    /* webpackChunkName: 'changePW' */
    /* webpackPrefetch: true */
    './components/changePW/'
  )
    .then((module) => module.default)
    .catch((err) => showError(err));
} else if (window.location.pathname === '/profilepage/img') {
  // qSel('.login').style.display ="none" // navbar mgt
  import(
    /* webpackChunkName: 'img' */
    /* webpackPrefetch: true */
    './components/profilePage/imgViewer'
  )
    .then((module) => module.default)
    .catch((err) => showError(err));
} else if (window.location.pathname === '/createFamilyCode') {
  import(
    /* webpackChunkName: 'familyCode' */
    /* webpackPrefetch: true */
    './components/register/familyCode'
  )
    .then((module) => module.default)
    .catch((err) => showError(err));
} else if (window.location.pathname === '/register/nextStep') {
  qSel('.login').style.display = 'none'; // navbar mgt
  qSel('.signUp').style.display = 'none'; // navbar mgt
} else if (checkURL('accountSetting')) {
  import(
    /* webpackChunkName: 'accountSetting' */
    /* webpackPrefetch: true */
    './components/accountSetting'
  )
    .then((module) => module.default)
    .catch((err) => showError(err));
} else if (window.location.pathname === '/organogram') {
  qSel('.familyTreeNav').style.display = 'none'; // navbar mgt

  import(
    /* webpackChunkName: 'organogram' */
    /* webpackPrefetch: true */
    './components/familyTree/index.js'
  )
    .then((module) => module.default)
    .catch((err) => showError(err));
} else if (window.location.pathname === '/allMembers/getProfile') {
  qSel('.familyTreeNav').style.display = 'none'; // navbar mgt
  qSel('.notification_count').style.display = 'none'; // navbar mgt

  // import (
  // import { setCookie } from '../../../node_modules/y/Cookie';
  /* webpackChunkName: 'getProfile' */
  //     /* webpackPrefetch: true */
  //     './components/familyTree/index.js'
  // )
  // .then((module) => module.default)
  //     .catch((err) => showError(err))
}
