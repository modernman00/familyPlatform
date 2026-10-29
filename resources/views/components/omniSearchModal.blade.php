<!-- 🔍 Universal Omni-Search & Command Navigation Palette (Google Stitch Standard) -->
<div class="modal fade omni-search-modal" id="omniSearchModal" tabindex="-1" aria-labelledby="omniSearchModalLabel" aria-hidden="true" data-bs-backdrop="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content omni-search-content shadow-2xl border-0 overflow-hidden">
            <!-- Search Input Header -->
            <div class="omni-search-header p-3 border-bottom d-flex align-items-center gap-3">
                <div class="omni-search-icon-wrap text-muted d-flex align-items-center justify-content-center">
                    <i class="bi bi-search fs-5 text-primary"></i>
                </div>
                <div class="flex-grow-1 position-relative">
                    <input type="search"
                           id="omniSearchInput"
                           class="form-control form-control-lg border-0 shadow-none omni-search-input ps-0"
                           placeholder="Search data, activities, or functions (e.g. 'family journal', 'create family code')..."
                           aria-label="Search data, activities, or functions"
                           autocomplete="off"
                           spellcheck="false">
                </div>
                <div class="d-flex align-items-center gap-2">
                    <div class="spinner-border spinner-border-sm text-primary omni-search-spinner" id="omniSearchSpinner" role="status" style="display: none;">
                        <span class="visually-hidden">Searching...</span>
                    </div>
                    <button type="button" class="btn btn-sm btn-light rounded-pill px-2.5 py-1 text-muted border omni-esc-badge" data-bs-dismiss="modal" aria-label="Close search" title="Close search modal (Esc)">
                        <span class="small fw-semibold">ESC</span>
                    </button>
                </div>
            </div>

            <!-- Category Filter Tabs -->
            <div class="omni-filter-bar px-3 py-2 border-bottom d-flex align-items-center gap-2 overflow-x-auto">
                <button type="button" class="btn btn-sm omni-filter-chip active" data-filter="all" title="Show results across all categories">
                    <span>All</span>
                </button>
                <button type="button" class="btn btn-sm omni-filter-chip" data-filter="actions" title="Filter by app functions and shortcuts">
                    <i class="bi bi-lightning-charge-fill me-1 text-warning"></i><span>Functions</span>
                </button>
                <button type="button" class="btn btn-sm omni-filter-chip" data-filter="journals" title="Filter by family journal entries and memoirs">
                    <i class="bi bi-journal-text me-1 text-danger"></i><span>Journals</span>
                </button>
                <button type="button" class="btn btn-sm omni-filter-chip" data-filter="members" title="Filter by family members and relatives">
                    <i class="bi bi-person-fill me-1 text-info"></i><span>Members</span>
                </button>
                <button type="button" class="btn btn-sm omni-filter-chip" data-filter="posts" title="Filter by family feed posts and stories">
                    <i class="bi bi-chat-heart-fill me-1 text-primary"></i><span>Posts</span>
                </button>
                <button type="button" class="btn btn-sm omni-filter-chip" data-filter="events" title="Filter by upcoming family events and calendar">
                    <i class="bi bi-calendar-event me-1 text-success"></i><span>Events</span>
                </button>
                <button type="button" class="btn btn-sm omni-filter-chip" data-filter="reels" title="Filter by family video reels">
                    <i class="bi bi-camera-reels-fill me-1 text-purple"></i><span>Reels</span>
                </button>
            </div>

            <!-- Search Results Body -->
            <div class="modal-body p-0 omni-search-body" id="omniSearchResultsContainer" tabindex="0">
                <!-- Initial Suggestions & Recent Actions -->
                <div id="omniInitialState" class="p-3">
                    <!-- Recent Searches Container -->
                    <div id="omniRecentSearchesContainer" class="mb-3 px-2" style="display: none;">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-uppercase text-muted fw-bold small" style="font-size: 0.72rem; letter-spacing: 0.05em;">
                                <i class="bi bi-clock-history me-1 text-primary"></i> Recent Searches
                            </span>
                            <button type="button" class="btn btn-link btn-sm text-muted p-0 text-decoration-none" id="omniClearRecentBtn" style="font-size: 0.75rem;">Clear</button>
                        </div>
                        <div class="d-flex flex-wrap gap-1.5" id="omniRecentSearchesChips"></div>
                    </div>

                    <div class="text-uppercase text-muted fw-bold small mb-2 px-2" style="font-size: 0.72rem; letter-spacing: 0.05em;">
                        <i class="bi bi-stars me-1 text-warning"></i> Recommended Quick Actions
                    </div>
                    <div class="list-group list-group-flush omni-results-list" id="omniQuickActionsList">
                        <!-- Populated by JavaScript -->
                    </div>
                </div>

                <!-- Dynamic Search Results -->
                <div id="omniDynamicResults" class="p-3" style="display: none;">
                    <!-- Sections inserted dynamically -->
                </div>

                <!-- Empty State with Viral Lineage Invite Trigger -->
                <div id="omniEmptyState" class="text-center py-4 px-3" style="display: none;">
                    <div class="omni-empty-icon mb-2">
                        <i class="bi bi-binoculars text-muted" style="font-size: 2.5rem; opacity: 0.4;"></i>
                    </div>
                    <h6 class="fw-bold mb-1 text-dark">No matching data or functions found</h6>
                    <p class="text-muted small mb-3" id="omniEmptyMessage">
                        We could not find anything matching "<span id="omniEmptyQuery" class="fw-semibold text-dark"></span>".
                    </p>

                    <!-- Viral Lineage Invite Trigger Card -->
                    <div class="omni-invite-card p-3 rounded-3 mb-3 border text-start mx-auto" id="omniInviteCard" style="max-width: 520px; background: linear-gradient(135deg, rgba(184, 32, 44, 0.04) 0%, rgba(245, 158, 11, 0.06) 100%); border-color: rgba(184, 32, 44, 0.18) !important;">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-2.5">
                                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background: rgba(184, 32, 44, 0.1); color: #b8202c; flex-shrink: 0;">
                                    <i class="bi bi-person-plus-fill fs-5"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark small">Missing a family relative?</div>
                                    <div class="text-muted" style="font-size: 0.78rem;">Invite <span class="fw-semibold text-primary" id="omniInviteTargetName">relative</span> to join your living family tree.</div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 py-1.5 fw-bold text-nowrap ms-auto" id="omniInviteActionBtn">
                                <i class="bi bi-send-fill me-1 text-warning"></i> Invite Now
                            </button>
                        </div>
                    </div>

                    <div class="d-flex justify-content-center gap-2 flex-wrap">
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill omni-suggest-btn" data-query="family journal">
                            <i class="bi bi-journal me-1"></i> Family Journal
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill omni-suggest-btn" data-query="create family code">
                            <i class="bi bi-key me-1"></i> Create Family Code
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill omni-suggest-btn" data-query="add relative">
                            <i class="bi bi-person-plus me-1"></i> Add Relative
                        </button>
                    </div>
                </div>
            </div>

            <!-- Keyboard Navigation Helper Footer -->
            <div class="omni-search-footer px-3 py-2 border-top bg-light d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-3 text-muted small" style="font-size: 0.76rem;">
                    <span><kbd class="omni-kbd">↑</kbd> <kbd class="omni-kbd">↓</kbd> to navigate</span>
                    <span><kbd class="omni-kbd">↵</kbd> to select</span>
                    <span><kbd class="omni-kbd">esc</kbd> to dismiss</span>
                </div>
                <div class="small text-muted" style="font-size: 0.74rem;">
                    <span class="text-primary fw-semibold"><i class="bi bi-shield-check me-1"></i>FamilyPlatform</span> Omni-Search
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* 🔍 Omni-Search Styling conforming to Global Design Standards v2.1 */
.omni-search-modal .modal-dialog {
    max-width: 680px;
    margin: 1.75rem auto;
}
.omni-search-content {
    border-radius: 18px;
    background: #ffffff;
    box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.25);
    border: 1px solid rgba(226, 232, 240, 0.8);
}
.omni-search-header {
    background: #ffffff;
}
.omni-search-input {
    font-size: 1.05rem;
    font-weight: 500;
    color: #0f172a;
    background: transparent !important;
}
.omni-search-input::placeholder {
    color: #94a3b8;
    font-size: 0.95rem;
}
.omni-search-icon-wrap {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: #fbefef;
}
.omni-filter-bar {
    background: #f8fafc;
    scrollbar-width: none;
    -ms-overflow-style: none;
}
.omni-filter-bar::-webkit-scrollbar {
    display: none;
}
.omni-filter-chip {
    border-radius: 9999px;
    font-size: 0.78rem;
    font-weight: 600;
    padding: 0.25rem 0.75rem;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    color: #64748b;
    white-space: nowrap;
    transition: all 0.15s ease;
}
.omni-filter-chip:hover {
    background: #f1f5f9;
    color: #0f172a;
}
.omni-filter-chip.active {
    background: #b8202c;
    color: #ffffff;
    border-color: #b8202c;
}
.omni-filter-chip.active i {
    color: #ffffff !important;
}
.omni-search-body {
    max-height: 480px;
    min-height: 260px;
    overflow-y: auto;
}
.omni-result-item {
    border-radius: 12px;
    margin-bottom: 4px;
    padding: 10px 14px;
    border: 1px solid transparent;
    cursor: pointer;
    text-decoration: none !important;
    display: flex;
    align-items: center;
    gap: 12px;
    transition: background-color 0.15s ease, border-color 0.15s ease, transform 0.1s ease;
    min-height: 48px; /* Safe touch targets */
}
.omni-result-item:hover,
.omni-result-item.is-selected {
    background-color: #f8fafc;
    border-color: #e2e8f0;
    transform: translateX(2px);
}
.omni-result-item.is-selected {
    background-color: #fbefef !important;
    border-color: #fca5a5 !important;
}
.omni-item-icon-box {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 1.1rem;
}
.omni-item-title {
    font-size: 0.92rem;
    font-weight: 600;
    color: #0f172a;
    line-height: 1.25;
}
.omni-item-desc {
    font-size: 0.8rem;
    color: #64748b;
    margin-top: 2px;
    line-height: 1.3;
}
.omni-highlight {
    background: rgba(254, 240, 138, 0.7);
    color: #92400e;
    font-weight: 700;
    border-radius: 3px;
    padding: 0 2px;
}
.omni-recent-chip {
    border-radius: 9999px;
    font-size: 0.76rem;
    padding: 0.22rem 0.75rem;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    color: #475569;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    transition: all 0.15s ease;
}
.omni-recent-chip:hover {
    background: #e2e8f0;
    color: #0f172a;
}
.omni-kbd {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 4px;
    padding: 1px 5px;
    font-family: inherit;
    font-size: 0.7rem;
    font-weight: 600;
    color: #475569;
    box-shadow: 0 1px 1px rgba(0,0,0,0.06);
}
.omni-category-title {
    font-size: 0.72rem;
    letter-spacing: 0.05em;
    color: #94a3b8;
    text-transform: uppercase;
    font-weight: 700;
    margin-top: 12px;
    margin-bottom: 6px;
    padding-left: 6px;
}
.pulse-highlight-target {
    animation: omniPulseGlow 2.5s ease-out;
    outline: 3px solid #b8202c !important;
    outline-offset: 4px;
}
@keyframes omniPulseGlow {
    0% {
        box-shadow: 0 0 0 0 rgba(184, 32, 44, 0.6);
        background-color: rgba(251, 239, 239, 0.8);
    }
    50% {
        box-shadow: 0 0 0 16px rgba(184, 32, 44, 0);
        background-color: rgba(251, 239, 239, 0.4);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(184, 32, 44, 0);
        background-color: transparent;
    }
}

