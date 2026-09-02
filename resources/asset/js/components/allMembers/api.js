// resources/js/allMembers/api.js
import { renderHtml } from "./html";
import { createSearchHandler } from "./handleInput";
import { getApiData, id, showError } from "@modernman00/shared-js-lib";

const URL = process.env.MIX_APP_URL2;

const allMembersContainer = id("allMembers");
const memberCountBadge = id("memberCount");
const memberCountDisplay = id("memberCountDisplay");
const searchInput = id("searchFamily");

const NO_MEMBER_HTML = `
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

/**
 * Render a list of members into the main container.
 * Also updates the member count badge.
 *
 * @param {Array<object>} members
 */
export const renderMembers = (members = []) => {
  allMembersContainer.innerHTML = "";

  if (!members.length) {
    allMembersContainer.innerHTML = NO_MEMBER_HTML;
    if (memberCountBadge) memberCountBadge.textContent = "0";
    if (memberCountDisplay) memberCountDisplay.textContent = "0";
    return;
  }

  // Render each member card
  members.forEach(renderHtml);

  // Update member count badges
  const countStr = members.length.toLocaleString();
  if (memberCountBadge) memberCountBadge.textContent = countStr;
  if (memberCountDisplay) memberCountDisplay.textContent = countStr;
};

// Client-side category pill filtering
function setupFilterPills() {
  const pillsContainer = id("memberFilterPills");
  if (!pillsContainer) return;

  pillsContainer.addEventListener("click", (e) => {
    const btn = e.target.closest(".filter-pill-btn");
    if (!btn) return;

    pillsContainer.querySelectorAll(".filter-pill-btn").forEach((b) => b.classList.remove("active"));
    btn.classList.add("active");

    const filterVal = btn.getAttribute("data-filter");
    const cards = allMembersContainer.querySelectorAll(".member-card");
    let visibleCount = 0;

    cards.forEach((card) => {
      const cat = card.getAttribute("data-category");
      if (filterVal === "all" || cat === filterVal) {
        card.style.display = "";
        visibleCount++;
      } else {
        card.style.display = "none";
      }
    });

    if (visibleCount === 0 && cards.length > 0) {
      // Show empty state placeholder if none match category filter
      let emptyPlaceholder = allMembersContainer.querySelector(".empty-filter-placeholder");
      if (!emptyPlaceholder) {
        allMembersContainer.insertAdjacentHTML(
          "beforeend",
          `<div class="empty-filter-placeholder col-12 text-center py-4 text-muted small" style="grid-column: 1 / -1;">No members in this category.</div>`
        );
      }
    } else {
      const emptyPlaceholder = allMembersContainer.querySelector(".empty-filter-placeholder");
      if (emptyPlaceholder) emptyPlaceholder.remove();
    }
  });
}

(async function bootstrapAllMembers() {
  try {
    const url = `${URL}allMembers/processApiData`;

    const famCodeData = await getApiData(url);

    let familyMembers = [];
    if (Array.isArray(famCodeData)) {
      familyMembers = famCodeData;
    } else if (Array.isArray(famCodeData?.message)) {
      familyMembers = famCodeData.message;
    } else if (Array.isArray(famCodeData?.data)) {
      familyMembers = famCodeData.data;
    }

    // Pre-render network members
    renderMembers(familyMembers);

    // Remove loading spinner
    const loader = id("setLoader");
    if (loader) {
      loader.classList.remove("loader");
      loader.classList.add("d-none");
      loader.style.display = "none";
    }

    // Setup filter pills
    setupFilterPills();

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
