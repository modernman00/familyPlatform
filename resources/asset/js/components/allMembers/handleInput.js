// resources/js/allMembers/handleInput.js
import { id, msgException, showNotification } from "@modernman00/shared-js-lib";
import { esc, getCsrfToken } from "../global";
import axios from "axios";
import Swal from "sweetalert2";

/**
 * Render the modern Google Stitch "Invite Relative" card when search returns 0 results.
 * Wraps the entire block in a single container spanning the grid so children do not scatter.
 *
 * @param {HTMLElement} container
 * @param {string} rawQuery
 * @param {Function} [onResetView]
 */
const renderInviteBlock = (container, rawQuery, onResetView) => {
  const famCode = localStorage.getItem("requesterFamCode") || "";
  const yourName = localStorage.getItem("yourName") || "";
  const trimmedQuery = (rawQuery || "").trim();

  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  const mobileRegex = /^\+?[1-9]\d{6,14}$/;

  const isQueryEmail = emailRegex.test(trimmedQuery);
  const isQueryPhone = mobileRegex.test(trimmedQuery);

  const initialName = (!isQueryEmail && !isQueryPhone) ? trimmedQuery : "";
  const initialEmail = (isQueryEmail || isQueryPhone) ? trimmedQuery : "";

  container.innerHTML = `
    <div class="member-invite-container w-100" style="grid-column: 1 / -1;">
      <div class="member-invite-card mx-auto">
        <div class="member-invite-cover">
          <div class="member-invite-icon-wrapper">
            <i class="bi bi-person-plus-fill"></i>
          </div>
        </div>

        <div class="member-invite-body">
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
            <span class="invite-badge">
              <i class="bi bi-search me-1"></i> Member Not Found
            </span>
            ${famCode ? `
            <span class="invite-fam-code" title="Your registered family code">
              <i class="bi bi-shield-check text-success me-1"></i> Family Code: <strong>#${esc(famCode)}</strong>
            </span>` : ''}
          </div>

          <h3 class="invite-title">Invite Relative to Platform</h3>
          <p class="invite-desc">
            We could not find anyone matching <strong class="text-dark">"${esc(trimmedQuery)}"</strong> in your family network. Enter their details below to send a personalised invitation to join your family space on FamilyPlatform.
          </p>

          <form id="newMemberInviteForm" onsubmit="return false;" novalidate>
            <div class="mb-3">
              <label for="newMemberRequestName" class="form-label invite-field-label">
                Relative's Full Name <span class="text-danger">*</span>
              </label>
              <div class="input-group invite-input-group">
                <span class="input-group-text invite-addon">
                  <i class="bi bi-person-fill"></i>
                </span>
                <input type="text"
                       id="newMemberRequestName"
                       name="newMemberRequestName"
                       class="form-control invite-input"
                       placeholder="e.g. Grandma Rose or Uncle David"
                       value="${esc(initialName)}"
                       autocomplete="name"
                       required>
              </div>
            </div>

            <div class="mb-2">
              <label for="newMemberRequestEmail" class="form-label invite-field-label">
                Email Address <span class="text-danger">*</span>
              </label>
              <div class="input-group invite-input-group">
                <span class="input-group-text invite-addon">
                  <i class="bi bi-envelope-at-fill"></i>
                </span>
                <input type="email"
                       id="newMemberRequestEmail"
                       name="newMemberRequestEmail"
                       class="form-control invite-input"
                       placeholder="e.g. rose.family@example.co.uk"
                       value="${esc(initialEmail)}"
                       autocomplete="email"
                       required>
              </div>
              <div class="form-text invite-help-hint">
                <i class="bi bi-shield-lock-fill text-muted me-1"></i> A secure cryptographic invitation link will be sent to their email inbox.
              </div>
            </div>

            <div id="newMemberRequest_help" class="invite-error-msg d-none" role="alert"></div>

            <div class="invite-actions-row">
              <button type="button"
                      class="btn invite-btn-secondary"
                      id="newMemberCancelBtn"
                      title="Clear search and return to directory">
                <i class="bi bi-arrow-left me-1"></i> Clear Search
              </button>

              <button type="button"
                      class="btn invite-btn-primary"
                      id="newMemberRequestBtn"
                      title="Send invitation link">
                <i class="bi bi-send-fill me-1"></i> Send Invitation
              </button>
            </div>

            ${famCode ? `
            <div class="invite-share-footer">
              <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <span class="text-muted small">Prefer instant messaging?</span>
                <button type="button" class="btn btn-link btn-sm text-decoration-none p-0 fw-semibold text-primary" id="btnShareInviteLink">
                  <i class="bi bi-share-fill me-1"></i> Share Invite Link via WhatsApp
                </button>
              </div>
            </div>` : ''}
          </form>
        </div>
      </div>
    </div>
  `;

  const nameInput = id("newMemberRequestName");
  const emailInput = id("newMemberRequestEmail");
  const submitBtn = id("newMemberRequestBtn");
  const cancelBtn = id("newMemberCancelBtn");
  const helpMsg = id("newMemberRequest_help");
  const shareBtn = id("btnShareInviteLink");

  // Focus appropriate field based on pre-fill
  if (nameInput && emailInput) {
    if (initialName && !initialEmail) {
      emailInput.focus();
    } else if (!initialName && initialEmail) {
      nameInput.focus();
    } else if (nameInput) {
      nameInput.focus();
    }
  }

  // Clear Search button handler
  if (cancelBtn) {
    cancelBtn.addEventListener("click", () => {
      const searchInput = id("searchFamily");
      if (searchInput) searchInput.value = "";
      if (typeof onResetView === "function") {
        onResetView();
      }
    });
  }

  // WhatsApp / System Share Link handler
  if (shareBtn && famCode) {
    shareBtn.addEventListener("click", async () => {
      const joinUrl = `${window.location.origin}/register?famCode=${encodeURIComponent(famCode)}`;
      const shareText = `Hi! Join our family space on FamilyPlatform so we can stay connected. Use our family code #${famCode}: ${joinUrl}`;

      if (navigator.share) {
        try {
          await navigator.share({
            title: "Join our Family on FamilyPlatform",
            text: shareText,
            url: joinUrl,
          });
          return;
        } catch (e) {
          // Fall back to clipboard if cancelled or unsupported
        }
      }

      try {
        await navigator.clipboard.writeText(shareText);
        if (window.swalToast) {
          window.swalToast("Family invitation link copied to clipboard!", "success");
        } else if (window.Swal?.fire) {
          window.Swal.fire({
            icon: "success",
            title: "Copied to Clipboard!",
            text: "Family invite link and code copied. You can now paste it into WhatsApp or Messages.",
            timer: 2500,
            showConfirmButton: false,
          });
        } else {
          alert("Invitation link copied to clipboard!");
        }
      } catch (clipErr) {
        prompt("Copy this invite link to share via WhatsApp:", joinUrl);
      }
    });
  }

  // Submit button handler with spinner & SweetAlert
  if (submitBtn) {
    submitBtn.addEventListener("click", async () => {
      const recipientName = nameInput ? nameInput.value.trim() : "";
      const recipientEmail = emailInput ? emailInput.value.trim() : "";

      const showValidationWarning = (msg, inputToFocus) => {
        if (helpMsg) {
          helpMsg.textContent = msg;
          helpMsg.classList.remove("d-none");
        }
        if (inputToFocus) inputToFocus.focus();
      };

      if (recipientName.length < 2) {
        showValidationWarning("Please enter your relative's name (at least 2 characters).", nameInput);
        return;
      }

      if (!recipientEmail) {
        showValidationWarning("Please enter an email address so we can deliver their secure registration link.", emailInput);
        return;
      }

      if (!emailRegex.test(recipientEmail)) {
        showValidationWarning("Please enter a valid email address (e.g. relative@example.co.uk).", emailInput);
        return;
      }

      if (helpMsg) {
        helpMsg.textContent = "";
        helpMsg.classList.add("d-none");
      }

      // Universal Button Spinner Start
      if (window.ScoreTenantSpinner?.start) {
        window.ScoreTenantSpinner.start(submitBtn);
      } else {
        submitBtn.disabled = true;
        submitBtn.setAttribute("aria-busy", "true");
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Sending...';
      }

      try {
        const postObj = {
          mobile: "",
          viewPath: "msg/contactNewMember",
          data: {
            email: recipientEmail,
            mobile: "",
            name: recipientName,
            familyCode: famCode,
            yourName: yourName || "A family member",
          },
          familyCode: famCode,
          subject: `${yourName || 'A family member'} Wants You: Experience the Magic of your Family Network Today!`,
        };

        const response = await axios.post("/register/contactNewMember", postObj, {
          headers: {
            "X-CSRF-TOKEN": getCsrfToken(),
            "X-XSRF-TOKEN": getCsrfToken(),
          },
        });

        const status = response.data?.status;
        const message = response.data?.message;

        if (status === "success") {
          const swalLib = window.Swal || Swal;
          if (swalLib?.fire) {
            await swalLib.fire({
              icon: "success",
              title: "Invitation Dispatched!",
              html: `<div class="text-start small text-muted">
                       <p class="mb-2">A personalised invitation has been sent to <strong>${esc(recipientName)}</strong> at <code>${esc(recipientEmail)}</code>.</p>
                       <p class="mb-0">They will receive a secure cryptographic registration link to join your family space.</p>
                     </div>`,
              confirmButtonColor: "#a31621",
              confirmButtonText: "Return to Directory",
              customClass: {
                confirmButton: "btn btn-primary px-4 py-2"
              }
            });
          } else if (window.swalSuccess) {
            window.swalSuccess(`Invitation dispatched to ${recipientName}!`);
          } else {
            alert(`Invitation dispatched to ${recipientName}!`);
          }

          // Clear search input and restore full directory
          const searchInput = id("searchFamily");
          if (searchInput) searchInput.value = "";
          if (typeof onResetView === "function") {
            onResetView();
          }
        } else {
          const errMsg = message || "Unable to send invitation. Please verify the email address and try again.";
          const swalLib = window.Swal || Swal;
          if (swalLib?.fire) {
            swalLib.fire({
              icon: "error",
              title: "Invitation Not Sent",
              text: errMsg,
              confirmButtonColor: "#a31621"
            });
          } else if (window.swalError) {
            window.swalError(errMsg);
          } else {
            alert(errMsg);
          }
        }
      } catch (error) {
        const errMsg = error.response?.data?.message || error.message || "Failed to deliver invitation. Please try again.";
        const swalLib = window.Swal || Swal;
        if (swalLib?.fire) {
          swalLib.fire({
            icon: "error",
            title: "Delivery Error",
            text: errMsg,
            confirmButtonColor: "#a31621"
          });
        } else if (window.swalError) {
          window.swalError(errMsg);
        } else {
          alert(errMsg);
        }
      } finally {
        // Universal Button Spinner Stop
        if (window.ScoreTenantSpinner?.stop) {
          window.ScoreTenantSpinner.stop(submitBtn);
        } else {
          submitBtn.disabled = false;
          submitBtn.removeAttribute("aria-busy");
          submitBtn.innerHTML = '<i class="bi bi-send-fill me-1"></i> Send Invitation';
        }
      }
    });
  }
};

