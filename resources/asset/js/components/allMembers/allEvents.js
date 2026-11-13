import axios from 'axios';
import Swal from 'sweetalert2';
import { id, showError, qSel, msgException, log } from '@shared';
import { deleteNotification, esc, getCsrfToken } from '../global.js';
import { addToNotificationTab, increaseNotificationCount } from '../navbar';
import { friendRequestCard } from '../profilePage/htmlFolder/friendRequestCard';

// Attach a click event listener to the document
/**
 * Attach a click event listener to the document. When a button with the id `addFamily<userId>` is clicked, send a family request to the user identified by the userId and update the button's HTML and disable it.
 it returns the notification details for the approvers tab
 * 
 * @param {MouseEvent} e - The event object.
 */
document.onclick = async (e) => {
  try {
    // Get the target element's ID (defensively checking closest button or element with ID)
    const btn = e.target.closest('button') || e.target.closest('[id]');
    const targetId = btn ? btn.id : (e.target.id || '');
    if (!targetId) return;

    // Check if the ID includes 'addFamily'
    if (targetId.includes('addFamily')) {
      const targetUserId = btn.getAttribute('data-target-id') || targetId.replace('addFamily', '');
      const targetName = btn.getAttribute('data-target-name') || 'this relative';
      const firstName = targetName.split(' ')[0] || targetName;

      // 3-Pillar Taxonomy Presets for Connected Kin (Artboards 18, 19, 20 — Line Icons, Zero Emoji)
      const kinshipPillars = {
        in_laws: {
          key: 'in_laws',
          icon: `<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="12" r="5"/><circle cx="15" cy="12" r="5"/></svg>`,
          title: 'In-laws',
          desc: 'Connected by marriage',
          roles: [
            'Brother-in-Law',
            'Sister-in-Law',
            'Parent-in-Law',
            'Son-in-Law / Daughter-in-Law',
            'Spouse’s Relative',
            'Other In-Law'
          ]
        },
        extended_lineage: {
          key: 'extended_lineage',
          icon: `<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="5" r="2.5"/><circle cx="6" cy="19" r="2.5"/><circle cx="18" cy="19" r="2.5"/><path d="M12 7.5V12m0 0H6v4.5m6-4.5h6v4.5"/></svg>`,
          title: 'Extended lineage',
          desc: 'Maternal or blood branch',
          roles: [
            'Maternal Cousin',
            'Paternal Cousin',
            'Aunt / Uncle',
            'Niece / Nephew',
            'Grandparent / Elder',
            'Other Blood Relative'
          ]
        },
        family_friend: {
          key: 'family_friend',
          icon: `<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>`,
          title: 'Family friend',
          desc: 'Godparent, close to family',
          roles: [
            'Lifelong Family Friend',
            'Godparent / Godchild',
            'Family Elder / Mentor',
            'Neighbour / Associate',
            'Other Family Friend'
          ]
        }
      };

      let selectedCategory = 'extended_lineage';
      let selectedRole = 'Maternal Cousin';
      let hasUserManuallyEditedNote = false;

      // Smart Contextual Draft Templates per Pillar & Role
      const getDraftTemplates = (catKey, role, name) => {
        const cleanName = name || 'there';
        if (catKey === 'in_laws') {
          if (role.includes('Parent')) {
            return [
              { label: 'Respectful', text: `Dear ${cleanName}, sending warm regards and respect. Delighted to connect with you here on FamilyPlatform.` },
              { label: 'Family Circle', text: `Hello ${cleanName}, wishing you good health and joy. Excited to connect our family circles and share updates.` },
              { label: 'Warm Hello', text: `Dear ${cleanName}, hope you are doing wonderfully. Looking forward to keeping in touch.` }
            ];
          }
          if (role.includes('Son-in-Law') || role.includes('Daughter-in-Law')) {
            return [
              { label: 'Warm Welcome', text: `Dear ${cleanName}, so wonderful to connect with you on FamilyPlatform! Sending warm family love.` },
              { label: 'Family Updates', text: `Hello ${cleanName}, excited to connect as family and share milestones and celebrations.` },
              { label: 'Warm Hello', text: `Hi ${cleanName}, hope all is well with you. Delighted to stay in touch on here.` }
            ];
          }
          return [
            { label: 'Warm Hello', text: `Hi ${cleanName}, hope you're doing well! Reaching out to connect our families on the platform and stay in touch.` },
            { label: 'Family News', text: `Hello ${cleanName}, wonderful to see you here! Let's connect as in-laws to share celebrations and family updates.` },
            { label: 'Household Regards', text: `Hi ${cleanName}, sending warmest greetings from our household! Looking forward to staying connected.` }
          ];
        }

        if (catKey === 'extended_lineage') {
          if (role.includes('Cousin')) {
            return [
              { label: 'Catching Up', text: `Hi ${cleanName}, great to find you here! Let's stay in touch and share updates across our family branches.` },
              { label: 'Family Branches', text: `Hello ${cleanName}! Reaching out from our branch of the family. Wonderful to connect with you here.` },
              { label: 'Milestones', text: `Hi ${cleanName}, hope all is well! Let's connect so we don't miss any family news and celebrations.` }
            ];
          }
          if (role.includes('Aunt') || role.includes('Uncle') || role.includes('Elder') || role.includes('Grandparent')) {
            return [
              { label: 'Warm Respect', text: `Dear ${cleanName}, sending you our deepest respect and warmest family love. Delighted to be connected.` },
              { label: 'Family Regards', text: `Hello ${cleanName}, so happy to see you on the platform! Sending much love from our branch of the family.` },
              { label: 'Staying in Touch', text: `Dear ${cleanName}, hope you are doing wonderfully. Looking forward to keeping in touch with you here.` }
            ];
          }
          return [
            { label: 'Reconnecting', text: `Hi ${cleanName}, so great to discover family here! Let's connect our branches and stay in touch.` },
            { label: 'Lineage Bridge', text: `Hello ${cleanName}, reaching out from our side of the family. Delighted to connect on FamilyPlatform!` },
            { label: 'Warm Wishes', text: `Hi ${cleanName}, sending warm wishes from our household. Excited to share family updates with you.` }
          ];
        }

        // family_friend
        if (role.includes('Godparent') || role.includes('Godchild')) {
          return [
            { label: 'Special Bond', text: `Dear ${cleanName}, so wonderful to connect with you here! Thank you for always being such a special part of our lives.` },
            { label: 'Warm Blessings', text: `Hello ${cleanName}, sending warmest family love and blessings. Great to have you in our connected circle!` },
            { label: 'Catch Up', text: `Dear ${cleanName}, hope you are keeping well. Looking forward to sharing our family updates with you.` }
          ];
        }
        return [
          { label: 'Warm Hello', text: `Hi ${cleanName}, so lovely to see you on FamilyPlatform! You've always been like family to our household.` },
          { label: 'Family Moments', text: `Hello ${cleanName}, hope you're doing great! Let's stay connected and share our family moments and news.` },
          { label: 'Household Regards', text: `Hi ${cleanName}, sending warmest greetings from all of us! Delighted to connect with you on here.` }
        ];
      };

      const renderPillarCards = () => {
        return Object.values(kinshipPillars).map(p => {
          const isActive = p.key === selectedCategory;
          return `
          <div class="kinship-pillar-card ${isActive ? 'active' : ''}" data-pillar="${p.key}" style="
            border: 1.5px solid ${isActive ? '#a31621' : '#e6dfd5'};
            background: ${isActive ? '#fdf2f2' : '#ffffff'};
            border-radius: 14px;
            padding: 12px 10px;
            cursor: pointer;
            text-align: left;
            transition: all 0.18s ease;
            user-select: none;
          ">
            <div class="kinship-pillar-icon" style="color: ${isActive ? '#a31621' : '#5c5049'}; margin-bottom: 6px; display: inline-flex;">${p.icon}</div>
            <div style="font-weight: 700; font-size: 0.86rem; color: #1c1412; margin-bottom: 2px;">${p.title}</div>
            <div style="font-size: 0.72rem; color: #786c65; line-height: 1.3;">${p.desc}</div>
          </div>
        `;
        }).join('');
      };

      const renderRoleChips = (categoryKey) => {
        const pillar = kinshipPillars[categoryKey] || kinshipPillars.extended_lineage;
        return pillar.roles.map((r) => {
          const isActive = r === selectedRole;
          return `
          <button type="button" class="kinship-role-chip ${isActive ? 'active' : ''}" data-role="${r}" style="
            padding: 7px 14px;
            border-radius: 9999px;
            border: 1px solid ${isActive ? '#1c1412' : '#e2d9cd'};
            background: ${isActive ? '#1c1412' : '#ffffff'};
            color: ${isActive ? '#ffffff' : '#3d322c'};
            font-size: 0.81rem;
            font-weight: ${isActive ? '700' : '600'};
            cursor: pointer;
            transition: all 0.15s ease;
            white-space: nowrap;
            min-height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
          ">${r}</button>
        `;
        }).join('');
      };

      const renderDraftPills = (categoryKey, roleName) => {
        const drafts = getDraftTemplates(categoryKey, roleName, firstName);
        return drafts.map((d, idx) => `
          <button type="button" class="kinship-draft-pill" data-draft-idx="${idx}" style="
            padding: 6px 13px;
            border-radius: 9999px;
            border: 1.5px solid ${idx === 0 ? '#a31621' : '#e2d9cd'};
            background: ${idx === 0 ? '#fdf2f2' : '#ffffff'};
            color: ${idx === 0 ? '#a31621' : '#3d322c'};
            font-size: 0.76rem;
            font-weight: ${idx === 0 ? '700' : '600'};
            cursor: pointer;
            transition: all 0.15s ease;
            white-space: nowrap;
            min-height: 36px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
          ">
            ${d.label}
          </button>
        `).join('');
      };

      const initialDrafts = getDraftTemplates(selectedCategory, selectedRole, firstName);
      const initialDefaultNote = initialDrafts[0]?.text || '';
      const targetInitials = targetName.split(' ').filter(Boolean).slice(0, 2).map(part => part[0]?.toUpperCase() || '').join('') || 'FK';

      const modalHtml = `
        <style>
          .swal2-popup.swal-kinship-modal {
            width: 780px !important;
            max-width: 95vw !important;
            max-height: 92vh !important;
            max-height: 92dvh !important;
            display: flex !important;
            flex-direction: column !important;
            overflow: hidden !important;
            padding: 22px 24px 16px 24px !important;
            border-radius: 24px !important;
            background: #ffffff !important;
            border: 1px solid #e6dfd5 !important;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif !important;
          }
          .swal2-popup.swal-kinship-modal .swal2-html-container {
            overflow-y: auto !important;
            overflow-x: hidden !important;
            flex: 1 1 auto !important;
            max-height: calc(92dvh - 110px) !important;
            margin: 0 !important;
            padding: 2px 4px 4px 2px !important;
            scrollbar-width: thin;
            scrollbar-color: #d5c8b8 transparent;
          }
          .swal-kinship-body-grid {
            display: grid;
            grid-template-columns: 1.08fr 0.92fr;
            gap: 24px;
            align-items: start;
          }
          @media (max-width: 859.98px) {
            .swal2-popup.swal-kinship-modal {
              width: 560px !important;
            }
            .swal-kinship-body-grid {
              grid-template-columns: 1fr;
              gap: 18px;
            }
          }
          .kinship-step-badge {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: #fdf2f2;
            color: #a31621;
            font-size: 0.74rem;
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
          }
          .kinship-pillar-card:hover {
            border-color: #a31621 !important;
          }
          .kinship-role-chip:hover:not(.active) {
            background-color: #faf6f0 !important;
            border-color: #c5b8a8 !important;
            color: #1c1412 !important;
          }
          .kinship-draft-pill:hover {
            border-color: #a31621 !important;
          }
          .swal2-popup.swal-kinship-modal .swal2-actions {
            display: flex !important;
            justify-content: flex-end !important;
            gap: 10px !important;
            margin: 14px 0 0 0 !important;
            padding: 14px 0 0 0 !important;
            border-top: 1px solid #efe8de !important;
            width: 100% !important;
            flex-shrink: 0 !important;
          }
          .swal2-popup.swal-kinship-modal .swal2-confirm {
            min-height: 46px !important;
            border-radius: 12px !important;
            font-weight: 700 !important;
            padding: 10px 22px !important;
            font-size: 0.9rem !important;
            background: #a31621 !important;
            box-shadow: 0 6px 16px rgba(163, 22, 33, 0.22) !important;
            white-space: nowrap !important;
            flex-shrink: 0 !important;
          }
          .swal2-popup.swal-kinship-modal .swal2-cancel {
            min-height: 46px !important;
            border-radius: 12px !important;
            font-weight: 700 !important;
            padding: 10px 18px !important;
            font-size: 0.9rem !important;
            background: #ffffff !important;
            color: #3d322c !important;
            border: 1px solid #dfd7cc !important;
            white-space: nowrap !important;
            flex-shrink: 0 !important;
          }
          @media (max-width: 640px) {
            .swal2-popup.swal-kinship-modal {
              padding: 16px 14px 12px 14px !important;
              max-height: 94dvh !important;
              border-radius: 20px !important;
            }
            .swal2-popup.swal-kinship-modal .swal2-actions {
              margin-top: 10px !important;
              padding-top: 10px !important;
              gap: 8px !important;
            }
            .swal2-popup.swal-kinship-modal .swal2-confirm,
            .swal2-popup.swal-kinship-modal .swal2-cancel {
              flex: 1 !important;
              min-height: 44px !important;
              padding: 10px 12px !important;
              font-size: 0.86rem !important;
            }
            .kinship-pillar-grid {
              gap: 8px !important;
            }
          }
        </style>

        <div class="text-start" style="font-family: inherit; color: #1c1412;">
          <!-- Header Row -->
          <div class="d-flex align-items-center justify-content-between pb-3 mb-3" style="border-bottom: 1px solid #efe8de;">
            <div class="d-flex align-items-center gap-3 min-w-0">
              <div class="d-flex align-items-center justify-content-center rounded-circle fw-bold flex-shrink-0" style="width: 44px; height: 44px; background: #f4d8cd; color: #8a1019; font-size: 0.95rem;">
                ${targetInitials}
              </div>
              <div class="min-w-0">
                <h5 class="mb-0 fw-bold text-truncate" style="font-family: 'Outfit', sans-serif; font-size: 1.25rem; color: #1c1412; letter-spacing: -0.02em;">Connect with ${targetName}</h5>
                <div style="font-size: 0.82rem; color: #786c65;">
                  Extended family · Connect as kin
                </div>
              </div>
            </div>
          </div>

          <div class="swal-kinship-body-grid">
            <!-- Left Column: Step 1 (Category) + Step 2 (Role) -->
            <div>
              <!-- Step 1: Select Kinship Category -->
              <div class="d-flex align-items-center gap-2 mb-2">
                <span class="kinship-step-badge">1</span>
                <span class="fw-bold" style="font-size: 0.9rem; color: #1c1412;">How are you connected to ${firstName}?</span>
              </div>
              <div id="kinshipPillarContainer" class="kinship-pillar-grid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 18px;">
                ${renderPillarCards()}
              </div>

              <!-- Step 2: Specific Role -->
              <div class="d-flex align-items-center gap-2 mb-2">
                <span class="kinship-step-badge">2</span>
                <span class="fw-bold" style="font-size: 0.9rem; color: #1c1412;">Who is ${firstName} to you?</span>
              </div>
              <div id="kinshipRoleContainer" style="display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 8px;">
                ${renderRoleChips(selectedCategory)}
              </div>

              <div class="mt-2" id="customRoleWrapper" style="display: none;">
                <input type="text" id="kinshipCustomRole" class="form-control" placeholder="Specify relationship (e.g. Second Cousin)" maxlength="100" style="border-radius: 10px; font-size: 0.85rem; padding: 8px 12px; min-height: 44px; border-color: #dfd7cc;">
              </div>
            </div>

            <!-- Right Column: Step 3 (Note with drafts) + Condensed 2-Line Privacy Card -->
            <div>
              <!-- Step 3: Optional Personal Note with Smart Drafts -->
              <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <div class="d-flex align-items-center gap-2">
                    <span class="kinship-step-badge">3</span>
                    <span class="fw-bold" style="font-size: 0.9rem; color: #1c1412;">Add a note <span class="fw-normal" style="font-size: 0.8rem; color: #786c65;">(optional)</span></span>
                  </div>
                  <button type="button" id="kinshipClearNoteBtn" class="btn btn-link p-0 small text-decoration-none fw-semibold" style="font-size: 0.76rem; color: #786c65;">
                    Clear
                  </button>
                </div>

                <!-- Contextual Draft Pills -->
                <div id="kinshipDraftPillsContainer" style="display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 10px;">
                  ${renderDraftPills(selectedCategory, selectedRole)}
                </div>
                <span id="kinshipDraftRoleLabel" class="d-none">${selectedRole}</span>

                <div class="position-relative">
                  <textarea id="kinshipNote" class="form-control" rows="3" maxlength="255" placeholder="Pick a draft above or write your own message..." style="border-radius: 14px; font-size: 0.85rem; line-height: 1.5; padding: 12px 14px 26px; resize: none; border: 1px solid #dfd7cc; background: #ffffff; color: #1c1412;">${initialDefaultNote}</textarea>
                  <span id="kinshipCharCount" class="font-monospace position-absolute" style="bottom: 8px; right: 12px; font-size: 0.72rem; color: #8c7e75;">${initialDefaultNote.length}/255</span>
                </div>
              </div>

              <!-- Condensed 2-Line Privacy Block (Artboards 18, 19, 20) -->
              <div class="p-3 rounded-3 d-flex align-items-start gap-2.5" style="background: #faf6f0; border: 1px solid #e6dfd5; color: #3d322c;">
                <i class="bi bi-shield-check flex-shrink-0 mt-0.5" style="color: #a31621; font-size: 1rem;"></i>
                <div style="font-size: 0.78rem; line-height: 1.48;">
                  <div><strong style="color: #1c1412;">You'll share:</strong> family feed, stories and tree.</div>
                  <div><strong style="color: #1c1412;">Stays private:</strong> phone, email, address and birth year — change anytime in Settings.</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      `;

      const { isConfirmed, value } = await Swal.fire({
        html: modalHtml,
        showCancelButton: true,
        confirmButtonColor: '#a31621',
        cancelButtonColor: '#ffffff',
        confirmButtonText: 'Send connection request',
        cancelButtonText: 'Cancel',
        width: 780,
        customClass: {
          popup: 'rounded-4 shadow-lg border-0 swal-kinship-modal'
        },
        didOpen: () => {
          const popup = Swal.getPopup();
          const pillarCards = popup.querySelectorAll('.kinship-pillar-card');
          const roleContainer = popup.querySelector('#kinshipRoleContainer');
          const customWrapper = popup.querySelector('#customRoleWrapper');
          const customInput = popup.querySelector('#kinshipCustomRole');
          const draftPillsContainer = popup.querySelector('#kinshipDraftPillsContainer');
          const draftRoleLabel = popup.querySelector('#kinshipDraftRoleLabel');
          const noteTextarea = popup.querySelector('#kinshipNote');
          const charCount = popup.querySelector('#kinshipCharCount');
          const clearNoteBtn = popup.querySelector('#kinshipClearNoteBtn');

          const updateCharCount = () => {
            if (charCount && noteTextarea) {
              charCount.textContent = `${noteTextarea.value.length}/255`;
            }
          };

          const highlightActivePill = (activeIdx) => {
            if (!draftPillsContainer) return;
            const pills = draftPillsContainer.querySelectorAll('.kinship-draft-pill');
            pills.forEach((p, idx) => {
              if (idx === activeIdx) {
                p.style.background = '#fdf2f2';
                p.style.color = '#a31621';
                p.style.borderColor = '#a31621';
                p.style.fontWeight = '700';
              } else {
                p.style.background = '#ffffff';
                p.style.color = '#3d322c';
                p.style.borderColor = '#e2d9cd';
                p.style.fontWeight = '600';
              }
            });
          };

          const updateDraftsUI = (preserveIfUserEdited = true) => {
            if (draftRoleLabel) draftRoleLabel.textContent = selectedRole;
            const drafts = getDraftTemplates(selectedCategory, selectedRole, firstName);
            if (draftPillsContainer) {
              draftPillsContainer.innerHTML = renderDraftPills(selectedCategory, selectedRole);
              const pills = draftPillsContainer.querySelectorAll('.kinship-draft-pill');
              pills.forEach((pill, idx) => {
                pill.onclick = (ev) => {
                  ev.preventDefault();
                  const targetDraft = drafts[idx];
                  if (targetDraft && noteTextarea) {
                    noteTextarea.value = targetDraft.text;
                    hasUserManuallyEditedNote = false;
                    updateCharCount();
                    highlightActivePill(idx);

                    // Tactile visual feedback on insertion
                    noteTextarea.style.transition = 'box-shadow 0.25s ease';
                    noteTextarea.style.boxShadow = '0 0 0 3px rgba(2, 132, 199, 0.25)';
                    setTimeout(() => { if (noteTextarea) noteTextarea.style.boxShadow = 'none'; }, 500);
                  }
                };
              });
            }

            // Auto-populate default draft if user hasn't typed their own custom note
            if (!preserveIfUserEdited || !hasUserManuallyEditedNote) {
              if (noteTextarea && drafts[0]) {
                noteTextarea.value = drafts[0].text;
                updateCharCount();
                highlightActivePill(0);
              }
            } else {
              highlightActivePill(-1);
            }
          };

          const updateRoleListeners = () => {
            const roleChips = roleContainer.querySelectorAll('.kinship-role-chip');
            roleChips.forEach(chip => {
              chip.onclick = () => {
                roleChips.forEach(c => {
                  c.classList.remove('active');
                  c.style.background = '#ffffff';
                  c.style.color = '#3d322c';
                  c.style.borderColor = '#e2d9cd';
                  c.style.fontWeight = '600';
                });
                chip.classList.add('active');
                chip.style.background = '#1c1412';
                chip.style.color = '#ffffff';
                chip.style.borderColor = '#1c1412';
                chip.style.fontWeight = '700';
                selectedRole = chip.getAttribute('data-role') || '';

                if (selectedRole.includes('Other')) {
                  customWrapper.style.display = 'block';
                  customInput.focus();
                } else {
                  customWrapper.style.display = 'none';
                }

                updateDraftsUI(true);
              };
            });
          };

          pillarCards.forEach(card => {
            card.onclick = () => {
              pillarCards.forEach(c => {
                c.classList.remove('active');
                c.style.borderColor = '#e6dfd5';
                c.style.background = '#ffffff';
                const iconEl = c.querySelector('.kinship-pillar-icon');
                if (iconEl) iconEl.style.color = '#5c5049';
              });
              card.classList.add('active');
              card.style.borderColor = '#a31621';
              card.style.background = '#fdf2f2';
              const activeIconEl = card.querySelector('.kinship-pillar-icon');
              if (activeIconEl) activeIconEl.style.color = '#a31621';
              selectedCategory = card.getAttribute('data-pillar') || 'extended_lineage';
              
              // Set default role for new category
              const defaultPillar = kinshipPillars[selectedCategory];
              selectedRole = defaultPillar?.roles[0] || 'Connected Kin';

              roleContainer.innerHTML = renderRoleChips(selectedCategory);
              customWrapper.style.display = 'none';
              updateRoleListeners();
              updateDraftsUI(true);
            };
          });

          if (noteTextarea) {
            noteTextarea.oninput = () => {
              hasUserManuallyEditedNote = true;
              updateCharCount();
              highlightActivePill(-1);
            };
          }

          if (clearNoteBtn) {
            clearNoteBtn.onclick = (ev) => {
              ev.preventDefault();
              if (noteTextarea) {
                noteTextarea.value = '';
                hasUserManuallyEditedNote = true;
                updateCharCount();
                highlightActivePill(-1);
                noteTextarea.focus();
              }
            };
          }

          updateRoleListeners();
          updateDraftsUI(false);
        },
        preConfirm: () => {
          const popup = Swal.getPopup();
          const customRoleEl = popup.querySelector('#kinshipCustomRole');
          const noteEl = popup.querySelector('#kinshipNote');

          let finalRole = selectedRole;
          if (selectedRole.includes('Other') && customRoleEl && customRoleEl.value.trim() !== '') {
            finalRole = customRoleEl.value.trim();
          }

          return {
            category: selectedCategory,
            role: finalRole,
            note: noteEl ? noteEl.value.trim() : ''
          };
        }
      });

      if (isConfirmed && value) {
        btn.disabled = true;
        const originalText = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Sending...';

        try {
          // Fetch approver details for the user
          const approverDetails = await fetchApproverData(targetUserId);

          const familyRequestData = {
            approver: approverDetails,
            relationship_category: value.category,
            relationship_role: value.role,
            relationship_note: value.note,
            emailPath: 'msg/request_premium',
          };

          // Send the family request data to the server
          const result = await sendFamilyRequest(familyRequestData);

          if (result.data.status === 'success' && result.data.message === 'Request sent') {
            btn.innerHTML = '<i class="bi bi-check2-circle text-success me-1"></i> Request Sent';
            btn.classList.remove('btn-stitch-connect');
            btn.classList.add('btn-outline-success');
            btn.disabled = true;

            Swal.fire({
              icon: 'success',
              title: 'Request Sent!',
              html: `
                <p class="text-muted small mb-0">
                  Your connection request has been sent to <strong>${targetName}</strong> as <strong>${value.role}</strong>.
                  They will receive a notification capturing this relationship and your respective family trees will remain private.
                </p>
              `,
              confirmButtonColor: '#059669',
              confirmButtonText: 'Great!'
            });
          } else if (result.data.status === 'error' && result.data.message === 'Request already pending') {
            btn.innerHTML = 'Request Pending';
            btn.disabled = true;
            Swal.fire('Request Pending', 'You already have an active request pending with this member.', 'info');
          } else {
            btn.innerHTML = originalText;
            btn.disabled = false;
            Swal.fire('Error', result.data.message || 'Unable to send request. Please try again.', 'error');
          }
        } catch (requestErr) {
          btn.innerHTML = originalText;
          btn.disabled = false;
          showError(requestErr);
          const errText = requestErr.response?.data?.message || requestErr.message || 'Error sending connection request';
          Swal.fire('Error', errText, 'error');
        }
      }
    } else if (targetId.includes('removeProfile')) {
      // Extract the user ID from the target ID
      const userId = targetId.replace('removeProfile', '');
      const url = `/allMembers/removeProfile/${userId}`;

      // Confirm with SweetAlert warning modal
      const result = await Swal.fire({
        title: 'Are you sure?',
        text: 'You will no longer see this profile and associated posts. Are you sure you want to remove this connection?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#a31621',
        confirmButtonText: 'Yes, remove connection!'
      });

      if (result.isConfirmed) {
        const memberCard = qSel(`.member_profile_${userId}`) || id(userId) || (btn ? btn.closest('.member-card') : null);

        try {
          const csrfToken = getCsrfToken();
          const response = await axios.delete(url, {
            headers: {
              'X-CSRF-TOKEN': csrfToken,
              'X-XSRF-TOKEN': csrfToken,
              'X-Requested-With': 'XMLHttpRequest'
            },
            params: {
              token: csrfToken
            },
            data: {
              token: csrfToken
            }
          });

          if (response.data && (response.data.status === 'success' || response.data.message === 'success')) {
            if (memberCard) {
              memberCard.remove();
            }

            // Update member count badge
            const container = id('allMembers');
            if (container) {
              const remaining = container.querySelectorAll('.member-card').length;
              const countStr = remaining.toLocaleString();
              const memberCountBadge = id('memberCount');
              const memberCountDisplay = id('memberCountDisplay');
              if (memberCountBadge) memberCountBadge.textContent = countStr;
              if (memberCountDisplay) memberCountDisplay.textContent = countStr;

              if (remaining === 0) {
                container.innerHTML = `
                  <div class="col-12 text-center py-5 bg-white rounded-4 border w-100" style="grid-column: 1 / -1; border-radius: var(--stitch-radius-lg);">
                      <div class="mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 64px; height: 64px; font-size: 1.8rem; background: var(--stitch-primary-container); color: var(--stitch-primary); border-radius: 50%;">
                          <i class="bi bi-people"></i>
                      </div>
                      <h5 class="fw-bold text-dark mb-1">No Members in View</h5>
                      <p class="text-muted small mb-3" style="max-width: 440px; margin: 0 auto;">
                          There are no matching relatives in this directory category yet. Invite or connect with your family members to build your network.
                      </p>
                      <a href="/familyStudio" class="btn btn-primary btn-sm fw-bold px-4 py-2" style="border-radius: var(--stitch-radius-pill);">
                          <i class="bi bi-plus-circle-fill me-1"></i> Open Family Studio
                      </a>
                  </div>
                `;
              }
            }

            Swal.fire({
              title: 'Removed!',
              text: 'Member connection has been removed successfully.',
              icon: 'success',
              timer: 2000,
              showConfirmButton: false
            });
          } else {
            const errText = response.data?.message || 'Error deleting profile';
            msgException(errText);
            Swal.fire('Error', errText, 'error');
          }
        } catch (err) {
          showError(err);
          const errText = err.response?.data?.message || err.message || 'Error deleting profile';
          Swal.fire('Error', errText, 'error');
        }
      }
    } else if (targetId.includes('seeProfile')) {

      // Extract the user ID from the target ID
      const userId = targetId.replace('seeProfile', '');

      // redirect to 'allMembers/setProfile/'+userId
      window.location.href = `/allMembers/seeProfile/${userId}`;

    } else if (targetId.includes('familyTree')) {

      // Extract the user ID from the target ID
      const userId = targetId.replace('familyTree', '');

      // redirect to 'allMembers/setProfile/'+userId
      window.location.href = `/organogram/${userId}`;
    } else if (targetId.includes('nudgeMigrate')) {
      const targetUserId = btn.getAttribute('data-target-id') || targetId.replace('nudgeMigrate', '');
      const targetName = btn.getAttribute('data-target-name') || 'this relative';
      const safeTargetName = esc(targetName);

      const { value: note, isConfirmed } = await Swal.fire({
        title: `Nudge ${safeTargetName}?`,
        html: `
          <p class="text-muted small mb-3">
            Invite ${safeTargetName} to migrate to your family network so you can share your family tree, events, and memories.
          </p>
          <div class="form-group text-start">
            <label for="swal-nudge-note" class="form-label small fw-semibold text-secondary">Optional Note:</label>
            <textarea id="swal-nudge-note" class="form-control form-control-sm" rows="2" placeholder="e.g. Hi! I saw your profile in the directory. Let's unite our family trees!"></textarea>
          </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#8a1019',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<i class="bi bi-send-check me-1"></i> Send Nudge',
        cancelButtonText: 'Cancel',
        preConfirm: () => {
          const noteEl = document.getElementById('swal-nudge-note');
          return noteEl ? noteEl.value.trim() : '';
        }
      });

      if (isConfirmed) {
        btn.disabled = true;
        const originalText = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Sending...';

        try {
          const csrfToken = getCsrfToken();
          const response = await axios.post('/api/family-migration/nudge', {
            target_user_id: targetUserId,
            note: note || null
          }, {
            headers: {
              'X-CSRF-TOKEN': csrfToken,
              'X-XSRF-TOKEN': csrfToken,
              'Content-Type': 'application/json'
            }
          });

          if (response.data && response.data.success) {
            btn.innerHTML = '<i class="bi bi-check2-circle text-success me-1"></i> Nudge Sent';
            btn.classList.remove('btn-stitch-tonal');
            btn.classList.add('btn-outline-success');
            Swal.fire({
              icon: 'success',
              title: 'Nudge Sent!',
              text: response.data?.email_sent === false
                ? `${targetName} has been invited and will see it in the app, but the email could not be delivered.`
                : `We have sent a migration invitation to ${targetName}. They will receive a notification to review and accept.`,
              confirmButtonColor: '#10b981'
            });
          } else {
            btn.disabled = false;
            btn.innerHTML = originalText;
            Swal.fire({
              icon: 'warning',
              title: 'Notice',
              text: response.data?.message || response.data?.error || 'Unable to send nudge.',
              confirmButtonColor: '#f59e0b'
            });
          }
        } catch (err) {
          btn.disabled = false;
          btn.innerHTML = originalText;
          const errMsg = err.response?.data?.error || err.response?.data?.message || 'A network error occurred. Please try again.';
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: errMsg,
            confirmButtonColor: '#ef4444'
          });
        }
      }
    } else if (targetId.includes('requestMigrate')) {
      const targetUserId = btn.getAttribute('data-target-id') || targetId.replace('requestMigrate', '');
      const targetName = btn.getAttribute('data-target-name') || 'this family';
      const targetFirstName = btn.getAttribute('data-target-first-name') || targetName.split(' ')[0] || 'this';
      const safeTargetName = esc(targetName);
      const safeFirstName = esc(targetFirstName);

      const { value: note, isConfirmed } = await Swal.fire({
        title: `Join ${safeFirstName} famcode?`,
        html: `
          <p class="text-muted small mb-3">
            Request to migrate your account into <strong>${safeTargetName}</strong>'s family network.
            <br><br>
            <span class="badge bg-primary text-white px-2 py-1"><i class="bi bi-info-circle me-1"></i> Subject to Family Approval</span>
            <br>
            <small class="text-muted mt-2 d-block">Existing members of ${safeFirstName}'s family will be notified to approve your transfer.</small>
          </p>
          <div class="form-group text-start">
            <label for="swal-req-note" class="form-label small fw-semibold text-secondary">Optional Note / Kinship explanation:</label>
            <textarea id="swal-req-note" class="form-control form-control-sm" rows="2" placeholder="e.g. Hello, I am relative to ${safeTargetName}, registering to connect with our family!"></textarea>
          </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#a31621',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<i class="bi bi-box-arrow-in-right me-1"></i> Submit Request',
        cancelButtonText: 'Cancel',
        preConfirm: () => {
          const noteEl = document.getElementById('swal-req-note');
          return noteEl ? noteEl.value.trim() : '';
        }
      });

      if (isConfirmed) {
        btn.disabled = true;
        const originalText = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Submitting...';

        try {
          const csrfToken = getCsrfToken();
          const response = await axios.post('/api/family-migration/request', {
            target_user_id: targetUserId,
            note: note || null
          }, {
            headers: {
              'X-CSRF-TOKEN': csrfToken,
              'X-XSRF-TOKEN': csrfToken,
              'Content-Type': 'application/json'
            }
          });

          if (response.data && response.data.success) {
            btn.innerHTML = '<i class="bi bi-hourglass-split text-primary me-1"></i> Request Pending';
            btn.classList.remove('btn-stitch-tonal');
            btn.classList.add('btn-outline-primary');
            Swal.fire({
              icon: 'success',
              title: 'Request Submitted!',
              text: response.data?.email_sent === false
                ? `Your request to join ${targetFirstName}'s family was submitted and members will see it in the app, but the email could not be delivered.`
                : `Your migration request to join ${targetFirstName}'s family has been sent! You will be notified as soon as a family member approves.`,
              confirmButtonColor: '#a31621'
            });
          } else {
            btn.disabled = false;
            btn.innerHTML = originalText;
            Swal.fire({
              icon: 'warning',
              title: 'Notice',
              text: response.data?.message || response.data?.error || 'Unable to submit request.',
              confirmButtonColor: '#f59e0b'
            });
          }
        } catch (err) {
          btn.disabled = false;
          btn.innerHTML = originalText;
          const errMsg = err.response?.data?.error || err.response?.data?.message || 'A network error occurred. Please try again.';
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: errMsg,
            confirmButtonColor: '#ef4444'
          });
        }
      }
    }
    // else if (targetId.includes('deleteNotification')) {
    //   // Call the deleteNotification function to remove the notification
    //   deleteNotification(targetId);
    // }
    // Extract the user ID from the target ID
  } catch (error) {
    // Handle any errors that occur during execution
    showError(error);
  }
};

// Function to fetch approver data based on user ID
async function fetchApproverData(userId) {
  try {
    const result = await axios.get(
      `/members/familyRequestMgt/getApprover?id=${userId}`,
    );
    const approverDetails = {
      approverFirstName: result.data.message.firstName,
      approverLastName: result.data.message.lastName,
      approverEmail: result.data.message.email,
      approverId: result.data.message.id,
      approverCode: result.data.message.famCode,
    };
    return approverDetails;
  } catch (error) {
    console.error(error);
    throw error;
  }
}


// Function to send family request data to the server
function sendFamilyRequest(data) {
  try {
    const csrfToken = getCsrfToken();
    return axios.post('/members/familyRequestMgt', {
      ...data,
      token: csrfToken
    }, {
      headers: {
        'X-CSRF-TOKEN': csrfToken,
        'X-XSRF-TOKEN': csrfToken,
        'Content-Type': 'application/json'
      }
    });
  } catch (error) {
    showError(error);
  }
}

// Function to update the button's HTML and disable it
function updateButton(targetId, newHTML) {
  const theTargetId = id(targetId);
  theTargetId.innerHTML = newHTML;
  theTargetId.disabled = true;
}

// ADD THE NEW EVENT TO THE NOTIFICATION TAB
