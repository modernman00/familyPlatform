// resources/js/allMembers/api.js
import { renderHtml } from "./html";
import { createSearchHandler } from "./handleInput";
import { getApiData, id, showError } from "@modernman00/shared-js-lib";

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
export function applyFilter(filterVal = "all") {
  const cards = allMembersContainer.querySelectorAll(".member-card");
  let visibleCount = 0;

  cards.forEach((card) => {
    const cat = card.getAttribute("data-category");
    if (filterVal === "all") {
      // "all" shows all connected members in network (family + connected kin)
      if (cat === "family" || cat === "connected") {
        card.style.display = "";
        visibleCount++;
      } else {
        card.style.display = "none";
      }
    } else if (cat === filterVal) {
      card.style.display = "";
      visibleCount++;
    } else {
      card.style.display = "none";
    }
  });

  const emptyPlaceholder = allMembersContainer.querySelector(".empty-filter-placeholder");
  if (visibleCount === 0 && cards.length > 0) {
    if (!emptyPlaceholder) {
      const message = filterVal === "explore"
        ? "No additional members in the public directory."
        : "No members in this category.";
      allMembersContainer.insertAdjacentHTML(
        "beforeend",
        `<div class="empty-filter-placeholder col-12 text-center py-4 text-muted small" style="grid-column: 1 / -1;">${message}</div>`
      );
    }
  } else if (emptyPlaceholder) {
    emptyPlaceholder.remove();
  }

  return visibleCount;
}

function setupFilterPills() {
  const pillsContainer = id("memberFilterPills");
  if (!pillsContainer) return;

  pillsContainer.addEventListener("click", (e) => {
    const btn = e.target.closest(".filter-pill-btn");
    if (!btn) return;

    pillsContainer.querySelectorAll(".filter-pill-btn").forEach((b) => b.classList.remove("active"));
    btn.classList.add("active");

    const filterVal = btn.getAttribute("data-filter") || "all";
    const visibleCount = applyFilter(filterVal);

    if (memberCountBadge) {
      memberCountBadge.textContent = visibleCount.toLocaleString();
    }
  });
}

(async function bootstrapAllMembers() {
  try {
    const url1 = `/allMembers/processApiData`;
    const url2 = `/allMembers/allData`;

    // Fetch caller's network (family/connected) and wider directory in parallel
    const [famCodeData, allUsersData] = await Promise.all([
      getApiData(url1).catch(() => ({ message: [] })),
      getApiData(url2).catch(() => ({ message: [] }))
    ]);

    let familyMembers = [];
    if (Array.isArray(famCodeData)) {
      familyMembers = famCodeData;
    } else if (Array.isArray(famCodeData?.message)) {
      familyMembers = famCodeData.message;
    } else if (Array.isArray(famCodeData?.data)) {
      familyMembers = famCodeData.data;
    }

    let directoryMembers = [];
    if (Array.isArray(allUsersData)) {
      directoryMembers = allUsersData;
    } else if (Array.isArray(allUsersData?.message)) {
      directoryMembers = allUsersData.message;
    } else if (Array.isArray(allUsersData?.data)) {
      directoryMembers = allUsersData.data;
    }

    // Tag directory members with relationType 'other' unless already set
    const normalizedDirMembers = directoryMembers.map((m) => ({
      ...m,
      relationType: m.relationType || "other"
    }));

    // Deduplicate any overlap by ID (prioritizing family/connected)
    const seenIds = new Set();
    const allLoadedMembers = [];

    familyMembers.forEach((m) => {
      if (m && m.id && !seenIds.has(m.id)) {
        seenIds.add(m.id);
        allLoadedMembers.push(m);
      }
    });

    normalizedDirMembers.forEach((m) => {
      if (m && m.id && !seenIds.has(m.id)) {
        seenIds.add(m.id);
        allLoadedMembers.push(m);
      }
    });

    // Pre-render all members (family + connected + directory) into DOM
    renderMembers(allLoadedMembers);

    // Initial view: show connected network by default
    applyFilter("all");
    if (memberCountBadge) {
      memberCountBadge.textContent = familyMembers.length.toLocaleString();
    }

    // Remove loading spinner
    const loader = id("setLoader");
    if (loader) {
      loader.classList.remove("loader");
      loader.classList.add("d-none");
      loader.style.display = "none";
    }

    // Setup filter pills
    setupFilterPills();

    // Wire up search handler across both client-side loaded members & server endpoint
    if (searchInput) {
      const resetViewToCurrentFilter = () => {
        renderMembers(allLoadedMembers);
        const activeBtn = id("memberFilterPills")?.querySelector(".filter-pill-btn.active");
        const activeFilter = activeBtn?.getAttribute("data-filter") || "all";
        applyFilter(activeFilter);
        if (memberCountBadge) {
          const count = activeFilter === "all" ? familyMembers.length : (activeFilter === "explore" ? normalizedDirMembers.length : allLoadedMembers.length);
          memberCountBadge.textContent = count.toLocaleString();
        }
      };

      const handleSearch = createSearchHandler({
        familyMembers,
        directoryMembers: normalizedDirMembers,
        allLoadedMembers,
        renderMembers,
        container: allMembersContainer,
        searchUrl: `/allMembers/search`,
        onResetView: resetViewToCurrentFilter
      });

      searchInput.addEventListener("input", handleSearch);
      searchInput.addEventListener("search", handleSearch);
    }
  } catch (error) {
    showError(error);
  }
})();

