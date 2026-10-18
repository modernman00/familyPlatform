// resources/js/allMembers/handleInput.js
import { id, msgException, showNotification, checkBox } from "@modernman00/shared-js-lib";
import { esc, getCsrfToken } from "../global";
import axios from "axios";

/**
 * Render the "invite a new member" block when there is no search match.
 *
 * @param {HTMLElement} container
 * @param {string} rawQuery
 */
const renderInviteBlock = (container, rawQuery) => {
  const famCode = localStorage.getItem("requesterFamCode") || "";
  const yourName = localStorage.getItem("yourName") || "";

  container.innerHTML = `
    <p>No matching name found – do you want us to send them a text/email to register to the platform?</p>
    ${checkBox("newMemberRequest")} <br>

    <input type="hidden" id="newMemberName" value="${esc(rawQuery)}">

    <input type="text" id="newMemberRequestName" class="form-control"
           name="newMemberRequestName"
           placeholder="Enter their name">

    <input type="text" id="newMemberRequestEmail" class="form-control"
           name="newMemberRequestEmail"
           placeholder="Enter their email address or mobile number">

    <p id="loader" class="loader" style="display:none;"></p>
    <small id="newMemberRequest_help" class="form-text text-muted"></small>

    <button class="button is-primary" id="newMemberRequestBtn">Send Request</button>
  `;

  const nameInput = id("newMemberRequestName");
  const emailInput = id("newMemberRequestEmail");
  const submitBtn = id("newMemberRequestBtn");
  const helpMsg = id("newMemberRequest_help");

  // hide fields until checkbox ticked
  nameInput.style.display = "none";
  emailInput.style.display = "none";
  submitBtn.style.display = "none";

  const yesCheckbox = id("newMemberRequestYes");
  if (yesCheckbox) {
    yesCheckbox.addEventListener("click", () => {
      nameInput.style.display = "block";
      emailInput.style.display = "block";
      submitBtn.style.display = "block";
    });
  }

  submitBtn.addEventListener("click", async () => {
    const emailOrMobile = emailInput.value.trim();
    const name = nameInput.value.trim();

    const mobileRegex = /^\+?[1-9]\d{1,14}$/;
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    // validation
    if (!emailRegex.test(emailOrMobile) && !mobileRegex.test(emailOrMobile)) {
      helpMsg.textContent =
        "Please enter a valid email address or mobile number.";
      return;
    }

    if (mobileRegex.test(emailOrMobile) && !emailOrMobile.startsWith("+")) {
      helpMsg.textContent =
        "Please include the country code. E.g. +2348036517179";
      return;
    }

    if (emailRegex.test(emailOrMobile) && name.length < 2) {
      helpMsg.textContent =
        "Please enter a valid name with at least 2 characters.";
      return;
    }

    // current implementation: send email only
    if (emailRegex.test(emailOrMobile)) {
      const postObj = {
        mobile: "",
        viewPath: "msg/contactNewMember",
        data: {
          email: emailOrMobile,
          mobile: "",
          name,
          familyCode: famCode,
          yourName,
        },
        subject: `${yourName} Wants You: Experience the Magic of your Family Network Today!`,
      };

      try {
        const response = await axios.post("/register/contactNewMember", postObj, {
          headers: {
            "X-CSRF-TOKEN": getCsrfToken(),
            "X-XSRF-TOKEN": getCsrfToken()
          }
        });
        showNotification("allMembers", "is-success", response.data.message);
        helpMsg.textContent = "";
      } catch (error) {
        showNotification("allMembers", "is-danger", error.message);
      }
    }
  });
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
      const famCode = (m.famCode || "").toLowerCase();
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
        renderInviteBlock(container, rawQuery);
        return;
      }

      if (matches.length > 0) {
        resetPillsToAll();
        renderMembers(matches);
      }
    } catch (error) {
      if (localMatches.length === 0) {
        showNotification("allMembers", "is-danger", "Search failed. Please try again.");
        msgException(error);
      }
    }
  };

  return () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(performSearch, 150);
  };
};

