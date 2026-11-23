import { useCallback, useEffect, useMemo, useRef, useState } from 'preact/hooks';
import { getCsrfToken } from '../shared/bootstrap';
import { fetchAccountSettingsState } from './api/accountSettingsApi';
import { ProfileTab } from './tabs/ProfileTab';
import { PasswordTab } from './tabs/PasswordTab';
import { PreferencesTab } from './tabs/PreferencesTab';
import { PrivacyTab } from './tabs/PrivacyTab';
import { ParentsTab } from './tabs/ParentsTab';
import { ChildrenTab } from './tabs/ChildrenTab';
import { SiblingsTab } from './tabs/SiblingsTab';
import { MaritalTab } from './tabs/MaritalTab';
import { FamilyCodeTab } from './tabs/FamilyCodeTab';
import styles from './AccountSettingsShell.module.css';

const ACCOUNT_TABS = [
    {
        id: 'profile',
        group: 'account',
        label: 'Profile & Work',
        icon: 'bi-person-fill',
        keywords: 'profile name email mobile country work occupation employment status contact',
    },
    {
        id: 'password',
        group: 'account',
        label: 'Password',
        icon: 'bi-lock-fill',
        keywords: 'password security change reset',
    },
    {
        id: 'preferences',
        group: 'account',
        label: 'Preferences',
        icon: 'bi-bell-fill',
        keywords: 'notifications email sms push diagnostics pwa app install tooltip mastery',
    },
    {
        id: 'privacy',
        group: 'account',
        label: 'Privacy',
        icon: 'bi-shield-fill-check',
        keywords: 'privacy 2fa two-factor visibility birth year biometric passkey gdpr export delete restrict block',
    },
    {
        id: 'parents',
        group: 'family',
        label: 'Parents',
        icon: 'bi-people-fill',
        keywords: 'parents father mother maiden lineage birth tree studio',
    },
    {
        id: 'children',
        group: 'family',
        label: 'Children',
        icon: 'bi-person-heart',
        keywords: 'children kids son daughter former partner invite',
    },
    {
        id: 'siblings',
        group: 'family',
        label: 'Siblings',
        icon: 'bi-person-lines-fill',
        keywords: 'siblings brother sister half step adopted lineage invite',
    },
    {
        id: 'marital',
        group: 'family',
        label: 'Marital Status',
        icon: 'bi-heart-fill',
        keywords: 'marital spouse husband wife wedding anniversary divorced widowed maiden',
    },
    {
        id: 'familycode',
        group: 'family',
        label: 'Family Network & Code',
        icon: 'bi-diagram-3-fill',
        keywords: 'family code dual lineage birth maiden solo branch out join transfer rename vote',
    },
];

const HASH_ALIAS_MAP = {
    'v-pills-profile': { tab: 'profile', focusId: null },
    profile: { tab: 'profile', focusId: null },
    'v-pills-password': { tab: 'password', focusId: null },
    password: { tab: 'password', focusId: null },
    'v-pills-preferences': { tab: 'preferences', focusId: null },
    preferences: { tab: 'preferences', focusId: null },
    'v-pills-privacy': { tab: 'privacy', focusId: null },
    privacy: { tab: 'privacy', focusId: null },
    memberrestrictioncard: { tab: 'privacy', focusId: 'memberRestrictionCard' },
    'v-pills-parents': { tab: 'parents', focusId: null },
    parents: { tab: 'parents', focusId: null },
    'family-settings': { tab: 'parents', focusId: 'family-settings' },
    'family-connections': { tab: 'parents', focusId: 'familyConnectionsHeading' },
    familyconnections: { tab: 'parents', focusId: 'familyConnectionsHeading' },
    'v-pills-children': { tab: 'children', focusId: null },
    children: { tab: 'children', focusId: null },
    'v-pills-siblings': { tab: 'siblings', focusId: null },
    siblings: { tab: 'siblings', focusId: null },
    'v-pills-marital': { tab: 'marital', focusId: null },
    marital: { tab: 'marital', focusId: null },
    'v-pills-familycode': { tab: 'familycode', focusId: 'birthFamilyCard' },
    familycode: { tab: 'familycode', focusId: 'birthFamilyCard' },
    'family-code-mgt': { tab: 'familycode', focusId: 'birthFamilyCard' },
    familycodemgt: { tab: 'familycode', focusId: 'birthFamilyCard' },
    lineage: { tab: 'familycode', focusId: 'birthFamilyCard' },
    'dual-lineage': { tab: 'familycode', focusId: 'birthFamilyCard' },
    duallineage: { tab: 'familycode', focusId: 'birthFamilyCard' },
    birthfamilycard: { tab: 'familycode', focusId: 'birthFamilyCard' },
    coderenamecard: { tab: 'familycode', focusId: 'codeRenameCard' },
    pendingrequestalert: { tab: 'familycode', focusId: 'pendingRequestAlert' },
};

