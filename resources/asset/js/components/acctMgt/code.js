'use strict';
import { createCodeSubmitHandler, qSelAll, id } from '@modernman00/shared-js-lib';

// Determine redirect target based on session flag set by the forgot-password flow.
const fromForgot = sessionStorage.getItem('fromForgot');
const redirectTo = fromForgot ? '/changePW' : '/profilePage';
if (fromForgot) sessionStorage.removeItem('fromForgot');

// The shared library binds the click handler on #button and submits form#code,
// posting the hidden #codeValue field that the OTP boxes below keep in sync.
createCodeSubmitHandler({
  formId: 'code',
  route: '/login/code',
  buttonId: 'button',
  redirect: redirectTo,
  theme: 'bootstrap',
  lengthLimitArray: {
    id: ['codeValue'],
    max: [6],
  },
  recaptchaAction: 'LOGIN_CODE',
});

// Normalise Eastern-Arabic / Persian digits to ASCII and strip non-alphanumerics.
const normalizeDigits = (str) => {
  if (!str) return '';
  return str
    .replace(/[٠-٩]/g, (c) => String.fromCharCode(c.charCodeAt(0) - 0x0660 + 48))
    .replace(/[۰-۹]/g, (c) => String.fromCharCode(c.charCodeAt(0) - 0x06F0 + 48))
    .replace(/[^a-zA-Z0-9]/g, '')
    .toUpperCase();
};

const initOtp = () => {
  const otpForm = id('code');
  const otpInputs = qSelAll('.otp-input');
  const hiddenCodeInput = id('codeValue');
  const pasteBtn = id('pasteBtn');
  const resendBtn = id('resendBtn');

  // Blade may not be parsed yet on first tick — retry briefly.
  if (!otpInputs.length || !hiddenCodeInput) {
    setTimeout(initOtp, 100);
    return;
  }

  const updateHiddenInput = () => {
    const raw = Array.from(otpInputs).map((i) => i.value).join('');
    hiddenCodeInput.value = normalizeDigits(raw);
  };

  const fillFromString = (value) => {
    const code = normalizeDigits(value).substring(0, otpInputs.length);
    if (!code.length) return;
    code.split('').forEach((char, i) => {
      if (otpInputs[i]) otpInputs[i].value = char;
    });
    updateHiddenInput();
    otpInputs[Math.min(code.length - 1, otpInputs.length - 1)].focus();
  };

  otpInputs.forEach((input, index) => {
    input.addEventListener('input', (e) => {
      const clean = normalizeDigits(e.target.value);
      e.target.value = clean ? clean.slice(-1) : '';
      if (clean && index < otpInputs.length - 1) otpInputs[index + 1].focus();
      updateHiddenInput();
    });

    input.addEventListener('keydown', (e) => {
      if (e.key === 'Backspace' && !e.target.value && index > 0) {
        otpInputs[index - 1].focus();
      }
    });

    input.addEventListener('paste', (e) => {
      e.preventDefault();
      const pasted = (e.clipboardData || window.clipboardData).getData('text');
      fillFromString(pasted);
    });
  });

  // All submission goes through the shared library's click handler on #button.
  // Never let the form submit natively (e.g. Enter key) as a bare GET — route
  // it back through the button once the 6 digits are in.
  if (otpForm) {
    otpForm.addEventListener('submit', (e) => {
      e.preventDefault();
      updateHiddenInput();
      if (hiddenCodeInput.value.length === 6) id('button')?.click();
    });
  }

  if (pasteBtn) {
    pasteBtn.addEventListener('click', async () => {
      try {
        const text = await navigator.clipboard.readText();
        fillFromString(text);
      } catch (err) {
        if (window.Swal) {
          window.Swal.fire({
            icon: 'info',
            title: 'Clipboard unavailable',
            text: 'Your browser blocked clipboard access. Please type the code manually.',
            confirmButtonColor: '#7b03fc',
          });
        }
      }
    });
  }

  if (resendBtn) {
    resendBtn.addEventListener('click', async (e) => {
      e.preventDefault();
      const original = resendBtn.innerHTML;
      resendBtn.style.pointerEvents = 'none';
      resendBtn.textContent = 'Sending…';

      try {
        const response = await fetch('/login/code/resend', {
          method: 'POST',
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-XSRF-TOKEN': id('token')?.value || '',
          },
        });
        const result = await response.json().catch(() => ({}));

        if (response.ok) {
          window.Swal?.fire({
            icon: 'success',
            title: 'Code sent',
            text: result?.message || 'A new verification code has been sent to your email.',
            confirmButtonColor: '#7b03fc',
          });
        } else {
          window.Swal?.fire({
            icon: 'error',
            title: 'Could not resend',
            text: result?.message || 'Failed to resend the code. Please try again.',
            confirmButtonColor: '#7b03fc',
          });
        }
      } catch (err) {
        window.Swal?.fire({
          icon: 'error',
          title: 'Network error',
          text: 'A connection error occurred. Please check your network and try again.',
          confirmButtonColor: '#7b03fc',
        });
      } finally {
        resendBtn.innerHTML = original;
        resendBtn.style.pointerEvents = 'auto';
      }
    });
  }

  otpInputs[0].focus();
  id('button')?.setAttribute('data-ready', 'true');
};

initOtp();
