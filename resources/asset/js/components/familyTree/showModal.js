import { id } from "@modernman00/shared-js-lib";
import { esc, getCsrfToken } from '../global';

const personModal = id('personModal');
const modalBody = id('modalBody');
const closeModal = id('closeModal');

/**
 * Open the Person Details Modal for a selected person
 * @param {Object} personData
 */
export const showPersonDetails = async (personData) => {
  const { 
    fullName = 'Family Member', 
    email = '', 
    mobile = '',
    img = '/resources/images/profile/avatarM.png', 
    relation = 'Relative', 
    maritalStatus = '', 
    spouseName = '', 
    occupation = '', 
    country = '', 
    personId = null, 
    nodeId = null,
    isRegistered = false, 
    familyCode = '', 
    isDeceased = false, 
    bio = '' 
  } = personData || {};

  const targetModal = id('personModal');
  const targetModalBody = id('modalBody');

  if (!targetModal || !targetModalBody) return;

  let cleanName = (fullName || 'Family Member').trim().replace(/\s+/g, ' ');

  // Robust effectiveNodeId resolution (defensive fallback from graph nodes)
  const effectiveNodeId = nodeId || 
    (typeof window.getGraphNodeId === 'function' ? window.getGraphNodeId(personId, cleanName) : null) || 
    (window.graphData?.nodes?.find(n => (personId && String(n.user_id) === String(personId)) || (cleanName && (n.full_name === cleanName || n.first_name === cleanName)))?.id) ||
    null;

  // Clean duplicate repeated words in full name (e.g. "AJIBIKE OLAOGUN OLAOGUN" -> "Ajibike Olaogun")
  const isSelf = (personId && String(personId) === String(window.__ROOT_USER_ID__)) || 
                 (effectiveNodeId && typeof window.getGraphNodeId === 'function' && String(effectiveNodeId) === String(window.getGraphNodeId(window.__ROOT_USER_ID__, '')));

  const parts = cleanName.split(' ');
  if (parts.length >= 2) {
    const deduped = [];
    for (let i = 0; i < parts.length; i++) {
      if (i > 0 && parts[i].toLowerCase() === parts[i - 1].toLowerCase()) {
        continue;
      }
      deduped.push(parts[i]);
    }
    cleanName = deduped.map(w => {
      if (w === w.toUpperCase() && w.length > 1) {
        return w.charAt(0).toUpperCase() + w.slice(1).toLowerCase();
      }
      return w;
    }).join(' ');
  }

  const effectiveFamilyCode = (personData?.raw?.family_code || familyCode || window.__FAMILY_CODE__ || '').trim();
  const roleLower = String(personData?.title || personData?.ego_relation || '').toLowerCase();
  const egoSide = String(personData?.ego_side || (personData?.raw && personData?.raw?.ego_side) || '').toLowerCase();

  const isMaternalNode = (egoSide === 'maternal') ||
                         roleLower.includes('maternal') ||
                         (window.__ACTIVE_LINEAGE_SCOPE__ === 'maternal') ||
                         Boolean(window.__HAS_MATERNAL_CODE__ && window.__MATERNAL_FAM_CODE__ && String(effectiveFamilyCode).toUpperCase() === String(window.__MATERNAL_FAM_CODE__).toUpperCase());

  const paternalSurname = (window.__PATERNAL_SURNAME__ || window.__MARITAL_SURNAME__ || 'our');
  let familySurname = paternalSurname;
  let familyTreeLabel = `${familySurname} Family`;
  let displayCodeBadge = effectiveFamilyCode ? `(#${effectiveFamilyCode})` : '';

  if (isMaternalNode) {
    const rawLast = (personData?.last_name || (personData?.raw && personData?.raw?.last_name) || '').trim();
    const maternalSurname = (rawLast && rawLast.toUpperCase() !== paternalSurname.toUpperCase())
      ? rawLast
      : (window.__MOTHER_MAIDEN_SURNAME__ || window.__MAIDEN_SURNAME__ || 'Maternal');
    familySurname = maternalSurname;
    const motherBirthCode = (window.__MOTHER_BIRTH_FAMILY_CODE__ || (window.__HAS_MATERNAL_CODE__ ? window.__MATERNAL_FAM_CODE__ : '')).toUpperCase();
    if (motherBirthCode) {
      familyTreeLabel = `${maternalSurname} Birth Family`;
      displayCodeBadge = `(#${motherBirthCode})`;
    } else {
      familyTreeLabel = `${maternalSurname} Family (Maternal Lineage)`;
      displayCodeBadge = `(Maternal Lineage)`;
    }
  } else if (window.__HAS_BIRTH_FAMILY__ && window.__BIRTH_FAMILY_CODE__ && (egoSide === 'paternal' || roleLower.includes('father') || roleLower.includes('sibling'))) {
    familySurname = window.__MAIDEN_SURNAME__ || 'Birth';
    familyTreeLabel = `${familySurname} Birth Family`;
    displayCodeBadge = `(#${window.__BIRTH_FAMILY_CODE__})`;
  } else if (egoSide === 'paternal' || roleLower.includes('father') || roleLower.includes('paternal')) {
    familyTreeLabel = `${familySurname} Birth / Paternal Family`;
  }

  const inviteLink = `${window.location.origin}/register?famCode=${encodeURIComponent(effectiveFamilyCode)}&name=${encodeURIComponent(cleanName)}`;

  const codeInMsg = displayCodeBadge ? ` ${displayCodeBadge}` : '';
  const inviteMessage = encodeURIComponent(
    `🌳 *Family Tree Invitation* 🌳\n\n` +
    `You are invited to connect with the *${familyTreeLabel}* on FamilyPlatform${codeInMsg}.\n\n` +
    `Click below to claim your spot, explore our lineage, and connect with the family:\n` +
    `👉 ${inviteLink}`
  );

  const isReadOnly = Boolean(personData?.isReadOnly) || Boolean(window.__IS_READ_ONLY__) || (typeof window.graphData !== 'undefined' && Boolean(window.graphData?.isReadOnly));

  const birthYr = personData?.birth_year || (personData?.birth_date ? String(personData.birth_date).substring(0, 4) : (personData?.raw?.birth_year ? String(personData.raw.birth_year) : null));
  const deathYr = personData?.death_year || (personData?.death_date ? String(personData.death_date).substring(0, 4) : (personData?.raw?.death_year ? String(personData.raw.death_year) : null));
  let lifespanStr = '';
  if (isDeceased) {
    if (birthYr && deathYr) lifespanStr = `${birthYr} – ${deathYr}`;
    else if (deathYr) lifespanStr = `Passed in ${deathYr}`;
    else if (birthYr) lifespanStr = `Born in ${birthYr} (Deceased)`;
    else lifespanStr = `Deceased`;
  }

  const claimSpotHtml = (!isRegistered && !isReadOnly && !isDeceased) ? `
    <div class="claim-spot-section mt-4" style="background: var(--gold-light); padding: 18px; border-radius: 18px; text-align: center; border: 1px dashed var(--gold-accent);">
      <h4 style="color: var(--primary-color); font-size: 1rem; font-weight: 700; margin-bottom: 6px;">Invite Relative to Claim This Spot</h4>
      <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 14px;">Send a personal invitation so they can join the <strong>${esc(familyTreeLabel)} ${esc(displayCodeBadge)}</strong> and share family memories.</p>
      <div style="display: flex; gap: 10px; justify-content: center; flex-wrap: wrap;">
        <a id="treeClaimWhatsappBtn" href="https://api.whatsapp.com/send?text=${inviteMessage}" onclick="if(window.trackTreeAnalytics) window.trackTreeAnalytics('tree_invite_claim', null, { method: 'whatsapp' })" target="_blank" rel="noopener noreferrer" class="btn" style="background: #25D366; color: white; border-radius: 20px; padding: 8px 18px; font-size: 0.86rem; text-decoration: none; font-weight: 600;">
          <i class="bi bi-whatsapp"></i> WhatsApp
        </a>
        <a id="treeClaimSmsBtn" href="sms:?body=${inviteMessage}" onclick="if(window.trackTreeAnalytics) window.trackTreeAnalytics('tree_invite_claim', null, { method: 'sms' })" class="btn" style="background: var(--primary-color); color: white; border-radius: 20px; padding: 8px 18px; font-size: 0.86rem; text-decoration: none; font-weight: 600;">
          <i class="bi bi-chat-text"></i> SMS
        </a>
        <button type="button" id="treeClaimCopyBtn" data-invite-url="${inviteLink}" onclick="if(window.copyInviteToClipboard) window.copyInviteToClipboard(this.getAttribute('data-invite-url') || '${inviteLink}', this)" class="btn btn-secondary" style="border-radius: 20px; padding: 8px 18px; font-size: 0.86rem; font-weight: 600;">
          <i class="bi bi-clipboard-check"></i> Copy Link
        </button>
      </div>
    </div>
  ` : '';

  const memorialBannerHtml = isDeceased ? `
    <div class="memorial-dossier-banner mt-3 p-3 rounded-4 text-center" style="background: rgba(30, 41, 59, 0.7); border: 1.5px solid #64748b; color: #f1f5f9;">
      <div class="d-flex align-items-center justify-content-center gap-2 mb-1">
        <span style="font-size: 1.1rem;">🕊️</span>
        <span class="fw-bold" style="letter-spacing: 0.5px; font-size: 0.88rem;">IN LOVING MEMORY</span>
      </div>
      <p class="mb-0 text-white-50" style="font-size: 0.8rem;">This relative is lovingly remembered and honoured in your family tree heritage.</p>
    </div>
  ` : '';

  // Inject content into modal body — every field below is user-controlled
  // family-member data, so escape it before it touches innerHTML (SEC-2).
  targetModalBody.innerHTML = `
    <div class="person-detail">
      <img src="${esc(img)}" alt="${esc(fullName)}" class="person-image" style="${isDeceased ? 'filter: grayscale(100%); border: 3px solid #94a3b8;' : ''}">
      <div class="person-info">
        <h2 class="person-name">${esc(fullName)}</h2>
        <div class="person-relation">${esc(relation || 'Relative')}</div>
        ${isDeceased ? '<span class="badge" style="font-size: 0.75rem; background: #334155; color: #f8fafc; border: 1px solid #64748b; padding: 4px 10px; border-radius: 999px;"><i class="bi bi-flower1 text-warning me-1"></i> In Loving Memory (Deceased)</span>' : ''}
      </div>
    </div>

    ${memorialBannerHtml}

    <!-- Kinship Pathfinder Shortcut -->
    <div class="kinship-action-section mt-3 mb-3">
      <button type="button" class="btn btn-sm btn-outline-primary w-100 fw-bold d-flex align-items-center justify-content-center gap-2" id="btnTraceKinship" onclick="if(window.traceKinshipToSelected) window.traceKinshipToSelected('${esc(String(nodeId || personId || ''))}', '${esc(cleanName)}')">
        <i class="bi bi-diagram-2-fill text-primary"></i> Trace Relationship to Me
      </button>
      <div id="kinshipPathTrail" class="kinship-path-trail mt-2 d-none"></div>
    </div>

    <div class="detail-grid">
      <div class="detail-item">
        <div class="detail-label">Relationship</div>
        <div class="detail-value">${esc(relation || 'Family Member')}</div>
      </div>

      ${lifespanStr ? `
        <div class="detail-item">
          <div class="detail-label">Lifespan / Status</div>
          <div class="detail-value" style="color: #cbd5e1; font-weight: 700;">🕊️ ${esc(lifespanStr)}</div>
        </div>
      ` : ''}

      <div class="detail-item">
        <div class="detail-label">Marital Status</div>
        <div class="detail-value">${esc(maritalStatus || 'N/A')}</div>
      </div>

      <div class="detail-item">
        <div class="detail-label">Occupation</div>
        <div class="detail-value">${esc(occupation || 'Not specified')}</div>
      </div>

      <div class="detail-item">
        <div class="detail-label">Location / Origin</div>
        <div class="detail-value">${esc(country || 'N/A')}</div>
      </div>

      ${spouseName ? `
        <div class="detail-item" style="grid-column: span 2;">
          <div class="detail-label">Spouse / Partner</div>
          <div class="detail-value" style="display: flex; align-items: center; justify-content: space-between;">
            <span>${esc(spouseName)}</span>
            <span class="badge" style="background: var(--gold-light); color: var(--primary-dark); font-size: 0.75rem;"><i class="bi bi-heart-fill text-danger"></i> Partner</span>
          </div>
        </div>
      ` : ''}

      ${email ? `
        <div class="detail-item" style="grid-column: span 2;">
          <div class="detail-label">Email Address</div>
          <div class="detail-value">
            <a href="mailto:${esc(email)}" style="color: var(--primary-color); text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
              <i class="bi bi-envelope-fill text-warning"></i> ${esc(email)}
            </a>
          </div>
        </div>
      ` : ''}

      ${mobile ? `
        <div class="detail-item" style="grid-column: span 2;">
          <div class="detail-label">Mobile / Telephone</div>
          <div class="detail-value">
            <a href="tel:${esc(mobile)}" style="color: var(--primary-color); text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
              <i class="bi bi-telephone-fill text-success"></i> ${esc(mobile)}
            </a>
          </div>
        </div>
      ` : ''}
    </div>

    ${bio ? `
      <div class="detail-item mt-3" style="width: 100%;">
        <div class="detail-label">Notes & Biography</div>
        <div class="detail-value" style="font-size: 0.9rem; font-weight: 500;">${esc(bio)}</div>
      </div>
    ` : ''}

    <div id="extraUnionsSection"></div>

    ${claimSpotHtml}

    <div class="modal-actions mt-4" style="display: flex; gap: 10px; justify-content: flex-end; flex-wrap: wrap;">
      ${(!isSelf && effectiveNodeId && !isReadOnly) ? `
        <button type="button" class="btn" id="modalRemoveNodeBtn" onclick="if(window.confirmRemovePerson) { window.confirmRemovePerson('${esc(String(effectiveNodeId))}', '${esc(cleanName)}'); } else if(window.removePersonFallback) { window.removePersonFallback('${esc(String(effectiveNodeId))}', '${esc(cleanName)}'); }" style="background: rgba(239, 68, 68, 0.12); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 14px; padding: 8px 16px; font-weight: 600;">
          <i class="bi bi-trash3-fill text-danger me-1"></i> Remove from Tree
        </button>
      ` : ''}
      <button type="button" class="btn" onclick="if(window.openHeritageStoryModal) window.openHeritageStoryModal('${esc(cleanName)}', '${esc(relation)}', '${esc(img)}', ${isDeceased ? 'true' : 'false'})" style="background: linear-gradient(135deg, var(--gold-accent), #b45309); color: white; border-radius: 14px; padding: 8px 16px; font-weight: 600; border: none;">
        <i class="bi bi-stars"></i> Story Card
      </button>
      ${personId ? `
        <a href="/allMembers/seeProfile/${encodeURIComponent(personId)}" class="btn" style="background: var(--primary-color); color: white; border-radius: 14px; padding: 8px 20px; font-weight: 600; text-decoration: none;">
          <i class="bi bi-person-fill"></i> View Profile
        </a>
      ` : ''}
      <button type="button" class="btn" id="modalCloseActionBtn" style="background: #e2e8f0; color: #334155; border-radius: 14px; padding: 8px 18px; font-weight: 600;">
        Close
      </button>
    </div>

  `;

  // Attach close handlers
  const closeActionBtn = id('modalCloseActionBtn');
  closeActionBtn?.addEventListener('click', () => {
    targetModal.style.display = 'none';
  });

  // Display modal
  targetModal.style.display = 'flex';

  // Asynchronously upgrade invite links to opaque zero-PII tokens if available
  if (effectiveFamilyCode && !isReadOnly && !isRegistered) {
    const csrfToken = getCsrfToken();

    fetch('/api/invite/generate', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-XSRF-TOKEN': csrfToken,
        'X-CSRF-TOKEN': csrfToken
      },
      body: JSON.stringify({
        family_code: effectiveFamilyCode,
        lineage_scope: isMaternalNode ? 'maternal' : 'primary',
        family_surname: familySurname,
        first_name: cleanName.split(' ')[0] || '',
        last_name: cleanName.split(' ').slice(1).join(' ') || '',
        email: email || '',
        node_id: nodeId || null,
        type: 'organogram',
        token: csrfToken
      })
    })
    .then(r => r.json())
    .then(data => {
      if (data && data.status === 'success' && data.invite_url) {
        const freshUrl = data.invite_url;
        const freshMsg = encodeURIComponent(
          `🌳 *Family Tree Invitation* 🌳\n\n` +
          `You are invited to connect with the *${familyTreeLabel}* on FamilyPlatform${codeInMsg}.\n\n` +
          `Click below to claim your spot, explore our lineage, and connect with the family:\n` +
          `👉 ${freshUrl}`
        );
        const wBtn = id('treeClaimWhatsappBtn');
        const sBtn = id('treeClaimSmsBtn');
        const cBtn = id('treeClaimCopyBtn');
        if (wBtn) wBtn.href = `https://api.whatsapp.com/send?text=${freshMsg}`;
        if (sBtn) sBtn.href = `sms:?body=${freshMsg}`;
        if (cBtn) cBtn.setAttribute('data-invite-url', freshUrl);
      }
    })
    .catch((err) => {
      console.warn('[showModal] Invite token fetch fallback:', err);
    });
  }
};

// Global modal close handlers
const initModalClosers = () => {
  const modal = id('personModal');
  const closeBtn = id('closeModal');

  closeBtn?.addEventListener('click', () => {
    if (modal) modal.style.display = 'none';
  });

  window.addEventListener('click', (e) => {
    if (e.target === modal) {
      modal.style.display = 'none';
    }
  });
};

initModalClosers();
window.showPersonDetails = showPersonDetails;