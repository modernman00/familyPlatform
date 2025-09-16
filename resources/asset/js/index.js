'use strict';
import { showError , log} from '@modernman00/shared-js-lib';



document.addEventListener('DOMContentLoaded', () => {
  const routeMap = {
    '/register': {
      module: () => import(/* webpackChunkName: 'register' */ './components/register/'),
      hide: ['.signUp'], // Hide signup navbar
    },
    '/allMembers': {
      module: () => import(/* webpackChunkName: 'all_members' */ './components/allMembers/'),
      hide: ['.allMemberNav'], // Hide allMembers navbar
    },
    '/login': {
      
      module: () => import(/* webpackChunkName: 'login' */ './components/acctMgt/login.js'),
      hide: ['.login'], // Hide login navbar
    },
    '/lasu': {
      module: () => import(/* webpackChunkName: 'lasu' */ './components/acctMgt/admin.js'),
      hide: ['.login'], // Same login module as /login
    },
    '/forgot': {
      module: () => import(/* webpackChunkName: 'forgotPwd' */ './components/acctMgt/forgot.js'),
      hide: ['.signup_login'], // Hide login/signup navbar
    },
    '/code': {
      module: () => import(/* webpackChunkName: 'code' */ './components/acctMgt/code.js'),
      hide: ['.signup_login'], // Hide login/signup navbar
    },
    '/profilePage': {
      module: () => import(/* webpackChunkName: 'profilePage' */ './components/profilePage/'),
      hide: ['.profilePageNav'], // Hide profile page navbar
    },
    '/changePW': {
      module: () => import(/* webpackChunkName: 'changePW' */ './components/acctMgt/changePW.js'),
      hide: ['.login', '.signUp'], // Hide login and signup navbars
    },
    '/profilepage/img': {
      module: () => import(/* webpackChunkName: 'img' */ './components/profilePage/imgViewer'),
    },
    '/createFamilyCode': {
      module: () => import(/* webpackChunkName: 'familyCode' */ './components/register/familyCode'),
    },
    '/register/nextStep': {
      hide: ['.login', '.signUp'], // No module, just hide navbars
    },
    '/organogram': {
      module: () => import(/* webpackChunkName: 'organogram' */ './components/familyTree/index.js'),
      hide: ['.familyTreeNav'], // Hide family tree navbar
    },
    '/allMembers/getProfile': {
      // Module import commented out — placeholder for future logic
      hide: ['.familyTreeNav', '.notification_count'], // Hide navbars
    },
  };

  // Support for dynamic route matching
  if (checkURL('accountSetting')) {
    routeMap['/accountSetting'] = {
      module: () => import(/* webpackChunkName: 'accountSetting' */ './components/accountSetting'),
    };
  }

  try {
    const path = window.location.pathname;
    const route = routeMap[path];

    if (!route) {
      throw new Error(`Unhandled route: ${path}`);
    }

    // Hide specified nav elements
    if (route.hide) {
      route.hide.forEach((selector) => {
        const el = document.querySelector(selector);
        if (el) el.style.display = 'none';
      });
    }

    // Load module if defined
    if (route.module) {
      route.module()
        .then((mod) => mod.default)
        .catch((err) => {
          showError(err);
          throw new Error(`Failed to load module for ${path}: ${err.message}`);
        });
    }
  } catch (error) {
    showError(error);
    throw error;
  }
});

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