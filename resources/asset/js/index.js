'use strict';
import axios from 'axios';
import Alpine from 'alpinejs';
import { qSel, showError } from '@modernman00/shared-js-lib';
import Swal from 'sweetalert2';
import PWAManager from './components/pwa/pwaManager';
import { initDarkMode } from './components/darkMode';

import { profileFeed } from './components/profilePage/feedComponent';
import { profileSidebar, upcomingEvents } from './components/profilePage/sidebarComponents';

window.Swal = Swal;
window.Alpine = Alpine;
Alpine.data('profileFeed', profileFeed);
Alpine.data('profileSidebar', profileSidebar);
Alpine.data('upcomingEvents', upcomingEvents);
window.profileFeed = profileFeed;
window.profileSidebar = profileSidebar;
window.upcomingEvents = upcomingEvents;

window.pwaManager = new PWAManager();
initDarkMode();
let routePromise = Promise.resolve();

// The server occasionally finds the session's CSRF token missing (e.g. session
// data expired/evicted between page load and this request) and responds 401
// with "We are not familiar with the nature of your activities.". That same
// response always carries a fresh Set-Cookie: XSRF-TOKEN, which the browser
// applies immediately — so a single automatic retry of the exact same request
// picks up the new cookie and succeeds transparently instead of surfacing a
// dead-end error the user has to work around by reloading the page.
axios.interceptors.response.use(
  (response) => response,
  (error) => {
    const config = error.config;
    const message = error.response?.data?.message;
    const isStaleCsrfToken = error.response?.status === 401
      && typeof message === 'string'
      && message.includes('not familiar with the nature of your activities')
      && config && !config._csrfRetried;

    if (isStaleCsrfToken) {
      config._csrfRetried = true;
      if (config.headers) {
        delete config.headers['X-XSRF-TOKEN'];
        delete config.headers['x-xsrf-token'];
        delete config.headers['X-CSRF-TOKEN'];
        delete config.headers['x-csrf-token'];
      }
      return axios(config);
    }

    return Promise.reject(error);
  }
);

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
  if (qSel('.registerNav')) qSel('.registerNav').style.display = 'none'; // navbar mgt

  routePromise = import(
    /* webpackChunkName: 'register' */
    /* webpackPrefetch: true */
    './components/register/'
  )
    .then((module) => module.default)
    .catch((err) => showError(err));
} else if (window.location.pathname === '/allMembers') {
  if (qSel('.allMemberNav')) qSel('.allMemberNav').style.display = 'none';
  if (qSel('.allMembersNav')) qSel('.allMembersNav').style.display = 'none';

  routePromise = import(
    /* webpackChunkName: 'all_members' */
    /* webpackPrefetch: true */
    './components/allMembers/'
  )
    .then((module) => module.default)
    .catch((err) => showError(err));
} else if (window.location.pathname.toLowerCase().startsWith('/reels')) {
  if (qSel('.reelsNav')) qSel('.reelsNav').style.display = 'none';

  routePromise = import(
    /* webpackChunkName: 'reels' */
    /* webpackPrefetch: true */
    './components/reels/reelsPlayer'
  )
    .then((module) => module.default)
    .catch((err) => showError(err));
} else if (
  window.location.pathname === '/login' 
) {
  if (qSel('.loginNav')) qSel('.loginNav').style.display = 'none'; // navbar mgt

  routePromise = import(
    /* webpackChunkName: 'login' */
    /* webpackPrefetch: true */
    './components/acctMgt/login'
  )
    .then((module) => module.default)
    .catch((err) => showError(err));
} else if (
  window.location.pathname === '/lasu'
) {
  if (qSel('.loginNav')) qSel('.loginNav').style.display = 'none'; // navbar mgt

  routePromise = import(
    /* webpackChunkName: 'adminLogin' */
    /* webpackPrefetch: true */
    './components/acctMgt/adminLogin'
  )
    .then((module) => module.default)
    .catch((err) => showError(err));
} else if (window.location.pathname === '/login/forgot') {
  if (qSel('.signup_login')) qSel('.signup_login').style.display = 'none'; // navbar mgt

  routePromise = import(
    /* webpackChunkName: 'forgotPwd' */
    /* webpackPrefetch: true */
    './components/forgotPwd/'
  )
    .then((module) => module.default)
    .catch((err) => showError(err));
} else if (['/login/code', '/verify-email'].includes(window.location.pathname)) {
  if (qSel('.signup_login')) qSel('.signup_login').style.display = 'none'; // navbar mgt

  routePromise = import(
    /* webpackChunkName: 'code' */
    /* webpackPrefetch: true */
    './components/acctMgt/code'
  )
    .then((module) => module.default)
    .catch((err) => showError(err));
} else if (
  window.location.pathname.toLowerCase().startsWith('/member/profilepage') ||
  window.location.pathname.toLowerCase().startsWith('/profilepage') ||
  checkURL('member/ProfilePage') ||
  checkURL('member/profilePage') ||
  checkURL('profilePage')
) {
  if (qSel('.profilePageNav')) qSel('.profilePageNav').style.display = 'none'; // navbar mgt

  routePromise = import(
    /* webpackChunkName: 'profilePage' */
    /* webpackPrefetch: true */
    './components/profilePage/'
  )
    .then((module) => module.default)
    .catch((err) => showError(err));
} else if (window.location.pathname === '/login/changePW' || window.location.pathname === '/changePW') {
  if (qSel('.login')) qSel('.login').style.display = 'none'; // navbar mgt
  if (qSel('.signUp')) qSel('.signUp').style.display = 'none'; // navbar mgt
  // qSel('#loader').style.display ="none" // loader
  routePromise = import(
    /* webpackChunkName: 'changePW' */
    /* webpackPrefetch: true */
    './components/changePW/'
  )
    .then((module) => module.default)
    .catch((err) => showError(err));
} else if (window.location.pathname === '/profilepage/img') {
  // qSel('.login').style.display ="none" // navbar mgt
  routePromise = import(
    /* webpackChunkName: 'img' */
    /* webpackPrefetch: true */
    './components/profilePage/imgViewer'
  )
    .then((module) => module.default)
    .catch((err) => showError(err));
} else if (window.location.pathname === '/createFamilyCode') {
  routePromise = import(
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
  routePromise = import(
    /* webpackChunkName: 'accountSetting' */
    /* webpackPrefetch: true */
    './components/accountSetting'
  )
    .then((module) => module.default)
    .catch((err) => showError(err));
} else if (checkURL('organogram') || window.location.pathname.startsWith('/organogram')) {
  if (qSel('.familyTreeNav')) qSel('.familyTreeNav').style.display = 'none'; // navbar mgt

  routePromise = import(
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


routePromise.finally(() => {
  Alpine.start();
});
