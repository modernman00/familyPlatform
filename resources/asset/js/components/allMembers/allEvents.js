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

      // 3-Pillar Taxonomy Presets for Connected Kin
      const kinshipPillars = {
        in_laws: {
          key: 'in_laws',
          icon: '💍',
          title: 'In-Laws',
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
          icon: '🧬',
          title: 'Extended Lineage',
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
          icon: '🤝',
          title: 'Family Friend',
          desc: 'Godparent, trusted associate',
          roles: [
            'Lifelong Family Friend',
            'Godparent / Godchild',
            'Family Elder / Mentor',
            'Neighbour / Associate',
            'Other Family Friend'
          ]
        }
      };

      let selectedCategory = 'in_laws';
      let selectedRole = 'Brother-in-Law';
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
        return Object.values(kinshipPillars).map(p => `
          <div class="kinship-pillar-card ${p.key === selectedCategory ? 'active' : ''}" data-pillar="${p.key}" style="
            border: 2px solid ${p.key === selectedCategory ? '#0284c7' : '#e2e8f0'};
            background: ${p.key === selectedCategory ? '#f0f9ff' : '#ffffff'};
            border-radius: 12px;
            padding: 10px 8px;
            cursor: pointer;
            text-align: center;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            user-select: none;
            box-shadow: ${p.key === selectedCategory ? '0 4px 14px rgba(2, 132, 199, 0.12)' : '0 1px 3px rgba(0,0,0,0.04)'};
          ">
            <div style="font-size: 1.5rem; line-height: 1; margin-bottom: 5px;">${p.icon}</div>
            <div style="font-weight: 700; font-size: 0.86rem; color: #0f172a; margin-bottom: 3px;">${p.title}</div>
            <div style="font-size: 0.71rem; color: #64748b; line-height: 1.3;">${p.desc}</div>
          </div>
        `).join('');
      };

      const renderRoleChips = (categoryKey) => {
        const pillar = kinshipPillars[categoryKey] || kinshipPillars.in_laws;
        return pillar.roles.map((r, idx) => `
          <button type="button" class="kinship-role-chip ${r === selectedRole ? 'active' : ''}" data-role="${r}" style="
            padding: 6px 14px;
            border-radius: 9999px;
            border: 1px solid ${r === selectedRole ? '#0284c7' : '#cbd5e1'};
            background: ${r === selectedRole ? '#0284c7' : '#f8fafc'};
            color: ${r === selectedRole ? '#ffffff' : '#334155'};
            font-size: 0.82rem;
            font-weight: ${r === selectedRole ? '600' : '500'};
            cursor: pointer;
            transition: all 0.15s ease;
            white-space: nowrap;
            min-height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: ${r === selectedRole ? '0 2px 8px rgba(2, 132, 199, 0.22)' : 'none'};
          ">${r}</button>
        `).join('');
      };

      const renderDraftPills = (categoryKey, roleName) => {
        const drafts = getDraftTemplates(categoryKey, roleName, firstName);
        return drafts.map((d, idx) => `
          <button type="button" class="kinship-draft-pill" data-draft-idx="${idx}" style="
            padding: 5px 12px;
            border-radius: 9999px;
            border: 1px solid #bae6fd;
            background: #f0f9ff;
            color: #0369a1;
            font-size: 0.75rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.15s ease;
            white-space: nowrap;
            min-height: 30px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
          ">
            <i class="bi bi-chat-heart text-primary" style="pointer-events: none;"></i> ${d.label}
          </button>
        `).join('');
      };

      const initialDrafts = getDraftTemplates(selectedCategory, selectedRole, firstName);
      const initialDefaultNote = initialDrafts[0]?.text || '';

      const modalHtml = `
        <style>
          .swal2-popup.swal-kinship-modal {
            width: 640px !important;
            max-width: 95vw !important;
            max-height: 90vh !important;
            max-height: 90dvh !important;
            display: flex !important;
            flex-direction: column !important;
            overflow: hidden !important;
            padding: 22px 24px 16px 24px !important;
            border-radius: 20px !important;
          }
          .swal2-popup.swal-kinship-modal .swal2-html-container {
            overflow-y: auto !important;
            overflow-x: hidden !important;
            flex: 1 1 auto !important;
            max-height: calc(90dvh - 110px) !important;
            margin: 0 !important;
            padding: 2px 6px 4px 2px !important;
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 transparent;
          }
          .swal2-popup.swal-kinship-modal .swal2-html-container::-webkit-scrollbar {
            width: 5px;
          }
          .swal2-popup.swal-kinship-modal .swal2-html-container::-webkit-scrollbar-thumb {
            background-color: #cbd5e1;
            border-radius: 9999px;
          }
          .kinship-pillar-card:hover {
            border-color: #38bdf8 !important;
            transform: translateY(-2px);
          }
          .kinship-role-chip:hover:not(.active) {
            background-color: #e2e8f0 !important;
            border-color: #94a3b8 !important;
            color: #0f172a !important;
          }
          .kinship-draft-pill:hover {
            background-color: #e0f2fe !important;
            border-color: #7dd3fc !important;
          }
          .swal2-popup.swal-kinship-modal .swal2-actions {
            display: flex !important;
            justify-content: flex-end !important;
            gap: 12px !important;
            margin: 12px 0 0 0 !important;
            padding: 12px 0 0 0 !important;
            border-top: 1px solid #e2e8f0 !important;
            width: 100% !important;
            flex-shrink: 0 !important;
          }
          .swal2-confirm {
            min-height: 46px !important;
            border-radius: 12px !important;
            font-weight: 600 !important;
            padding: 10px 24px !important;
            font-size: 0.92rem !important;
            box-shadow: 0 4px 14px rgba(2, 132, 199, 0.22) !important;
            white-space: nowrap !important;
            flex-shrink: 0 !important;
          }
          .swal2-cancel {
            min-height: 46px !important;
            border-radius: 12px !important;
            font-weight: 500 !important;
            padding: 10px 18px !important;
            font-size: 0.92rem !important;
            white-space: nowrap !important;
            flex-shrink: 0 !important;
          }
          @media (max-width: 640px) {
            .swal2-popup.swal-kinship-modal {
              padding: 16px 14px 12px 14px !important;
              max-height: 94dvh !important;
              border-radius: 16px !important;
            }
            .swal2-popup.swal-kinship-modal .swal2-html-container {
              max-height: calc(94dvh - 100px) !important;
            }
            .swal2-popup.swal-kinship-modal .swal2-actions {
              margin-top: 8px !important;
              padding-top: 10px !important;
              gap: 8px !important;
            }
            .swal2-confirm, .swal2-cancel {
              flex: 1 !important;
              min-height: 44px !important;
              padding: 10px 12px !important;
              font-size: 0.88rem !important;
            }
            .kinship-pillar-grid {
              gap: 6px !important;
            }
            .kinship-pillar-card {
              padding: 8px 4px !important;
            }
            .kinship-pillar-card div:nth-child(2) {
              font-size: 0.80rem !important;
            }
            .kinship-pillar-card div:nth-child(3) {
              font-size: 0.68rem !important;
            }
          }
        </style>

        <div class="text-start" style="font-family: inherit;">
          <!-- Header Banner -->
          <div class="d-flex align-items-center gap-3 p-3 mb-3 rounded-3" style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border: 1px solid #e2e8f0;">
            <div class="d-flex align-items-center justify-content-center rounded-circle shadow-xs" style="width: 42px; height: 42px; background: #ffffff; border: 2px solid #e0f2fe; color: #0284c7; font-size: 1.2rem; flex-shrink: 0;">
              <i class="bi bi-people-fill"></i>
            </div>
            <div class="flex-grow-1" style="min-width: 0;">
              <div class="d-flex align-items-center gap-2 flex-wrap">
                <h5 class="mb-0 fw-bold text-dark text-truncate" style="font-size: 1.05rem;">Connect with ${targetName}</h5>
                <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary fw-semibold px-2.5 py-1" style="font-size: 0.72rem;">Connected Kin</span>
              </div>
              <div class="text-muted mt-0.5 small" style="font-size: 0.78rem;">
                Sovereign Family Network • Connect as Kin
              </div>
            </div>
          </div>

          <!-- Step 1: Select Kinship Category -->
          <div class="d-flex align-items-center justify-content-between mb-2">
            <div class="d-flex align-items-center gap-2">
              <span class="d-inline-flex align-items-center justify-content-center rounded-circle fw-bold" style="width: 22px; height: 22px; background: #e0f2fe; color: #0284c7; font-size: 0.75rem;">1</span>
              <span class="fw-bold text-dark" style="font-size: 0.88rem;">How are you connected to ${firstName}?</span>
            </div>
            <span class="small text-muted" style="font-size: 0.72rem;">Category</span>
          </div>
          <div id="kinshipPillarContainer" class="kinship-pillar-grid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 16px;">
            ${renderPillarCards()}
          </div>

          <!-- Step 2: Specific Role -->
          <div class="d-flex align-items-center justify-content-between mb-2">
            <div class="d-flex align-items-center gap-2">
              <span class="d-inline-flex align-items-center justify-content-center rounded-circle fw-bold" style="width: 22px; height: 22px; background: #e0f2fe; color: #0284c7; font-size: 0.75rem;">2</span>
              <span class="fw-bold text-dark" style="font-size: 0.88rem;">Specifically, who is ${firstName} to you?</span>
            </div>
            <span class="small text-muted" style="font-size: 0.72rem;">Select role</span>
          </div>
          <div id="kinshipRoleContainer" style="display: flex; flex-wrap: wrap; gap: 7px; margin-bottom: 16px;">
            ${renderRoleChips(selectedCategory)}
          </div>

          <div class="mb-3" id="customRoleWrapper" style="display: none;">
            <input type="text" id="kinshipCustomRole" class="form-control" placeholder="Specify custom relationship (e.g. Second Cousin, Foster Parent)" maxlength="100" style="border-radius: 10px; font-size: 0.86rem; padding: 8px 12px;">
          </div>

          <!-- Step 3: Optional Personal Note with Smart Drafts -->
          <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-1.5">
              <div class="d-flex align-items-center gap-2">
                <span class="d-inline-flex align-items-center justify-content-center rounded-circle fw-bold" style="width: 22px; height: 22px; background: #e0f2fe; color: #0284c7; font-size: 0.75rem;">3</span>
                <span class="fw-bold text-dark" style="font-size: 0.88rem;">Personal Note <span class="text-muted fw-normal" style="font-size: 0.78rem;">(Optional)</span></span>
              </div>
              <button type="button" id="kinshipClearNoteBtn" class="btn btn-link p-0 text-muted small text-decoration-none" style="font-size: 0.74rem;">
                <i class="bi bi-x-circle me-1"></i> Clear Note
              </button>
            </div>

            <!-- Contextual Draft Pills Box -->
            <div class="mb-2 p-2.5 rounded-3" style="background: #f8fafc; border: 1px solid #e2e8f0;">
              <div class="d-flex align-items-center gap-1.5 mb-1.5 text-muted" style="font-size: 0.76rem;">
                <i class="bi bi-stars text-primary"></i> <span class="fw-semibold">Suggested drafts for <strong id="kinshipDraftRoleLabel" class="text-primary">${selectedRole}</strong>:</span>
              </div>
              <div id="kinshipDraftPillsContainer" style="display: flex; flex-wrap: wrap; gap: 6px;">
                ${renderDraftPills(selectedCategory, selectedRole)}
              </div>
            </div>

            <textarea id="kinshipNote" class="form-control" rows="2" maxlength="255" placeholder="Pick a draft above or write your own message..." style="border-radius: 10px; font-size: 0.86rem; line-height: 1.45; padding: 8px 12px; resize: none; border-color: #cbd5e1;">${initialDefaultNote}</textarea>
            <div class="d-flex justify-content-between mt-1 text-muted px-1" style="font-size: 0.72rem;">
              <span>Tap any draft to insert. You can freely edit or personalise it.</span>
              <span id="kinshipCharCount" class="font-monospace">${initialDefaultNote.length}/255</span>
            </div>
          </div>

          <!-- Step 4: Transparency & Sovereign Privacy Safeguards -->
          <div class="p-3 rounded-3" style="background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%); border: 1px solid #a7f3d0; color: #166534;">
            <div class="d-flex align-items-center gap-2 mb-1.5" style="color: #065f46;">
              <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 22px; height: 22px; background: #d1fae5; color: #059669; font-size: 0.85rem; flex-shrink: 0;">
                <i class="bi bi-shield-check"></i>
              </div>
              <span class="fw-bold" style="font-size: 0.84rem;">Connected Kin Privacy &amp; Lineage Safeguards</span>
            </div>
            <div class="d-flex flex-column gap-1 ps-1" style="font-size: 0.76rem; line-height: 1.4;">
              <div>
                <strong class="text-dark">Access Granted:</strong> Mutual profile updates, direct kin messaging, and general shared family moments.
              </div>
              <div>
                <strong class="text-dark">Lineage Boundary:</strong> Both family trees remain <strong>100% private</strong> to your respective family codes. Sensitive lineage records and phone numbers remain strictly hidden.
              </div>
            </div>
          </div>
        </div>
      `;

      const { isConfirmed, value } = await Swal.fire({
        html: modalHtml,
        showCancelButton: true,
        confirmButtonColor: '#0284c7',
        cancelButtonColor: '#64748b',
        confirmButtonText: '<i class="bi bi-send-check-fill me-1.5"></i> Send Connection Request',
        cancelButtonText: 'Cancel',
        width: 640,
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
                p.style.background = '#0284c7';
                p.style.color = '#ffffff';
                p.style.borderColor = '#0284c7';
                p.style.fontWeight = '600';
              } else {
                p.style.background = '#f0f9ff';
                p.style.color = '#0369a1';
                p.style.borderColor = '#bae6fd';
                p.style.fontWeight = '500';
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
                  c.style.background = '#f8fafc';
                  c.style.color = '#334155';
                  c.style.borderColor = '#cbd5e1';
                  c.style.fontWeight = '500';
                });
                chip.classList.add('active');
                chip.style.background = '#0284c7';
                chip.style.color = '#ffffff';
                chip.style.borderColor = '#0284c7';
                chip.style.fontWeight = '600';
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
                c.style.borderColor = '#e2e8f0';
                c.style.background = '#ffffff';
              });
              card.classList.add('active');
              card.style.borderColor = '#0284c7';
              card.style.background = '#f0f9ff';
              selectedCategory = card.getAttribute('data-pillar') || 'in_laws';
              
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