/**
 * Factory that returns a debounced search handler using both client-side loaded members
 * and backend /allMembers/search endpoint for complete directory discovery.
 *
 * @param {object} options
 * @param {Array<object>} options.familyMembers
 * @param {Array<object>} options.directoryMembers
 * @param {Array<object>} options.allLoadedMembers
 * @param {Function} options.renderMembers
 * @param {HTMLElement} options.container
 * @param {string} options.searchUrl
 * @param {Function} [options.onResetView]
 * @returns {() => void}
 */
export const createSearchHandler = ({
  familyMembers = [],
  directoryMembers = [],
  allLoadedMembers = [],
  renderMembers,
  container,
  searchUrl,
  onResetView,
}) => {
  const searchInput = id("searchFamily");
  let debounceTimer = null;

  const performLocalFilter = (rawQuery) => {
    const q = rawQuery.toLowerCase();
    const cleanDigits = q.replace(/[^0-9]/g, "");

    return (allLoadedMembers || []).filter((m) => {
      const fName = (m.firstName || "").toLowerCase();
      const lName = (m.lastName || "").toLowerCase();
      const fullName = `${fName} ${lName}`.trim();
      const fullNameRev = `${lName} ${fName}`.trim();
      const email = (m.email || "").toLowerCase();
      const mobile = (m.mobile || "").toLowerCase();
      const mobileDigits = mobile.replace(/[^0-9]/g, "");
      const famCode = m.relationType === "family" ? (m.famCode || "").toLowerCase() : "";
      const country = (m.country || "").toLowerCase();

      return (
        fName.includes(q) ||
        lName.includes(q) ||
        fullName.includes(q) ||
        fullNameRev.includes(q) ||
        email.includes(q) ||
        mobile.includes(q) ||
        (cleanDigits.length >= 3 && mobileDigits.includes(cleanDigits)) ||
        famCode.includes(q) ||
        country.includes(q)
      );
    });
  };

  const performSearch = async () => {
    const rawQuery = searchInput.value.trim();

    const pillsContainer = id("memberFilterPills");
    const memberCountBadge = id("memberCount");
    const memberCountDisplay = id("memberCountDisplay");

    const resetPillsToAll = () => {
      if (pillsContainer) {
        pillsContainer.querySelectorAll(".filter-pill-btn").forEach((b) => b.classList.remove("active"));
        const allBtn = pillsContainer.querySelector('[data-filter="all"]');
        if (allBtn) allBtn.classList.add("active");
      }
    };

    // empty query = back to original view
    if (!rawQuery) {
      if (typeof onResetView === "function") {
        onResetView();
      } else {
        resetPillsToAll();
        renderMembers(familyMembers);
      }
      return;
    }

    // 1. Instant client-side matches across all loaded directory & family members
    const localMatches = performLocalFilter(rawQuery);
    if (localMatches.length > 0) {
      resetPillsToAll();
      renderMembers(localMatches);
    }

    // 2. Server-side debounced search for full platform directory coverage
    try {
      const response = await axios.get(searchUrl, {
        params: { q: rawQuery },
      });

      const data = response.data || {};
      const matches = data.message || [];

      if (!matches.length && localMatches.length === 0) {
        if (memberCountBadge) memberCountBadge.textContent = "0";
        if (memberCountDisplay) memberCountDisplay.textContent = "0";
        renderInviteBlock(container, rawQuery, onResetView);
        return;
      }

      if (matches.length > 0) {
        resetPillsToAll();
        renderMembers(matches);
      }
    } catch (error) {
      if (localMatches.length === 0) {
        const swalLib = window.Swal || Swal;
        if (swalLib?.fire) {
          swalLib.fire({
            icon: "warning",
            title: "Directory Search Interrupted",
            text: "Search failed. Please check your internet connection and try again.",
            confirmButtonColor: "#a31621"
          });
        } else if (window.swalError) {
          window.swalError("Search query failed. Please try again.");
        } else {
          showNotification("allMembers", "is-danger", "Search failed. Please try again.");
        }
        msgException(error);
      }
    }
  };

  return () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(performSearch, 150);
  };
};

