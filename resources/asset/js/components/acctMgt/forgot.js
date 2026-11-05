import { forgotSubmitHandler } from '@modernman00/shared-js-lib';

// Defensive safeguard: ensure #setLoader exists for legacy shared-js-lib showLoader/clearLoader
if (typeof document !== 'undefined' && !document.getElementById('setLoader')) {
  const dummyLoader = document.createElement('div');
  dummyLoader.id = 'setLoader';
  dummyLoader.style.display = 'none';
  dummyLoader.className = 'd-none';
  dummyLoader.setAttribute('aria-hidden', 'true');
  document.body.appendChild(dummyLoader);
}

sessionStorage.setItem('fromForgot', 'true');


forgotSubmitHandler({
  formId: 'forgot',
  route: '/login/forgot',
  redirect: '/login/code',
  theme: 'bulma'
 
});