/* Dark Mode Overrides */
body.dark-mode .omni-search-content {
    background: #0f172a;
    border-color: #334155;
}
body.dark-mode .omni-search-header {
    background: #0f172a;
    border-bottom-color: #1e293b !important;
}
body.dark-mode .omni-search-input {
    color: #f8fafc;
}
body.dark-mode .omni-search-input::placeholder {
    color: #64748b;
}
body.dark-mode .omni-filter-bar {
    background: #1e293b;
    border-bottom-color: #334155 !important;
}
body.dark-mode .omni-filter-chip {
    background: #0f172a;
    border-color: #334155;
    color: #94a3b8;
}
body.dark-mode .omni-filter-chip:hover {
    background: #1e293b;
    color: #f8fafc;
}
body.dark-mode .omni-result-item:hover,
body.dark-mode .omni-result-item.is-selected {
    background-color: #1e293b;
    border-color: #334155;
}
body.dark-mode .omni-item-title {
    color: #f8fafc;
}
body.dark-mode .omni-item-desc {
    color: #94a3b8;
}
body.dark-mode .omni-search-footer {
    background: #1e293b !important;
    border-top-color: #334155 !important;
}
body.dark-mode .omni-kbd {
    background: #0f172a;
    border-color: #475569;
    color: #cbd5e1;
}
</style>