function resolveHashTarget(hashVal) {
    if (!hashVal) return null;
    const clean = String(hashVal).replace(/^#/, '').trim();
    if (!clean) return null;
    const mapped = HASH_ALIAS_MAP[clean.toLowerCase()];
    if (mapped) return mapped;

    if (typeof document !== 'undefined') {
        const el = document.getElementById(clean);
        if (el) {
            const pane = el.closest('.tab-pane');
            if (pane && pane.id) {
                const paneMapped = HASH_ALIAS_MAP[pane.id.toLowerCase()];
                if (paneMapped) return { tab: paneMapped.tab, focusId: clean };
            }
        }
    }
    return null;
}

function getInitialTabFromLocation() {
    if (typeof window === 'undefined') return 'profile';
    const hash = window.location.hash;
    if (hash) {
        const resolved = resolveHashTarget(hash);
        if (resolved) return resolved.tab;
    }
    const tabParam = new URLSearchParams(window.location.search).get('tab');
    if (tabParam) {
        const resolved = resolveHashTarget(tabParam);
        if (resolved) return resolved.tab;
    }
    return 'profile';
}

export function AccountSettingsShell({ bootstrap = {} }) {
    const [accountData, setAccountData] = useState(() => bootstrap.accountData || {});
    const [pendingFamilyRequest, setPendingFamilyRequest] = useState(
        () => bootstrap.pendingFamilyRequest || null
    );
    const [birthFamily, setBirthFamily] = useState(
        () => bootstrap.birthFamily || { state: 'none', code: '', origin: null, pending: null }
    );
    const [codeRename, setCodeRename] = useState(
        () => bootstrap.codeRename || { is_creator: false, can_propose: false, code: '', open: null, cooldown: false }
    );
    const [passkeys, setPasskeys] = useState(() =>
        Array.isArray(bootstrap.passkeys) ? bootstrap.passkeys : []
    );
    const [restrictionDirectory, setRestrictionDirectory] = useState(() =>
        Array.isArray(bootstrap.restrictionDirectory) ? bootstrap.restrictionDirectory : []
    );
    const [maxWeddingDate, setMaxWeddingDate] = useState(() => bootstrap.maxWeddingDate || '');
    const [csrfToken, setCsrfToken] = useState(() => getCsrfToken(bootstrap));

    const [activeTab, setActiveTab] = useState(() => getInitialTabFromLocation());
    const [searchQuery, setSearchQuery] = useState('');
    const [dirtyTabs, setDirtyTabs] = useState({});
    const [pendingTabSwitch, setPendingTabSwitch] = useState(null);
    const [focusRequest, setFocusRequest] = useState(null);
    const pulseTimerRef = useRef(null);

    const notice = bootstrap.notice || (
        typeof window !== 'undefined'
            ? new URLSearchParams(window.location.search).get('notice') || ''
            : ''
    );

    const hasAnyDirty = useMemo(
        () => Object.values(dirtyTabs).some(Boolean),
        [dirtyTabs]
    );

    useEffect(() => {
        if (typeof window === 'undefined') return undefined;
        const handleBeforeUnload = (e) => {
            if (!hasAnyDirty) return undefined;
            e.preventDefault();
            e.returnValue = '';
            return '';
        };
        window.addEventListener('beforeunload', handleBeforeUnload);
        return () => window.removeEventListener('beforeunload', handleBeforeUnload);
    }, [hasAnyDirty]);

    const applyHighlightToTarget = useCallback((elementId) => {
        if (!elementId || typeof document === 'undefined') return;
        const target = document.getElementById(elementId);
        if (!target) return;

        if (pulseTimerRef.current) {
            clearTimeout(pulseTimerRef.current);
        }

        if (typeof target.scrollIntoView === 'function') {
            target.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        target.classList.add('pulse-highlight-target');
        const focusable = target.querySelector('input:not([type="hidden"]), select, button.btn-primary');
        if (focusable && typeof focusable.focus === 'function') {
            try {
                focusable.focus({ preventScroll: true });
            } catch {
                // Ignore non-focusable elements in JSDOM
            }
        }
        pulseTimerRef.current = setTimeout(() => {
            target.classList.remove('pulse-highlight-target');
        }, 2500);
    }, []);

    useEffect(() => {
        if (!focusRequest) return undefined;
        const timer = setTimeout(() => {
            applyHighlightToTarget(focusRequest);
            setFocusRequest(null);
        }, 120);
        return () => clearTimeout(timer);
    }, [activeTab, focusRequest, applyHighlightToTarget]);

    const handleDeepLinkTarget = useCallback((hashVal) => {
        if (!hashVal) return;
        const resolved = resolveHashTarget(hashVal);
        if (!resolved) return;
        setActiveTab(resolved.tab);
        setPendingTabSwitch(null);
        if (resolved.focusId) {
            setFocusRequest(resolved.focusId);
        }
    }, []);

    useEffect(() => {
        if (typeof window === 'undefined') return undefined;
        const initialHash = window.location.hash;
        const initialParam = new URLSearchParams(window.location.search).get('tab');
        if (initialHash) {
            handleDeepLinkTarget(initialHash);
        } else if (initialParam) {
            handleDeepLinkTarget(`#${initialParam}`);
        }

        const onHashChange = () => {
            handleDeepLinkTarget(window.location.hash);
        };
        window.addEventListener('hashchange', onHashChange);
        return () => window.removeEventListener('hashchange', onHashChange);
    }, [handleDeepLinkTarget]);

    const handleDirtyChange = useCallback((tabId, dirty) => {
        setDirtyTabs((prev) => {
            if (Boolean(prev[tabId]) === Boolean(dirty)) return prev;
            return { ...prev, [tabId]: Boolean(dirty) };
        });
    }, []);

    const performTabSwitch = useCallback((nextTabId) => {
        setPendingTabSwitch(null);
        setActiveTab(nextTabId);
        if (typeof window !== 'undefined' && window.history && typeof window.history.replaceState === 'function') {
            window.history.replaceState(null, '', `#v-pills-${nextTabId}`);
        }
    }, []);

    const handleSelectTab = useCallback(
        (nextTabId) => {
            if (nextTabId === activeTab) return;
            if (dirtyTabs[activeTab]) {
                setPendingTabSwitch(nextTabId);
                return;
            }
            performTabSwitch(nextTabId);
        },
        [activeTab, dirtyTabs, performTabSwitch]
    );

    const handleSavedAccountData = useCallback((patch) => {
        setAccountData((prev) => ({ ...prev, ...patch }));
    }, []);

    const handleRefreshState = useCallback(async () => {
        try {
            const fresh = await fetchAccountSettingsState(bootstrap);
            if (!fresh) return;
            if (fresh.accountData) setAccountData(fresh.accountData);
            if ('pendingFamilyRequest' in fresh) {
                setPendingFamilyRequest(fresh.pendingFamilyRequest || null);
            }
            if (fresh.birthFamily) setBirthFamily(fresh.birthFamily);
            if (fresh.codeRename) setCodeRename(fresh.codeRename);
            if (Array.isArray(fresh.passkeys)) setPasskeys(fresh.passkeys);
            if (Array.isArray(fresh.restrictionDirectory)) {
                setRestrictionDirectory(fresh.restrictionDirectory);
            }
            if (fresh.maxWeddingDate) setMaxWeddingDate(fresh.maxWeddingDate);
            if (fresh.csrfToken) setCsrfToken(fresh.csrfToken);
        } catch {
            // Preserve current local state on transient refresh error
        }
    }, [bootstrap]);

    const filteredTabs = useMemo(() => {
        const q = searchQuery.trim().toLowerCase();
        if (!q) return ACCOUNT_TABS;
        return ACCOUNT_TABS.filter(
            (tab) =>
                tab.label.toLowerCase().includes(q) ||
                tab.keywords.toLowerCase().includes(q)
        );
    }, [searchQuery]);

    const accountGroupTabs = filteredTabs.filter((t) => t.group === 'account');
    const familyGroupTabs = filteredTabs.filter((t) => t.group === 'family');
    const currentTabObj = ACCOUNT_TABS.find((t) => t.id === activeTab) || ACCOUNT_TABS[0];

    return (
        <div className={styles.settingsShell} data-testid="account-settings-shell">
            {/* Page Header */}
            <header className={styles.pageHeader}>
                <h1 className={styles.pageTitle}>Account Settings</h1>
                <p className={styles.pageSubtitle}>
                    Manage your profile, preferences, family connections, and security settings.
                </p>
            </header>

            {notice === 'email_required_for_party_studio' && (
                <div
                    className="alert alert-warning d-flex align-items-center gap-3 mb-4 rounded-3 shadow-sm"
                    role="alert"
                >
                    <i className="bi bi-envelope-exclamation-fill fs-4 text-warning" aria-hidden="true" />
                    <div>
                        <strong>Email Address Required for PartyPlatform Studio:</strong> Please add a valid email address to your profile below so your PartyPlatform host account and RSVP notifications can be linked.
                    </div>
                </div>
            )}

            <div className={styles.layoutGrid}>
                {/* Sidebar Navigation (Desktop Vertical + Mobile Horizontal Pills) */}
                <aside className={styles.sidebarCard} aria-label="Account settings navigation">
                    <div className={styles.searchBoxWrap}>
                        <label htmlFor="settingsQuickSearch" className="visually-hidden">
                            Search settings
                        </label>
                        <input
                            id="settingsQuickSearch"
                            type="search"
                            className={`form-control form-control-sm ${styles.formInput}`}
                            placeholder="Filter settings..."
                            value={searchQuery}
                            onInput={(e) => setSearchQuery(e.currentTarget.value)}
                        />
                    </div>

                    {filteredTabs.length === 0 ? (
                        <div className="px-3 py-3 text-center">
                            <p className="small mb-2" style={{ color: 'var(--as-muted)' }}>
                                No settings match &ldquo;{searchQuery}&rdquo;.
                            </p>
                            <button
                                type="button"
                                className={`btn btn-sm btn-outline-secondary rounded-pill px-3 ${styles.actionBtn}`}
                                onClick={() => setSearchQuery('')}
                            >
                                Clear Filter
                            </button>
                        </div>
                    ) : (
                        <>
                            {/* Mobile Horizontal Scrollable Pill Navigation */}
                            <div className={styles.mobileNavStrip} role="tablist" aria-label="Account settings sections">
                                {filteredTabs.map((tab) => {
                                    const isActive = activeTab === tab.id;
                                    const isDirty = Boolean(dirtyTabs[tab.id]);
                                    return (
                                        <button
                                            key={tab.id}
                                            id={`mobile-pill-${tab.id}`}
                                            type="button"
                                            role="tab"
                                            aria-selected={isActive}
                                            onClick={() => handleSelectTab(tab.id)}
                                            className={`${styles.mobilePillBtn} ${
                                                isActive ? styles.mobilePillBtnActive : ''
                                            }`}
                                        >
                                            <i className={`bi ${tab.icon}`} aria-hidden="true" />
                                            <span>{tab.label}</span>
                                            {isDirty && <span className={styles.dirtyDot} title="Unsaved changes" />}
                                        </button>
                                    );
                                })}
                            </div>

                            {/* Desktop Vertical Sidebar Navigation */}
                            <div className={styles.desktopOnlyNav}>
                                {accountGroupTabs.length > 0 && (
                                    <div>
                                        <div className={styles.navGroupHeading}>Account &amp; Security</div>
                                        <div
                                            className={styles.desktopNavList}
                                            id="v-pills-account-tab"
                                            role="tablist"
                                            aria-orientation="vertical"
                                        >
                                            {accountGroupTabs.map((tab) => {
                                                const isActive = activeTab === tab.id;
                                                const isDirty = Boolean(dirtyTabs[tab.id]);
                                                return (
                                                    <button
                                                        key={tab.id}
                                                        id={`v-pills-${tab.id}-tab`}
                                                        type="button"
                                                        role="tab"
                                                        data-bs-target={`#v-pills-${tab.id}`}
                                                        aria-controls={`v-pills-${tab.id}`}
                                                        aria-selected={isActive}
                                                        onClick={() => handleSelectTab(tab.id)}
                                                        className={`${styles.navTabBtn} ${
                                                            isActive ? styles.navTabBtnActive : ''
                                                        }`}
                                                    >
                                                        <span className="d-inline-flex align-items-center gap-2">
                                                            <i className={`bi ${tab.icon}`} aria-hidden="true" />
                                                            <span>{tab.label}</span>
                                                        </span>
                                                        {isDirty && (
                                                            <span
                                                                className={styles.dirtyDot}
                                                                title="Unsaved changes"
                                                                aria-label="Unsaved changes"
                                                            />
                                                        )}
                                                    </button>
                                                );
                                            })}
                                        </div>
                                    </div>
                                )}

                                {familyGroupTabs.length > 0 && (
                                    <div className={accountGroupTabs.length > 0 ? styles.navGroupDivider : ''}>
                                        <div className={styles.navGroupHeading} id="familyConnectionsHeading">
                                            Family Connections
                                        </div>
                                        <div
                                            className={styles.desktopNavList}
                                            id="v-pills-family-tab"
                                            role="tablist"
                                            aria-orientation="vertical"
                                        >
                                            {familyGroupTabs.map((tab) => {
                                                const isActive = activeTab === tab.id;
                                                const isDirty = Boolean(dirtyTabs[tab.id]);
                                                return (
                                                    <button
                                                        key={tab.id}
                                                        id={`v-pills-${tab.id}-tab`}
                                                        type="button"
                                                        role="tab"
                                                        data-bs-target={`#v-pills-${tab.id}`}
                                                        aria-controls={`v-pills-${tab.id}`}
                                                        aria-selected={isActive}
                                                        onClick={() => handleSelectTab(tab.id)}
                                                        className={`${styles.navTabBtn} ${
                                                            isActive ? styles.navTabBtnActive : ''
                                                        }`}
                                                    >
                                                        <span className="d-inline-flex align-items-center gap-2">
                                                            <i className={`bi ${tab.icon}`} aria-hidden="true" />
                                                            <span>{tab.label}</span>
                                                        </span>
                                                        {isDirty && (
                                                            <span
                                                                className={styles.dirtyDot}
                                                                title="Unsaved changes"
                                                                aria-label="Unsaved changes"
                                                            />
                                                        )}
                                                    </button>
                                                );
                                            })}
                                        </div>
                                    </div>
                                )}
                            </div>
                        </>
                    )}
                </aside>

                {/* Main Content Panel */}
                <section className={styles.contentCard} aria-label={currentTabObj.label}>
                    {pendingTabSwitch && (
                        <div className={styles.dirtyBanner} role="alert" data-testid="unsaved-changes-banner">
                            <div className="small fw-semibold">
                                <i className="bi bi-exclamation-triangle-fill me-2" aria-hidden="true" />
                                You have unsaved changes in <strong>{currentTabObj.label}</strong>. Discard them and switch?
                            </div>
                            <div className="d-flex flex-wrap gap-2">
                                <button
                                    type="button"
                                    className={`btn btn-sm btn-outline-secondary rounded-pill px-3 ${styles.actionBtn}`}
                                    onClick={() => setPendingTabSwitch(null)}
                                >
                                    Stay Here
                                </button>
                                <button
                                    type="button"
                                    className={`btn btn-sm btn-warning rounded-pill px-3 fw-semibold ${styles.actionBtn}`}
                                    onClick={() => {
                                        handleDirtyChange(activeTab, false);
                                        performTabSwitch(pendingTabSwitch);
                                    }}
                                >
                                    Discard &amp; Switch
                                </button>
                            </div>
                        </div>
                    )}

                    <div
                        className="tab-pane show active"
                        id={`v-pills-${activeTab}`}
                        role="tabpanel"
                        aria-labelledby={`v-pills-${activeTab}-tab`}
                    >
                        {activeTab === 'profile' && (
                            <ProfileTab
                                accountData={accountData}
                                csrfToken={csrfToken}
                                bootstrap={bootstrap}
                                onSavedAccountData={handleSavedAccountData}
                                onDirtyChange={handleDirtyChange}
                            />
                        )}
                        {activeTab === 'password' && (
                            <PasswordTab
                                csrfToken={csrfToken}
                                bootstrap={bootstrap}
                                onDirtyChange={handleDirtyChange}
                            />
                        )}
                        {activeTab === 'preferences' && (
                            <PreferencesTab
                                accountData={accountData}
                                csrfToken={csrfToken}
                                bootstrap={bootstrap}
                                onSavedAccountData={handleSavedAccountData}
                                onDirtyChange={handleDirtyChange}
                            />
                        )}
                        {activeTab === 'privacy' && (
                            <PrivacyTab
                                accountData={accountData}
                                passkeys={passkeys}
                                restrictionDirectory={restrictionDirectory}
                                csrfToken={csrfToken}
                                bootstrap={bootstrap}
                                onSavedAccountData={handleSavedAccountData}
                                onRestrictionsChange={setRestrictionDirectory}
                                onRefreshState={handleRefreshState}
                                onJumpToTarget={(elId) => applyHighlightToTarget(elId)}
                                onDirtyChange={handleDirtyChange}
                            />
                        )}
                        {activeTab === 'parents' && (
                            <ParentsTab
                                accountData={accountData}
                                csrfToken={csrfToken}
                                bootstrap={bootstrap}
                                onSavedAccountData={handleSavedAccountData}
                                onDirtyChange={handleDirtyChange}
                            />
                        )}
                        {activeTab === 'children' && (
                            <ChildrenTab
                                accountData={accountData}
                                csrfToken={csrfToken}
                                bootstrap={bootstrap}
                                onDirtyChange={handleDirtyChange}
                            />
                        )}
                        {activeTab === 'siblings' && (
                            <SiblingsTab
                                accountData={accountData}
                                csrfToken={csrfToken}
                                bootstrap={bootstrap}
                                onDirtyChange={handleDirtyChange}
                            />
                        )}
                        {activeTab === 'marital' && (
                            <MaritalTab
                                accountData={accountData}
                                maxWeddingDate={maxWeddingDate}
                                csrfToken={csrfToken}
                                bootstrap={bootstrap}
                                onSavedAccountData={handleSavedAccountData}
                                onDirtyChange={handleDirtyChange}
                            />
                        )}
                        {activeTab === 'familycode' && (
                            <FamilyCodeTab
                                accountData={accountData}
                                pendingFamilyRequest={pendingFamilyRequest}
                                birthFamily={birthFamily}
                                codeRename={codeRename}
                                csrfToken={csrfToken}
                                bootstrap={bootstrap}
                                onRefreshState={handleRefreshState}
                                onSavedAccountData={handleSavedAccountData}
                            />
                        )}
                    </div>
                </section>
            </div>
        </div>
    );
}
