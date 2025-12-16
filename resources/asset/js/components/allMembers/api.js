// resources/js/allMembers/api.js
import { renderHtml } from "./html";
import { createSearchHandler } from "./handleInput";
import { getApiData, id, showError, log} from "@modernman00/shared-js-lib";

const URL = process.env.MIX_APP_URL2;

const allMembersContainer = id("allMembers"); // main container TO SHOW THE MEMBERS
const memberCountBadge = id("memberCount"); // member count badge
const searchInput = id("searchFamily"); // search input

const NO_MEMBER_HTML =
  "There is no one in your network. It is either you didn't include the right family code or you didn't include your other family members during your registration.";

/**
 * Render a list of members into the main container.
 * Also updates the member count badge.
 *
 * @param {Array<object>} members
 */
export const renderMembers = (members = []) => {
  allMembersContainer.innerHTML = "";

  // if no members, show no member html
  if (!members.length) {
    allMembersContainer.innerHTML = NO_MEMBER_HTML;
    memberCountBadge.textContent = "0 Members";
    return;
  }

  // render each member
  members.forEach(renderHtml);

  // update member count badge
  memberCountBadge.textContent =
    members.length === 1
      ? "1 Member"
      : `${members.length.toLocaleString()} Members`;
};

(async function bootstrapAllMembers() {
  try {
    const url = `${URL}allMembers/processApiData`; // network (family + approved)

    const famCodeData = await getApiData(url);

    const familyMembers = famCodeData?.message ?? [];

    // Pre-render: show only the user's network
    renderMembers(familyMembers);

    // remove loader
    const loader = id("setLoader");
    if (loader) loader.classList.remove("loader");

    // Wire up debounced search handler
    if (searchInput) {
      const handleSearch = createSearchHandler({
        familyMembers,
        renderMembers,
        container: allMembersContainer,
        searchUrl: `${URL}allMembers/search`
      });

      searchInput.addEventListener("input", handleSearch);
    }
  } catch (error) {
    showError(error);
  }
})();