<script>
(function() {
    'use strict';

    let searchModalEl = null;
    let searchInputEl = null;
    let resultsContainerEl = null;
    let dynamicResultsEl = null;
    let initialStateEl = null;
    let emptyStateEl = null;
    let spinnerEl = null;
    let activeFilter = 'all';
    let currentResults = null;
    let selectedIndex = 0;
    let debounceTimer = null;
    let activeAbortController = null;
    let currentSearchQuery = '';
    const RECENT_SEARCHES_KEY = 'familyplatform_recent_searches';

    function getRecentSearches() {
        try {
            const raw = localStorage.getItem(RECENT_SEARCHES_KEY);
            return raw ? JSON.parse(raw) : [];
        } catch {
            return [];
        }
    }

    function saveRecentSearch(term) {
        const clean = (term || '').trim();
        if (!clean || clean.length < 2) return;
        try {
            let list = getRecentSearches();
            list = list.filter(item => item.toLowerCase() !== clean.toLowerCase());
            list.unshift(clean);
            if (list.length > 5) list = list.slice(0, 5);
            localStorage.setItem(RECENT_SEARCHES_KEY, JSON.stringify(list));
            renderRecentSearches();
        } catch (e) {
            console.warn('[OmniSearch] Could not save recent search:', e);
        }
    }

    function clearRecentSearches() {
        try {
            localStorage.removeItem(RECENT_SEARCHES_KEY);
            renderRecentSearches();
        } catch (e) {
            console.warn('[OmniSearch] Could not clear recent searches:', e);
        }
    }

    function renderRecentSearches() {
        const container = document.getElementById('omniRecentSearchesContainer');
        const chipsContainer = document.getElementById('omniRecentSearchesChips');
        if (!container || !chipsContainer) return;

        const list = getRecentSearches();
        if (!list || list.length === 0) {
            container.style.display = 'none';
            chipsContainer.innerHTML = '';
            return;
        }

        chipsContainer.innerHTML = '';
        list.forEach(term => {
            const chip = document.createElement('button');
            chip.type = 'button';
            chip.className = 'btn btn-sm omni-recent-chip';
            chip.innerHTML = `<i class="bi bi-search text-muted" style="font-size: 0.7rem;"></i> <span>${escapeHtml(term)}</span>`;
            chip.addEventListener('click', function() {
                if (searchInputEl) {
                    searchInputEl.value = term;
                    searchInputEl.dispatchEvent(new Event('input'));
                }
            });
            chipsContainer.appendChild(chip);
        });

        container.style.display = 'block';
    }

    function highlightText(text, query) {
        if (!text) return '';
        if (!query || query.trim() === '') return escapeHtml(text);
        const safeText = escapeHtml(text);
        const cleanTokens = query.trim().split(/\s+/).filter(t => t.length > 1).map(t => t.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'));
        if (cleanTokens.length === 0) return safeText;
        const pattern = new RegExp(`(${cleanTokens.join('|')})`, 'gi');
        return safeText.replace(pattern, '<mark class="omni-highlight">$1</mark>');
    }

    // Comprehensive client-side action catalog (guarantees instantaneous zero-latency discovery)
    const localActionCatalog = [
        // --- Family Journal & Chronicles ---
        {
            id: 'act_family_journal',
            title: 'Family Journal & Chronicles',
            description: 'Read chronicles, heritage reflections, and family milestones',
            keywords: ['family journal', 'journal', 'journals', 'diary', 'stories', 'chronicles', 'reflections', 'milestones', 'family chronicles', 'read journal', 'memories'],
            category: 'Functions & Tools',
            icon: 'bi-journal-bookmark-fill',
            iconColor: '#ea580c',
            badge: 'Stories',
            url: '/familyJournal',
            actionType: 'navigate',
            priority: 95
        },
        {
            id: 'act_write_journal',
            title: 'Write New Journal Entry',
            description: 'Record a new family story, chronicle, or memory with photos',
            keywords: ['write journal', 'new journal', 'create journal', 'add memory', 'write story', 'record reflection', 'new story', 'family journal', 'add journal'],
            category: 'Functions & Tools',
            icon: 'bi-pencil-square',
            iconColor: '#ca8a04',
            badge: 'Stories',
            url: '/familyJournal/create',
            actionType: 'navigate',
            priority: 94
        },

        // --- Lineage & Family Codes ---
        {
            id: 'act_create_family_code',
            title: 'Create Family Code',
            description: 'Generate a unique birth or household family code for your lineage',
            keywords: ['create family code', 'make family code', 'new family code', 'birth family code', 'dual lineage', 'family code', 'generate code', 'household code', 'create code', 'register family code'],
            category: 'Functions & Tools',
            icon: 'bi-key-fill',
            iconColor: '#b8202c',
            badge: 'Lineage',
            url: '/accountSetting#v-pills-familycode',
            targetElement: '#birthFamilyCard',
            actionType: 'deep_link',
            priority: 100
        },
        {
            id: 'act_link_birth_code',
            title: 'Link to Birth / Maiden Family Code',
            description: 'Connect your maternal lineage without changing your household code',
            keywords: ['link family code', 'connect birth code', 'maiden name code', 'maternal family code', 'dual family', 'link code'],
            category: 'Functions & Tools',
            icon: 'bi-link-45deg',
            iconColor: '#0d9488',
            badge: 'Lineage',
            url: '/accountSetting#v-pills-familycode',
            targetElement: '#linkBirthCodeForm',
            actionType: 'deep_link',
            priority: 92
        },
        {
            id: 'act_rename_family_code',
            title: 'Rename Family Code',
            description: 'Request an official surname or identifier rename for your family code',
            keywords: ['rename family code', 'change family code', 'edit family code', 'update family code', 'rename code'],
            category: 'Functions & Tools',
            icon: 'bi-pencil-fill',
            iconColor: '#d97706',
            badge: 'Lineage',
            url: '/accountSetting#v-pills-familycode',
            targetElement: '#renameCodeCard',
            actionType: 'deep_link',
            priority: 90
        },
        {
            id: 'act_family_migration',
            title: 'Family Migration & Code Discovery',
            description: 'Discover related family codes and request kinship network alignment',
            keywords: ['family migration', 'migrate family', 'discover family', 'switch family code', 'merge family'],
            category: 'Functions & Tools',
            icon: 'bi-arrow-left-right',
            iconColor: '#4f46e5',
            badge: 'Network',
            url: '/family/migrate',
            actionType: 'navigate',
            priority: 85
        },

        // --- Family Tree & Kinship ---
        {
            id: 'act_add_relative',
            title: 'Add Relative to Family Tree',
            description: 'Add parents, siblings, children, spouses, in-laws or aunts/uncles',
            keywords: ['add relative', 'add member', 'add parent', 'add child', 'add father', 'add mother', 'add sibling', 'add brother', 'add sister', 'add spouse', 'add wife', 'add husband', 'add in law', 'add uncle', 'add aunt', 'tree editor', 'lineage wizard'],
            category: 'Functions & Tools',
            icon: 'bi-person-plus-fill',
            iconColor: '#16a34a',
            badge: 'Family Tree',
            url: '/organogram/editor',
            targetElement: '#treeEditorApp',
            actionType: 'navigate',
            priority: 98
        },
        {
            id: 'act_view_tree',
            title: 'View Interactive Family Tree',
            description: 'Explore the visual multi-generational organogram canvas with pan & zoom',
            keywords: ['family tree', 'organogram', 'view tree', 'visual tree', 'ancestry', 'pedigree', 'canvas tree', 'kinship tree'],
            category: 'Functions & Tools',
            icon: 'bi-diagram-3-fill',
            iconColor: '#0284c7',
            badge: 'Family Tree',
            url: '/organogram',
            actionType: 'navigate',
            priority: 96
        },

        // --- Media & Memories ---
        {
            id: 'act_family_studio',
            title: 'Family Studio & Photo Albums',
            description: 'Browse, organise, and upload family photographs, milestones, and albums',
            keywords: ['family studio', 'photos', 'pictures', 'photo albums', 'gallery', 'images', 'upload photo', 'memories studio'],
            category: 'Functions & Tools',
            icon: 'bi-stars',
            iconColor: '#8b5cf6',
            badge: 'Media',
            url: '/familyStudio',
            actionType: 'navigate',
            priority: 93
        },
        {
            id: 'act_family_reels',
            title: 'Family Video Reels',
            description: 'Watch, record, and share short immersive family video moments',
            keywords: ['family reels', 'reels', 'videos', 'video clips', 'shorts', 'record video', 'watch reels'],
            category: 'Functions & Tools',
            icon: 'bi-camera-reels-fill',
            iconColor: '#e11d48',
            badge: 'Media',
            url: '/reels',
            actionType: 'navigate',
            priority: 88
        },
        {
            id: 'act_create_post',
            title: 'Create Post / Share Update',
            description: 'Share a celebration, announcement, or message on the family timeline',
            keywords: ['create post', 'new post', 'share update', 'post message', 'timeline update', 'celebration post', 'share photo'],
            category: 'Functions & Tools',
            icon: 'bi-chat-heart-fill',
            iconColor: '#b8202c',
            badge: 'Community',
            url: '/profilePage?action=newPost',
            targetElement: '#postModal',
            actionType: 'modal_trigger',
            priority: 91
        },

        // --- Community & Growth ---
        {
            id: 'act_invite_others',
            title: 'Invite Relatives & Friends',
            description: 'Send WhatsApp, SMS, or email invitation links with your family code',
            keywords: ['invite', 'invite others', 'invite relatives', 'bring family', 'share link', 'refer', 'invite code', 'recommend friends'],
            category: 'Functions & Tools',
            icon: 'bi-envelope-heart-fill',
            iconColor: '#f59e0b',
            badge: 'Growth',
            url: 'modal:recommendFriendsModal',
            targetElement: '#recommendFriendsModal',
            actionType: 'modal_trigger',
            priority: 97
        },
        {
            id: 'act_all_members',
            title: 'Family Directory & All Members',
            description: 'Search verified members, kinship connections, and view profiles',
            keywords: ['directory', 'all members', 'family members', 'members list', 'relatives list', 'kinship directory', 'contacts'],
            category: 'Functions & Tools',
            icon: 'bi-people-fill',
            iconColor: '#2563eb',
            badge: 'Directory',
            url: '/allMembers',
            actionType: 'navigate',
            priority: 89
        },

        // --- AI & Kinship Governance ---
        {
            id: 'act_roundtable',
            title: 'Family RoundTable & Dispute Council',
            description: 'Mediate household disagreements and kinship reconciliation councils',
            keywords: ['roundtable', 'dispute', 'council', 'arbitration', 'kinship reconciliation', 'disagreement', 'mediate', 'referee', 'family tribunal'],
            category: 'Functions & Tools',
            icon: 'bi-shield-check',
            iconColor: '#059669',
            badge: 'Council',
            url: '/family/roundtable',
            actionType: 'navigate',
            priority: 84
        },
        {
            id: 'act_ai_hub',
            title: 'Family AI Hub & Games',
            description: 'AI fridge recipes, family trivia generator, voice memos and event flyers',
            keywords: ['ai hub', 'the fun place', 'fridge recipe', 'inspect fridge', 'trivia', 'family games', 'voice memo', 'flyer generator', 'weekend outings', 'ai assistant'],
            category: 'Functions & Tools',
            icon: 'bi-robot',
            iconColor: '#9333ea',
            badge: 'AI Tools',
            url: '/family/fun-place',
            actionType: 'navigate',
            priority: 86
        },

        // --- Account, Security & Supervision ---
        {
            id: 'act_account_settings',
            title: 'Account Settings & Preferences',
            description: 'Manage notification preferences, display options, and account details',
            keywords: ['settings', 'account settings', 'preferences', 'notifications setting', 'configuration', 'my account'],
            category: 'Functions & Tools',
            icon: 'bi-gear-fill',
            iconColor: '#64748b',
            badge: 'Account',
            url: '/accountSetting',
            actionType: 'navigate',
            priority: 80
        },
        {
            id: 'act_edit_profile',
            title: 'Edit Profile Details & Avatar',
            description: 'Update your full name, bio, telephone number, and profile photograph',
            keywords: ['edit profile', 'profile photo', 'profile picture', 'avatar', 'change photo', 'update bio', 'personal details'],
            category: 'Functions & Tools',
            icon: 'bi-person-badge-fill',
            iconColor: '#0284c7',
            badge: 'Account',
            url: '/accountSetting#v-pills-profile',
            targetElement: '#v-pills-profile',
            actionType: 'deep_link',
            priority: 82
        },
        {
            id: 'act_security_passwords',
            title: 'Security, PIN & Passkeys',
            description: 'Update login password, parent/child PIN, and configure biometric passkeys',
            keywords: ['password', 'change password', 'pin', 'change pin', 'security', 'passkeys', 'biometrics', 'credentials'],
            category: 'Functions & Tools',
            icon: 'bi-shield-lock-fill',
            iconColor: '#dc2626',
            badge: 'Security',
            url: '/accountSetting#v-pills-password',
            targetElement: '#v-pills-password',
            actionType: 'deep_link',
            priority: 81
        },
        {
            id: 'act_child_accounts',
            title: 'Child Accounts & Parental Supervision',
            description: 'Switch between child accounts and manage underage family members',
            keywords: ['child accounts', 'kids', 'children', 'switch child', 'parent pin', 'child profile', 'kid account', 'supervision'],
            category: 'Functions & Tools',
            icon: 'bi-balloon-heart-fill',
            iconColor: '#ec4899',
            badge: 'Family',
            url: '/profilePage#childAccountsSection',
            actionType: 'navigate',
            priority: 79
        },
        {
            id: 'act_playbook',
            title: 'Living Guide & Playbook',
            description: 'Explore the architectural guide, how-to-use workflows, and tutorials',
            keywords: ['guide', 'how to use', 'playbook', 'help', 'documentation', 'tutorial', 'instructions', 'faq'],
            category: 'Functions & Tools',
            icon: 'bi-book-half',
            iconColor: '#6366f1',
            badge: 'Help',
            url: '/playbook',
            actionType: 'navigate',
            priority: 77
        },
        {
            id: 'act_toggle_theme',
            title: 'Toggle Theme (Dark / Light Mode)',
            description: 'Switch between high-contrast dark mode and daylight mode',
            keywords: ['theme', 'dark mode', 'light mode', 'night mode', 'toggle theme', 'appearance'],
            category: 'Functions & Tools',
            icon: 'bi-moon-stars-fill',
            iconColor: '#f59e0b',
            badge: 'Appearance',
            url: 'action:toggleTheme',
            actionType: 'action',
            priority: 76
        },
        {
            id: 'act_signout',
            title: 'Sign Out',
            description: 'Securely log off and end your active FamilyPlatform session',
            keywords: ['logout', 'sign out', 'exit', 'log off'],
            category: 'Functions & Tools',
            icon: 'bi-box-arrow-right',
            iconColor: '#ef4444',
            badge: 'Auth',
            url: '/signout',
            actionType: 'navigate',
            priority: 50
        }
    ];

    function initOmniSearch() {
        searchModalEl = document.getElementById('omniSearchModal');
        searchInputEl = document.getElementById('omniSearchInput');
        resultsContainerEl = document.getElementById('omniSearchResultsContainer');
        dynamicResultsEl = document.getElementById('omniDynamicResults');
        initialStateEl = document.getElementById('omniInitialState');
        emptyStateEl = document.getElementById('omniEmptyState');
        spinnerEl = document.getElementById('omniSearchSpinner');

        if (!searchModalEl || !searchInputEl) return;

        // Render initial recommended quick actions & recent searches
        renderRecentSearches();
        const clearRecentBtn = document.getElementById('omniClearRecentBtn');
        if (clearRecentBtn) {
            clearRecentBtn.addEventListener('click', clearRecentSearches);
        }

        const topActions = localActionCatalog.filter(a => [
            'act_create_family_code',
            'act_family_journal',
            'act_add_relative',
            'act_family_studio',
            'act_write_journal',
            'act_invite_others'
        ].includes(a.id));
        renderQuickActions(topActions.length ? topActions : localActionCatalog.slice(0, 6));

        // Autofocus input on modal open
        searchModalEl.addEventListener('shown.bs.modal', function() {
            selectedIndex = 0;
            searchInputEl.value = '';
            currentSearchQuery = '';
            renderRecentSearches();
            showInitialState();
            searchInputEl.focus();
        });

        // Input search listener (immediate local filter + debounced API sync with AbortController)
        searchInputEl.addEventListener('input', function() {
            const query = this.value.trim();
            currentSearchQuery = query;
            clearTimeout(debounceTimer);

            if (!query) {
                if (activeAbortController) activeAbortController.abort();
                showInitialState();
                return;
            }

            // 1. Instantaneous local scoring (0ms latency, zero delay)
            filterLocalActionsImmediately(query);

            // 2. Debounced API call for database records with active in-flight request cancellation
            if (spinnerEl) spinnerEl.style.display = 'inline-block';
            debounceTimer = setTimeout(function() {
                executeRemoteSearch(query);
            }, 180);
        });

        // Filter chips click
        document.querySelectorAll('.omni-filter-chip').forEach(function(chip) {
            chip.addEventListener('click', function() {
                document.querySelectorAll('.omni-filter-chip').forEach(c => c.classList.remove('active'));
                this.classList.add('active');
                activeFilter = this.getAttribute('data-filter') || 'all';
                renderCategorizedResults(currentResults, activeFilter);
            });
        });

        // Suggestion buttons in empty state
        document.querySelectorAll('.omni-suggest-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const q = this.getAttribute('data-query') || '';
                searchInputEl.value = q;
                searchInputEl.dispatchEvent(new Event('input'));
            });
        });

        // Keyboard navigation (ArrowDown, ArrowUp, Enter, Esc)
        searchInputEl.addEventListener('keydown', handleKeyNavigation);
        searchModalEl.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                e.preventDefault();
                handleKeyNavigation(e);
            }
        });

        // Global Keyboard Shortcut: Cmd+K / Ctrl+K / '/'
        document.addEventListener('keydown', function(e) {
            const tag = (e.target && e.target.tagName) ? e.target.tagName.toUpperCase() : '';
            const isEditing = tag === 'INPUT' || tag === 'TEXTAREA' || (e.target && e.target.isContentEditable);

            if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
                e.preventDefault();
                openOmniSearch();
            } else if (!isEditing && e.key === '/' && !e.ctrlKey && !e.metaKey && !e.altKey) {
                e.preventDefault();
                openOmniSearch();
            }
        });
    }

    function openOmniSearch() {
        if (!searchModalEl) return;
        if (window.bootstrap && window.bootstrap.Modal) {
            const modal = window.bootstrap.Modal.getOrCreateInstance(searchModalEl);
            modal.show();
        }
    }

    function showInitialState() {
        if (spinnerEl) spinnerEl.style.display = 'none';
        if (initialStateEl) initialStateEl.style.display = 'block';
        if (dynamicResultsEl) dynamicResultsEl.style.display = 'none';
        if (emptyStateEl) emptyStateEl.style.display = 'none';
        selectedIndex = 0;
        updateSelectedHighlight();
    }

    function renderQuickActions(actions) {
        const listEl = document.getElementById('omniQuickActionsList');
        if (!listEl) return;
        listEl.innerHTML = '';

        actions.forEach((act, idx) => {
            const item = createResultItemElement(act, idx);
            listEl.appendChild(item);
        });
        updateSelectedHighlight();
    }

    function filterLocalActionsImmediately(query) {
        const cleanQuery = query.toLowerCase().trim();
        const tokens = cleanQuery.split(/\s+/).filter(t => t.length > 1);

        const scored = [];
        localActionCatalog.forEach(act => {
            let score = 0;
            const title = act.title.toLowerCase();
            const desc = act.description.toLowerCase();
            const keywords = Array.isArray(act.keywords) ? act.keywords.map(k => k.toLowerCase()) : [];

            // Exact phrase match
            if (title === cleanQuery) score += 300;
            else if (title.includes(cleanQuery)) score += 200;

            keywords.forEach(k => {
                if (k === cleanQuery) score += 280;
                else if (k.includes(cleanQuery)) score += 140;
            });

            if (desc.includes(cleanQuery)) score += 80;

            // Multi-token overlap
            tokens.forEach(tok => {
                if (title.includes(tok)) score += 60;
                if (desc.includes(tok)) score += 25;
                keywords.forEach(k => {
                    if (k.includes(tok)) score += 45;
                });
            });

            // Specific intent boosts
            if (cleanQuery.includes('journal') && (act.id === 'act_family_journal' || act.id === 'act_write_journal')) {
                score += 250;
            }
            if (cleanQuery.includes('code') && (act.id === 'act_create_family_code' || act.id === 'act_link_birth_code')) {
                score += 220;
            }
            if (cleanQuery.includes('tree') && (act.id === 'act_view_tree' || act.id === 'act_add_relative')) {
                score += 200;
            }

            if (score > 0) {
                scored.push(Object.assign({}, act, { matchScore: score + (act.priority || 0) }));
            }
        });

        scored.sort((a, b) => b.matchScore - a.matchScore);

        currentResults = {
            query: query,
            categories: {
                actions: scored.slice(0, 8),
                members: (currentResults && currentResults.categories && currentResults.categories.members) ? currentResults.categories.members : [],
                journals: (currentResults && currentResults.categories && currentResults.categories.journals) ? currentResults.categories.journals : [],
                posts: (currentResults && currentResults.categories && currentResults.categories.posts) ? currentResults.categories.posts : [],
                events: (currentResults && currentResults.categories && currentResults.categories.events) ? currentResults.categories.events : [],
                reels: (currentResults && currentResults.categories && currentResults.categories.reels) ? currentResults.categories.reels : []
            }
        };

        renderCategorizedResults(currentResults, activeFilter);
    }

    function executeRemoteSearch(query) {
        if (activeAbortController) {
            activeAbortController.abort();
        }
        activeAbortController = new AbortController();
        const signal = activeAbortController.signal;

        fetch(`/api/omni-search?q=${encodeURIComponent(query)}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin',
            signal: signal
        })
        .then(res => {
            if (!res.ok) throw new Error('HTTP ' + res.status);
            return res.json();
        })
        .then(json => {
            if (spinnerEl) spinnerEl.style.display = 'none';
            if (json && json.status === 'success' && json.data) {
                const sData = json.data;
                // Merge actions: ensure local and remote best matches are combined
                const mergedActions = (sData.categories && sData.categories.actions && sData.categories.actions.length)
                    ? sData.categories.actions
                    : (currentResults ? currentResults.categories.actions : []);

                currentResults = {
                    query: sData.query || query,
                    total: sData.total || 0,
                    categories: {
                        actions: mergedActions,
                        members: (sData.categories && sData.categories.members) || [],
                        journals: (sData.categories && sData.categories.journals) || [],
                        posts: (sData.categories && sData.categories.posts) || [],
                        events: (sData.categories && sData.categories.events) || [],
                        reels: (sData.categories && sData.categories.reels) || []
                    }
                };
                renderCategorizedResults(currentResults, activeFilter);
            }
        })
        .catch(err => {
            if (err.name === 'AbortError') {
                return; // Normal: newer keystroke in-flight, discard superseded request silently
            }
            if (spinnerEl) spinnerEl.style.display = 'none';
            console.warn('[OmniSearch] Remote search notice:', err.message);
            // On remote error, local actions remain displayed seamlessly
        });
    }

    function renderCategorizedResults(data, filter) {
        if (!dynamicResultsEl) return;

        if (initialStateEl) initialStateEl.style.display = 'none';
        dynamicResultsEl.style.display = 'block';
        dynamicResultsEl.innerHTML = '';

        if (!data || !data.categories) {
            showEmptyState(data ? data.query : '');
            return;
        }

        const cats = data.categories;
        let totalRendered = 0;

        const sections = [
            { key: 'actions', label: 'Functions & Activities', icon: 'bi-lightning-charge-fill', items: cats.actions || [] },
            { key: 'journals', label: 'Family Journals', icon: 'bi-journal-bookmark-fill', items: cats.journals || [] },
            { key: 'members', label: 'Family Members', icon: 'bi-people-fill', items: cats.members || [] },
            { key: 'posts', label: 'Timeline Updates', icon: 'bi-chat-left-quote-fill', items: cats.posts || [] },
            { key: 'events', label: 'Events & Celebrations', icon: 'bi-calendar-event-fill', items: cats.events || [] },
            { key: 'reels', label: 'Family Video Reels', icon: 'bi-camera-reels-fill', items: cats.reels || [] }
        ];

        // If filter is specific to 'journals' and no data journals were returned, surface journal actions
        if (filter === 'journals' && (!cats.journals || cats.journals.length === 0)) {
            const jActions = (cats.actions || []).filter(a => a.id === 'act_family_journal' || a.id === 'act_write_journal');
            if (jActions.length) {
                const header = document.createElement('div');
                header.className = 'omni-category-title d-flex align-items-center justify-content-between';
                header.innerHTML = `<span><i class="bi bi-journal-text me-1.5 text-danger"></i> Journal Shortcuts</span>`;
                dynamicResultsEl.appendChild(header);
                jActions.forEach(itemData => {
                    dynamicResultsEl.appendChild(createResultItemElement(itemData, totalRendered));
                    totalRendered++;
                });
            }
        }

        sections.forEach(sec => {
            if (filter !== 'all' && filter !== sec.key) return;
            if (!sec.items || sec.items.length === 0) return;

            // Section title
            const header = document.createElement('div');
            header.className = 'omni-category-title d-flex align-items-center justify-content-between';
            header.innerHTML = `
                <span><i class="bi ${sec.icon} me-1.5 text-primary"></i> ${sec.label}</span>
                <span class="badge bg-secondary-subtle text-secondary rounded-pill">${sec.items.length}</span>
            `;
            dynamicResultsEl.appendChild(header);

            // Item list
            sec.items.forEach(itemData => {
                const itemEl = createResultItemElement(itemData, totalRendered);
                dynamicResultsEl.appendChild(itemEl);
                totalRendered++;
            });
        });

        if (totalRendered === 0) {
            showEmptyState(data.query || '');
        } else {
            if (emptyStateEl) emptyStateEl.style.display = 'none';
            selectedIndex = 0;
            updateSelectedHighlight();
        }
    }

    function showEmptyState(query) {
        if (dynamicResultsEl) dynamicResultsEl.style.display = 'none';
        if (emptyStateEl) {
            emptyStateEl.style.display = 'block';
            const querySpan = document.getElementById('omniEmptyQuery');
            if (querySpan) querySpan.textContent = query;

            // Viral Lineage Invite Trigger on zero-match member discoveries
            const inviteCard = document.getElementById('omniInviteCard');
            const inviteNameSpan = document.getElementById('omniInviteTargetName');
            const inviteBtn = document.getElementById('omniInviteActionBtn');

            if (inviteCard && inviteNameSpan && inviteBtn) {
                if (query && query.trim().length >= 2) {
                    inviteCard.style.display = 'block';
                    inviteNameSpan.textContent = `"${query.trim()}"`;
                    inviteBtn.onclick = function() {
                        if (searchModalEl && window.bootstrap && window.bootstrap.Modal) {
                            const modal = window.bootstrap.Modal.getInstance(searchModalEl);
                            if (modal) modal.hide();
                        }
                        setTimeout(function() {
                            const recModal = document.getElementById('recommendFriendsModal');
                            if (recModal && window.bootstrap && window.bootstrap.Modal) {
                                const m = window.bootstrap.Modal.getOrCreateInstance(recModal);
                                m.show();
                                const nameInput = recModal.querySelector('input[name="friendName"], input[name="name"], #friendName');
                                if (nameInput) {
                                    nameInput.value = query.trim();
                                    nameInput.focus();
                                }
                            } else {
                                window.location.href = `/profilePage?action=invite&name=${encodeURIComponent(query.trim())}`;
                            }
                        }, 250);
                    };
                } else {
                    inviteCard.style.display = 'none';
                }
            }
        }
    }

    function createResultItemElement(itemData, index) {
        const a = document.createElement('a');
        a.className = 'omni-result-item';
        a.href = itemData.url || 'javascript:void(0)';
        a.setAttribute('data-index', index);
        a.setAttribute('data-action-type', itemData.actionType || 'navigate');
        if (itemData.targetElement) {
            a.setAttribute('data-target-element', itemData.targetElement);
        }

        const iconClass = itemData.icon || 'bi-arrow-right-circle';
        const iconColor = itemData.iconColor || '#b8202c';
        const badge = itemData.badge ? `<span class="badge rounded-pill bg-light text-secondary border px-2 py-0.5" style="font-size: 0.72rem;">${escapeHtml(itemData.badge)}</span>` : '';

        // Avatar or Icon Box
        let iconHtml = '';
        if (itemData.avatar) {
            iconHtml = `
                <div class="omni-item-icon-box bg-light overflow-hidden">
                    <img src="${escapeHtml(itemData.avatar)}" alt="" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.onerror=null; this.src='/public/img/default-avatar.png';">
                </div>
            `;
        } else {
            iconHtml = `
                <div class="omni-item-icon-box" style="background: rgba(184, 32, 44, 0.08); color: ${iconColor};">
                    <i class="bi ${iconClass}"></i>
                </div>
            `;
        }

        const titleHtml = currentSearchQuery ? highlightText(itemData.title, currentSearchQuery) : escapeHtml(itemData.title);
        const descHtml = currentSearchQuery ? highlightText(itemData.description, currentSearchQuery) : escapeHtml(itemData.description);

        a.innerHTML = `
            ${iconHtml}
            <div class="flex-grow-1 overflow-hidden">
                <div class="d-flex align-items-center justify-content-between gap-2">
                    <div class="omni-item-title text-truncate">${titleHtml}</div>
                    ${badge}
                </div>
                <div class="omni-item-desc text-truncate">${descHtml}</div>
            </div>
            <div class="text-muted small ps-1 opacity-50">
                <i class="bi bi-chevron-right"></i>
            </div>
        `;

        a.addEventListener('click', function(e) {
            e.preventDefault();
            executeItemAction(itemData);
        });

        return a;
    }

    function executeItemAction(itemData) {
        if (!itemData) return;

        if (currentSearchQuery) {
            saveRecentSearch(currentSearchQuery);
        }

        // Dismiss search modal first
        if (searchModalEl && window.bootstrap && window.bootstrap.Modal) {
            const modal = window.bootstrap.Modal.getInstance(searchModalEl);
            if (modal) modal.hide();
        }

        const url = itemData.url || '';
        const actionType = itemData.actionType || 'navigate';
        const targetElement = itemData.targetElement || '';

        // Handle specific action types
        if (actionType === 'modal_trigger' || url.startsWith('modal:')) {
            const modalId = targetElement || url.replace(/^modal:/, '#');
            setTimeout(function() {
                const targetModal = document.querySelector(modalId.startsWith('#') ? modalId : '#' + modalId);
                if (targetModal && window.bootstrap && window.bootstrap.Modal) {
                    const m = window.bootstrap.Modal.getOrCreateInstance(targetModal);
                    m.show();
                } else {
                    window.location.href = '/profilePage?action=invite';
                }
            }, 250);
            return;
        }

        if (actionType === 'action' || url === 'action:toggleTheme') {
            const darkToggle = document.getElementById('darkModeToggle');
            if (darkToggle) darkToggle.click();
            return;
        }

        // Deep-link navigation
        if (url) {
            const currentPath = window.location.pathname.toLowerCase();
            const targetUrlObj = new URL(url, window.location.origin);
            const targetPath = targetUrlObj.pathname.toLowerCase();

            if (currentPath === targetPath && targetUrlObj.hash) {
                // Same page hash change
                window.location.hash = targetUrlObj.hash;
                if (targetElement) {
                    const el = document.querySelector(targetElement);
                    if (el) {
                        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        el.classList.add('pulse-highlight-target');
                        setTimeout(() => el.classList.remove('pulse-highlight-target'), 2500);
                    }
                }
            } else {
                // Navigate to URL
                window.location.href = url;
            }
        }
    }

    function handleKeyNavigation(e) {
        const visibleItems = document.querySelectorAll('.omni-search-body .omni-result-item:not([style*="display: none"])');
        if (!visibleItems || visibleItems.length === 0) return;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            selectedIndex = (selectedIndex + 1) % visibleItems.length;
            updateSelectedHighlight();
            scrollSelectedItemIntoView();
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            selectedIndex = (selectedIndex - 1 + visibleItems.length) % visibleItems.length;
            updateSelectedHighlight();
            scrollSelectedItemIntoView();
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (currentSearchQuery) {
                saveRecentSearch(currentSearchQuery);
            }
            if (visibleItems[selectedIndex]) {
                visibleItems[selectedIndex].click();
            }
        }
    }

    function updateSelectedHighlight() {
        const visibleItems = document.querySelectorAll('.omni-search-body .omni-result-item:not([style*="display: none"])');
        visibleItems.forEach((item, idx) => {
            if (idx === selectedIndex) {
                item.classList.add('is-selected');
                item.setAttribute('aria-selected', 'true');
            } else {
                item.classList.remove('is-selected');
                item.removeAttribute('aria-selected');
            }
        });
    }

    function scrollSelectedItemIntoView() {
        const visibleItems = document.querySelectorAll('.omni-search-body .omni-result-item:not([style*="display: none"])');
        const selected = visibleItems[selectedIndex];
        if (selected && resultsContainerEl) {
            const containerTop = resultsContainerEl.scrollTop;
            const containerBottom = containerTop + resultsContainerEl.clientHeight;
            const elemTop = selected.offsetTop;
            const elemBottom = elemTop + selected.offsetHeight;

            if (elemTop < containerTop) {
                resultsContainerEl.scrollTop = elemTop - 10;
            } else if (elemBottom > containerBottom) {
                resultsContainerEl.scrollTop = elemBottom - resultsContainerEl.clientHeight + 10;
            }
        }
    }

    function escapeHtml(text) {
        if (!text) return '';
        const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return String(text).replace(/[&<>"']/g, m => map[m]);
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initOmniSearch);
    } else {
        initOmniSearch();
    }
})();
</script>
