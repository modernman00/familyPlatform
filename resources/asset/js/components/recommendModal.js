/**
 * Recommend FamilyPlatform Modal Component
 *
 * Handles async fetching of personalized referral tokens, 1-tap WhatsApp sharing,
 * clipboard copying with animated feedback, and tracking the Walled Sanctuary USP.
 */

document.addEventListener('DOMContentLoaded', () => {
  const modalElem = document.getElementById('recommendFriendsModal');
  if (!modalElem) return;

  const inputShareLink = document.getElementById('inputShareLink');
  const btnShareWhatsApp = document.getElementById('btnShareWhatsApp');
  const btnCopyShareLink = document.getElementById('btnCopyShareLink');
  const copyFeedback = document.getElementById('copySuccessFeedback');
  const badgeCount = document.getElementById('badgeSpawnedCount');

  let isLoaded = false;

  async function loadRecommendationData() {
    if (isLoaded) return;
    try {
      const response = await fetch('/api/recommendation/get-link', {
        credentials: 'same-origin',
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        }
      });

      if (!response.ok) return;

      const resData = await response.json();

      // API wraps payload in `token` key (Utility::msgSuccess format)
      const payload = resData.data ?? resData.token ?? null;
      if (payload && payload.share) {
        const share = payload.share;
        const stats = payload.stats || {};

        if (inputShareLink) {
          inputShareLink.value = share.share_url || '';
        }
        if (btnShareWhatsApp && share.whatsapp_url) {
          btnShareWhatsApp.setAttribute('href', share.whatsapp_url);
        }
        if (badgeCount && stats.total_spawned !== undefined) {
          badgeCount.textContent = String(stats.total_spawned);
        }
        isLoaded = true;
      }
    } catch (e) {
      // Silent fail — link stays as placeholder
    }
  }

  // Load when modal is triggered
  modalElem.addEventListener('show.bs.modal', () => {
    loadRecommendationData();
  });

  // Direct trigger button
  const triggerBtn = document.getElementById('btnOpenRecommendModal');
  if (triggerBtn) {
    triggerBtn.addEventListener('click', () => {
      loadRecommendationData();
    });
  }

  // Copy to Clipboard
  if (btnCopyShareLink && inputShareLink) {
    btnCopyShareLink.addEventListener('click', async () => {
      const textToCopy = inputShareLink.value;
      if (!textToCopy || textToCopy === 'Generating link...') return;

      try {
        if (navigator.clipboard && navigator.clipboard.writeText) {
          await navigator.clipboard.writeText(textToCopy);
        } else {
          inputShareLink.select();
          document.execCommand('copy');
        }

        if (copyFeedback) {
          copyFeedback.classList.remove('d-none');
          setTimeout(() => {
            copyFeedback.classList.add('d-none');
          }, 3500);
        }

        btnCopyShareLink.innerHTML = '<i class="bi bi-check-lg me-1"></i> Copied!';
        setTimeout(() => {
          btnCopyShareLink.innerHTML = '<i class="bi bi-clipboard me-1"></i> Copy';
        }, 2500);

      } catch (err) {
        console.error('Failed to copy text:', err);
      }
    });
  }
});
