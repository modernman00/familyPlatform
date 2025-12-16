// resources/js/allMembers/handleInput.js
import { id, msgException, showNotification } from "../global";
import { checkBox } from "@shared";
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

    <input type="hidden" id="newMemberName" value="${rawQuery}">

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
        const response = await axios.post("/register/contactNewMember", postObj);
        showNotification("allMembers", "is-success", response.data.message);
        helpMsg.textContent = "";
      } catch (error) {
        showNotification("allMembers", "is-danger", error.message);
      }
    }
  });
};

/**
 * Factory that returns a debounced search handler using the backend /allMembers/search endpoint.
 *
 * @param {object} options
 * @param {Array<object>} options.familyMembers
 * @param {Array<object>} options.directoryMembers  // currently unused but handy if you want to fall back
 * @param {Function} options.renderMembers
 * @param {HTMLElement} options.container
 * @param {string} options.searchUrl  // e.g. `${URL}allMembers/search`
 * @returns {(e: InputEvent) => void}
 */
export const createSearchHandler = ({
  familyMembers,
  renderMembers,
  container,
  searchUrl,
}) => {
  const searchInput = id("searchFamily");
  let debounceTimer = null;

  const performSearch = async () => {
    const rawQuery = searchInput.value.trim();  // THE SEARCH QUERY


    // empty query = back to my network
    if (!rawQuery) {
      renderMembers(familyMembers);
      return;
    }

    try {
      const response = await axios.get(searchUrl, {
        params: { q: rawQuery },
      });

      const data = response.data || {};
      const matches = data.message || [];

      if (!matches.length) {
        renderInviteBlock(container, rawQuery);
        return;
      }

      // backend already orders: family first, then approved, etc.
      container.innerHTML = "";
      renderMembers(matches);
    } catch (error) {
      showNotification("allMembers", "is-danger", "Search failed. Please try again.");
      // optional: log or surface more detail in dev builds
      msgException(error);
    }
  };

  return () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(performSearch, 200);
  };
};
