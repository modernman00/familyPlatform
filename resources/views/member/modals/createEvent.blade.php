@php
    $token = $_SESSION['token'] ?? '';
    $_SESSION['CREATE_EVENT_ID'] = $data['id'];
@endphp

<!-- Create Event Modal Styles (Scoped Heirloom Craft) -->
<style>
    #createEventModal .ce-dialog {
        max-width: 660px;
        width: 100%;
    }

    @media (max-width: 575.98px) {
        #createEventModal .ce-dialog {
            margin: 0.5rem;
            max-width: calc(100% - 1rem);
        }
    }

    #createEventModal .modal-content.ce-content {
        padding: 0 !important;
        border-radius: 22px;
        overflow: hidden;
        border: 1px solid #e6dfd5;
        background-color: #ffffff;
        color: var(--text-main, #0f172a);
        box-shadow: 0 24px 60px rgba(28, 20, 18, 0.22);
        max-height: calc(100dvh - 1.5rem);
        display: flex;
        flex-direction: column;
    }

    #createEventModal .ce-accent-bar {
        height: 4px;
        width: 100%;
        flex-shrink: 0;
        background: linear-gradient(90deg, #a31621 0%, #e11d48 52%, #f59e0b 100%);
    }

    #createEventModal .modal-header.ce-header {
        padding: 18px 24px 16px;
        border-bottom: 1px solid #efe8de;
        background-color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: var(--space-3, 12px);
        flex-shrink: 0;
    }

    #createEventModal .ce-header__lead {
        display: flex;
        align-items: center;
        gap: var(--space-3, 12px);
        min-width: 0;
        flex: 1 1 auto;
    }

    #createEventModal .ce-header__icon {
        width: 42px;
        height: 42px;
        border-radius: var(--radius-md, 12px);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        background: linear-gradient(145deg, #f43f5e 0%, #a31621 55%, #7f1d1d 100%);
        box-shadow:
            0 2.5px 0 #6e0d15,
            0 6px 14px rgba(163, 22, 33, 0.26),
            inset 0 1.5px 2px rgba(255, 255, 255, 0.55),
            inset 0 -2px 3px rgba(91, 12, 20, 0.45);
    }

    #createEventModal .ce-header__copy {
        min-width: 0;
    }

    #createEventModal .modal-title.ce-title {
        font-family: var(--font-family, 'Outfit', -apple-system, BlinkMacSystemFont, sans-serif);
        font-weight: 800;
        font-size: var(--text-lg, 1.25rem);
        line-height: 1.25;
        letter-spacing: -0.015em;
        color: var(--text-main, #0f172a);
        margin: 0;
    }

    #createEventModal .ce-subtitle {
        font-size: var(--text-sm, 0.875rem);
        line-height: 1.35;
        color: var(--text-muted-aa, #475569);
        margin: 2px 0 0;
    }

    #createEventModal .ce-close-btn {
        width: 44px;
        height: 44px;
        min-width: 44px;
        min-height: 44px;
        border-radius: 50%;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        color: #334155;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        flex-shrink: 0;
        cursor: pointer;
        transition:
            background-color var(--motion-fast, 150ms) ease,
            border-color var(--motion-fast, 150ms) ease,
            transform var(--motion-fast, 150ms) var(--ease, cubic-bezier(.16, 1, .3, 1));
    }

    #createEventModal .ce-close-btn:active {
        transform: scale(0.98);
    }

    #createEventModal .ce-close-btn:focus-visible {
        outline: 2px solid var(--brand-primary, #a31621);
        outline-offset: 2px;
    }

    #createEventModal .ce-form {
        display: flex;
        flex-direction: column;
        flex: 1 1 auto;
        min-height: 0;
        overflow: hidden;
    }

    #createEventModal .modal-body.ce-body {
        padding: 20px 24px 24px;
        background-color: #ffffff;
        flex: 1 1 auto;
        overflow-y: auto;
        overflow-x: hidden;
    }

    @media (max-width: 575.98px) {
        #createEventModal .modal-header.ce-header {
            padding: 16px;
        }
        #createEventModal .modal-body.ce-body {
            padding: 16px;
        }
    }

    #createEventModal .ce-edit-notice {
        display: inline-flex;
        align-items: center;
        gap: var(--space-2, 8px);
        padding: 6px 12px;
        border-radius: 9999px;
        background: #fef3c7;
        color: #92400e;
        border: 1px solid #fde68a;
        font-size: var(--text-xs, 0.75rem);
        font-weight: 600;
        margin-bottom: 14px;
    }

    #createEventModal .ce-error-banner {
        color: #b91c1c;
        font-size: var(--text-sm, 0.875rem);
        font-weight: 600;
    }

    #createEventModal .ce-section {
        display: flex;
        flex-direction: column;
        gap: var(--space-4, 16px);
    }

    #createEventModal .ce-section--divided {
        margin-top: 22px;
        padding-top: 20px;
        border-top: 1px solid #e2e8f0;
    }

    #createEventModal .ce-section__heading {
        font-size: var(--text-base, 1rem);
        font-weight: 700;
        color: var(--text-main, #0f172a);
        letter-spacing: -0.01em;
        margin: 0;
    }

    /* Neutralise main.css/form.scss global screaming uppercase placeholder & tiny italic .small rules */
    #createEventModal .form-label {
        font-size: var(--text-sm, 0.875rem);
        font-weight: 600;
        text-transform: none;
        letter-spacing: normal;
        color: #1e293b;
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: var(--space-2, 8px);
    }

    #createEventModal .ce-req {
        color: #a31621;
        margin-left: 2px;
        font-weight: 700;
    }

    #createEventModal .ce-optional-tag {
        font-size: var(--text-xs, 0.75rem);
        font-weight: 500;
        color: var(--text-muted-aa, #475569);
    }

    #createEventModal .small,
    #createEventModal small:not(.ce-field-error) {
        font-style: normal !important;
        font-size: var(--text-xs, 0.75rem);
        color: var(--text-muted-aa, #475569);
    }

    #createEventModal .ce-field-error {
        display: block;
        margin-top: 4px;
        font-size: var(--text-xs, 0.75rem);
        font-style: normal !important;
        font-weight: 600;
        color: #b91c1c;
    }

    #createEventModal .form-control,
    #createEventModal .form-select {
        min-height: 46px;
        border-radius: var(--radius-md, 12px);
        padding: 10px 14px;
        font-size: 0.9375rem;
        line-height: 1.4;
        border: 1.5px solid #cbd5e1;
        background-color: #ffffff;
        color: #0f172a;
        transition:
            border-color var(--motion-fast, 150ms) ease,
            box-shadow var(--motion-fast, 150ms) ease,
            background-color var(--motion-fast, 150ms) ease;
    }

    #createEventModal .form-control--lg {
        min-height: 48px;
        font-size: 1rem;
    }

    #createEventModal textarea.form-control {
        min-height: 88px;
        resize: vertical;
    }

    #createEventModal select.form-select {
        -webkit-appearance: none !important;
        -moz-appearance: none !important;
        appearance: none !important;
        white-space: nowrap;
        padding-right: 2.5rem !important;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23475569' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 0.875rem center;
        background-size: 14px 12px;
        text-overflow: ellipsis;
    }

    #createEventModal select.form-select::picker-icon {
        display: none;
    }

    #createEventModal .form-select:disabled {
        background-color: #f1f5f9;
        color: #64748b;
        opacity: 0.78;
        cursor: not-allowed;
    }

    #createEventModal .form-control:focus,
    #createEventModal .form-select:focus {
        border-color: var(--brand-primary, #a31621);
        box-shadow: 0 0 0 3px rgba(163, 22, 33, 0.14);
        background-color: #ffffff;
        color: #0f172a;
        outline: none;
    }

    #createEventModal .form-control::-webkit-input-placeholder,
    #createEventModal input::-webkit-input-placeholder,
    #createEventModal textarea::-webkit-input-placeholder {
        text-transform: none !important;
        font-size: 0.9rem !important;
        font-style: normal !important;
        letter-spacing: normal !important;
        color: #64748b !important;
        opacity: 0.88;
    }

    #createEventModal .form-control::-moz-placeholder,
    #createEventModal input::-moz-placeholder,
    #createEventModal textarea::-moz-placeholder {
        text-transform: none !important;
        font-size: 0.9rem !important;
        font-style: normal !important;
        letter-spacing: normal !important;
        color: #64748b !important;
        opacity: 0.88;
    }

    #createEventModal .form-control::placeholder,
    #createEventModal input::placeholder,
    #createEventModal textarea::placeholder {
        text-transform: none !important;
        font-size: 0.9rem !important;
        font-style: normal !important;
        letter-spacing: normal !important;
        color: #64748b !important;
        opacity: 0.88;
    }

    #createEventModal input[type="date"]::-webkit-calendar-picker-indicator {
        opacity: 0.72;
        cursor: pointer;
    }

    /* Leading icon input wrapper */
    #createEventModal .ce-input-icon-wrap {
        position: relative;
        width: 100%;
    }

    #createEventModal .ce-input-icon-wrap__icon {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #a31621;
        font-size: 1rem;
        pointer-events: none;
        z-index: 2;
    }

    #createEventModal .ce-input-icon-wrap .form-control {
        padding-left: 2.5rem;
    }

    /* Audience & Proximity Panel */
    #createEventModal .ce-panel {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: var(--radius-lg, 16px);
        padding: 16px;
        display: flex;
        flex-direction: column;
        gap: var(--space-3, 12px);
    }

    #createEventModal .ce-panel__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: var(--space-2, 8px);
        flex-wrap: wrap;
    }

    #createEventModal .ce-panel__title {
        font-size: var(--text-sm, 0.875rem);
        font-weight: 700;
        color: var(--text-main, #0f172a);
        display: inline-flex;
        align-items: center;
        gap: var(--space-2, 8px);
    }

    #createEventModal .ce-helper-note {
        display: flex;
        align-items: flex-start;
        gap: 6px;
        font-size: 0.8125rem;
        line-height: 1.45;
        color: var(--text-muted-aa, #475569);
    }

    #createEventModal .ce-helper-note i {
        margin-top: 2px;
        flex-shrink: 0;
    }

    /* Collapsible Outing Inspiration Card */
    #createEventModal .ce-scout-card {
        border-radius: var(--radius-lg, 16px);
        background: linear-gradient(135deg, #fffdf9 0%, #fcf8ee 100%);
        border: 1.5px solid rgba(217, 119, 6, 0.28);
        overflow: hidden;
    }

    #createEventModal .ce-scout-toggle {
        width: 100%;
        min-height: 60px;
        padding: 14px 16px;
        background: transparent;
        border: none;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: var(--space-3, 12px);
        text-align: left;
        cursor: pointer;
        color: var(--text-main, #0f172a);
        transition: background-color var(--motion-fast, 150ms) ease;
    }

    #createEventModal .ce-scout-toggle:focus-visible {
        outline: 2px solid var(--brand-primary, #a31621);
        outline-offset: -2px;
    }

    #createEventModal .ce-scout-toggle__lead {
        display: flex;
        align-items: center;
        gap: var(--space-3, 12px);
        min-width: 0;
    }

    #createEventModal .ce-scout-toggle__badge {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        background: linear-gradient(145deg, #fcd34d 0%, #f59e0b 55%, #b45309 100%);
        color: #ffffff;
        font-size: 1rem;
        box-shadow: 0 2px 6px rgba(217, 119, 6, 0.26);
    }

    #createEventModal .ce-scout-toggle__title {
        display: block;
        font-size: 0.9375rem;
        font-weight: 700;
        color: var(--text-main, #0f172a);
        line-height: 1.3;
    }

    #createEventModal .ce-scout-toggle__sub {
        display: block;
        font-size: 0.8125rem;
        color: var(--text-muted-aa, #475569);
        line-height: 1.35;
        margin-top: 2px;
    }

    #createEventModal .ce-scout-toggle__action {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: var(--text-sm, 0.875rem);
        font-weight: 700;
        color: #92400e;
        white-space: nowrap;
        flex-shrink: 0;
    }

    #createEventModal .ce-scout-toggle__chevron {
        transition: transform var(--motion-base, 250ms) var(--ease, cubic-bezier(.16, 1, .3, 1));
    }

    #createEventModal .ce-scout-toggle__chevron.is-open {
        transform: rotate(180deg);
    }

    #createEventModal .ce-scout-body {
        padding: 4px 16px 16px;
        border-top: 1px solid rgba(217, 119, 6, 0.16);
    }

    #createEventModal .scout-pill {
        min-height: var(--tap-min, 44px);
        padding: var(--space-2, 8px) var(--space-3, 12px);
        border-radius: 9999px;
        font-size: 0.84rem;
        font-weight: 600;
        white-space: nowrap;
        flex-shrink: 0;
        border: 1.5px solid #cbd5e1;
        background: #ffffff;
        color: #334155;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition:
            transform var(--motion-fast, 150ms) ease,
            border-color var(--motion-fast, 150ms) ease,
            background-color var(--motion-fast, 150ms) ease;
    }

    #createEventModal .scout-pill:active {
        transform: scale(0.98);
    }

    #createEventModal .scout-pill.active,
    #createEventModal .scout-pill.btn-danger {
        background: linear-gradient(135deg, #a31621 0%, #8a1019 100%) !important;
        border-color: #8a1019 !important;
        color: #ffffff !important;
        box-shadow: 0 4px 12px rgba(163, 22, 33, 0.24);
    }

    #createEventModal .ce-scout-search {
        display: flex;
        align-items: stretch;
        gap: var(--space-2, 8px);
    }

    @media (max-width: 575.98px) {
        #createEventModal .ce-scout-search {
            flex-direction: column;
        }
    }

    #createEventModal .ce-scout-btn {
        min-height: 46px;
        padding: 10px 18px;
        border-radius: var(--radius-md, 12px);
        background: linear-gradient(135deg, #a31621 0%, #8a1019 100%);
        color: #ffffff !important;
        border: none;
        font-size: var(--text-sm, 0.875rem);
        font-weight: 700;
        white-space: nowrap;
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: var(--space-2, 8px);
        box-shadow: 0 4px 12px rgba(163, 22, 33, 0.24);
        transition:
            transform var(--motion-fast, 150ms) ease,
            filter var(--motion-fast, 150ms) ease;
    }

    #createEventModal .ce-scout-btn:active {
        transform: scale(0.98);
    }

    /* PartyPlatform Studio Switch Card */
    #createEventModal .ce-studio-card {
        padding: 16px;
        border-radius: var(--radius-lg, 16px);
        background: linear-gradient(135deg, rgba(163, 22, 33, 0.05) 0%, rgba(245, 158, 11, 0.07) 100%);
        border: 1px solid rgba(163, 22, 33, 0.2);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: var(--space-4, 16px);
    }

    #createEventModal .ce-studio-card__lead {
        display: flex;
        align-items: flex-start;
        gap: var(--space-3, 12px);
        min-width: 0;
    }

    #createEventModal .ce-studio-card__badge {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        background: linear-gradient(145deg, #fb7185 0%, #a31621 60%, #7f1d1d 100%);
        color: #fef08a;
        font-size: 1rem;
        box-shadow: 0 2px 6px rgba(163, 22, 33, 0.24);
    }

    #createEventModal .ce-studio-card__title {
        font-size: 0.9375rem;
        font-weight: 700;
        color: var(--text-main, #0f172a);
        cursor: pointer;
        margin: 0;
        line-height: 1.3;
        display: block;
    }

    #createEventModal .ce-studio-card__desc {
        font-size: 0.8125rem;
        line-height: 1.45;
        color: var(--text-muted-aa, #475569);
        margin: 4px 0 0;
    }

    #createEventModal .ce-switch-wrap {
        min-width: 48px;
        min-height: 44px;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        flex-shrink: 0;
    }

    #createEventModal .ce-switch-wrap .form-check-input {
        width: 2.75rem;
        height: 1.5rem;
        margin: 0;
        cursor: pointer;
    }

    #createEventModal .ce-switch-wrap .form-check-input:checked {
        background-color: #a31621;
        border-color: #a31621;
    }

    /* Pinned Footer */
    #createEventModal .modal-footer.ce-footer {
        border-top: 1px solid #efe8de;
        padding: 14px 24px calc(14px + env(safe-area-inset-bottom, 0px));
        background-color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: var(--space-3, 12px);
        flex-shrink: 0;
        margin: 0;
    }

    @media (max-width: 575.98px) {
        #createEventModal .modal-footer.ce-footer {
            padding: 12px 16px calc(12px + env(safe-area-inset-bottom, 0px));
        }
    }

    #createEventModal .ce-btn-cancel {
        min-height: 46px;
        border-radius: 9999px;
        padding: 10px 20px;
        font-size: 0.9375rem;
        font-weight: 600;
        color: #334155;
        border: 1.5px solid #cbd5e1;
        background-color: transparent;
        white-space: nowrap;
        flex: 1;
        transition: background-color var(--motion-fast, 150ms) ease, transform var(--motion-fast, 150ms) ease;
    }

    #createEventModal .ce-btn-cancel:active {
        transform: scale(0.98);
    }

    #createEventModal .ce-btn-save {
        min-height: 46px;
        border-radius: 9999px;
        padding: 10px 24px;
        font-size: 0.9375rem;
        font-weight: 700;
        color: #ffffff !important;
        background: linear-gradient(135deg, #a31621 0%, #8a1019 100%);
        border: none;
        white-space: nowrap;
        flex: 1.4;
        box-shadow: 0 4px 14px rgba(163, 22, 33, 0.28);
        transition: filter var(--motion-fast, 150ms) ease, transform var(--motion-fast, 150ms) ease;
    }

    #createEventModal .ce-btn-save:active {
        transform: scale(0.98);
    }

    #createEventModal .ce-btn-cancel:focus-visible,
    #createEventModal .ce-btn-save:focus-visible,
    #createEventModal .ce-scout-btn:focus-visible,
    #createEventModal .scout-pill:focus-visible {
        outline: 2px solid var(--brand-primary, #a31621);
        outline-offset: 2px;
    }

    @media (hover: hover) {
        #createEventModal .ce-close-btn:hover {
            background-color: #f1f5f9;
            border-color: #cbd5e1;
            color: #0f172a;
        }
        #createEventModal .ce-scout-toggle:hover {
            background-color: rgba(245, 158, 11, 0.06);
        }
        #createEventModal .scout-pill:not(.active):not(.btn-danger):hover {
            border-color: #a31621;
            color: #8a1019;
            background-color: #fff1f2;
        }
        #createEventModal .ce-scout-btn:hover,
        #createEventModal .ce-btn-save:hover {
            filter: brightness(1.07);
        }
        #createEventModal .ce-btn-cancel:hover {
            background-color: #f1f5f9;
            color: #0f172a;
        }
    }

    /* Full 5-Selector Dark Mode Parity (overrides standards-v21 !important safely via #createEventModal specificity) */
    body.dark-mode #createEventModal .modal-content.ce-content,
    body.dark-theme #createEventModal .modal-content.ce-content,
    [data-bs-theme="dark"] #createEventModal .modal-content.ce-content,
    html.dark #createEventModal .modal-content.ce-content,
    html.dark-mode #createEventModal .modal-content.ce-content {
        background-color: #141418 !important;
        color: #f8fafc !important;
        border-color: rgba(255, 255, 255, 0.12) !important;
    }

    body.dark-mode #createEventModal .modal-header.ce-header,
    body.dark-theme #createEventModal .modal-header.ce-header,
    [data-bs-theme="dark"] #createEventModal .modal-header.ce-header,
    html.dark #createEventModal .modal-header.ce-header,
    html.dark-mode #createEventModal .modal-header.ce-header,
    body.dark-mode #createEventModal .modal-footer.ce-footer,
    body.dark-theme #createEventModal .modal-footer.ce-footer,
    [data-bs-theme="dark"] #createEventModal .modal-footer.ce-footer,
    html.dark #createEventModal .modal-footer.ce-footer,
    html.dark-mode #createEventModal .modal-footer.ce-footer {
        background-color: #18181f !important;
        color: #f8fafc !important;
        border-color: rgba(255, 255, 255, 0.1) !important;
    }

    body.dark-mode #createEventModal .modal-body.ce-body,
    body.dark-theme #createEventModal .modal-body.ce-body,
    [data-bs-theme="dark"] #createEventModal .modal-body.ce-body,
    html.dark #createEventModal .modal-body.ce-body,
    html.dark-mode #createEventModal .modal-body.ce-body {
        background-color: #141418 !important;
        color: #f8fafc !important;
    }

    body.dark-mode #createEventModal .modal-title.ce-title,
    body.dark-theme #createEventModal .modal-title.ce-title,
    [data-bs-theme="dark"] #createEventModal .modal-title.ce-title,
    html.dark #createEventModal .modal-title.ce-title,
    html.dark-mode #createEventModal .modal-title.ce-title,
    body.dark-mode #createEventModal .ce-section__heading,
    body.dark-theme #createEventModal .ce-section__heading,
    [data-bs-theme="dark"] #createEventModal .ce-section__heading,
    html.dark #createEventModal .ce-section__heading,
    html.dark-mode #createEventModal .ce-section__heading,
    body.dark-mode #createEventModal .ce-panel__title,
    body.dark-theme #createEventModal .ce-panel__title,
    [data-bs-theme="dark"] #createEventModal .ce-panel__title,
    html.dark #createEventModal .ce-panel__title,
    html.dark-mode #createEventModal .ce-panel__title,
    body.dark-mode #createEventModal .ce-scout-toggle__title,
    body.dark-theme #createEventModal .ce-scout-toggle__title,
    [data-bs-theme="dark"] #createEventModal .ce-scout-toggle__title,
    html.dark #createEventModal .ce-scout-toggle__title,
    html.dark-mode #createEventModal .ce-scout-toggle__title,
    body.dark-mode #createEventModal .ce-studio-card__title,
    body.dark-theme #createEventModal .ce-studio-card__title,
    [data-bs-theme="dark"] #createEventModal .ce-studio-card__title,
    html.dark #createEventModal .ce-studio-card__title,
    html.dark-mode #createEventModal .ce-studio-card__title {
        color: #f8fafc !important;
    }

    body.dark-mode #createEventModal .ce-subtitle,
    body.dark-theme #createEventModal .ce-subtitle,
    [data-bs-theme="dark"] #createEventModal .ce-subtitle,
    html.dark #createEventModal .ce-subtitle,
    html.dark-mode #createEventModal .ce-subtitle,
    body.dark-mode #createEventModal .ce-optional-tag,
    body.dark-theme #createEventModal .ce-optional-tag,
    [data-bs-theme="dark"] #createEventModal .ce-optional-tag,
    html.dark #createEventModal .ce-optional-tag,
    html.dark-mode #createEventModal .ce-optional-tag,
    body.dark-mode #createEventModal .ce-helper-note,
    body.dark-theme #createEventModal .ce-helper-note,
    [data-bs-theme="dark"] #createEventModal .ce-helper-note,
    html.dark #createEventModal .ce-helper-note,
    html.dark-mode #createEventModal .ce-helper-note,
    body.dark-mode #createEventModal .ce-scout-toggle__sub,
    body.dark-theme #createEventModal .ce-scout-toggle__sub,
    [data-bs-theme="dark"] #createEventModal .ce-scout-toggle__sub,
    html.dark #createEventModal .ce-scout-toggle__sub,
    html.dark-mode #createEventModal .ce-scout-toggle__sub,
    body.dark-mode #createEventModal .ce-studio-card__desc,
    body.dark-theme #createEventModal .ce-studio-card__desc,
    [data-bs-theme="dark"] #createEventModal .ce-studio-card__desc,
    html.dark #createEventModal .ce-studio-card__desc,
    html.dark-mode #createEventModal .ce-studio-card__desc {
        color: #cbd5e1 !important;
    }

    body.dark-mode #createEventModal .form-label,
    body.dark-theme #createEventModal .form-label,
    [data-bs-theme="dark"] #createEventModal .form-label,
    html.dark #createEventModal .form-label,
    html.dark-mode #createEventModal .form-label {
        color: #e2e8f0 !important;
    }

    body.dark-mode #createEventModal .ce-req,
    body.dark-theme #createEventModal .ce-req,
    [data-bs-theme="dark"] #createEventModal .ce-req,
    html.dark #createEventModal .ce-req,
    html.dark-mode #createEventModal .ce-req {
        color: #fb7185 !important;
    }

    body.dark-mode #createEventModal .ce-close-btn,
    body.dark-theme #createEventModal .ce-close-btn,
    [data-bs-theme="dark"] #createEventModal .ce-close-btn,
    html.dark #createEventModal .ce-close-btn,
    html.dark-mode #createEventModal .ce-close-btn {
        background: #1e293b !important;
        border-color: #334155 !important;
        color: #e2e8f0 !important;
    }

    body.dark-mode #createEventModal .ce-section--divided,
    body.dark-theme #createEventModal .ce-section--divided,
    [data-bs-theme="dark"] #createEventModal .ce-section--divided,
    html.dark #createEventModal .ce-section--divided,
    html.dark-mode #createEventModal .ce-section--divided {
        border-top-color: rgba(255, 255, 255, 0.1) !important;
    }

    body.dark-mode #createEventModal .form-control,
    body.dark-theme #createEventModal .form-control,
    [data-bs-theme="dark"] #createEventModal .form-control,
    html.dark #createEventModal .form-control,
    html.dark-mode #createEventModal .form-control,
    body.dark-mode #createEventModal .form-select,
    body.dark-theme #createEventModal .form-select,
    [data-bs-theme="dark"] #createEventModal .form-select,
    html.dark #createEventModal .form-select,
    html.dark-mode #createEventModal .form-select {
        background-color: #0f172a !important;
        border-color: #334155 !important;
        color: #f8fafc !important;
    }

    body.dark-mode #createEventModal select.form-select,
    body.dark-theme #createEventModal select.form-select,
    [data-bs-theme="dark"] #createEventModal select.form-select,
    html.dark #createEventModal select.form-select,
    html.dark-mode #createEventModal select.form-select {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23cbd5e1' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e") !important;
    }

    body.dark-mode #createEventModal .form-select:disabled,
    body.dark-theme #createEventModal .form-select:disabled,
    [data-bs-theme="dark"] #createEventModal .form-select:disabled,
    html.dark #createEventModal .form-select:disabled,
    html.dark-mode #createEventModal .form-select:disabled {
        background-color: #161e2e !important;
        color: #94a3b8 !important;
        opacity: 0.72;
    }

    body.dark-mode #createEventModal .form-control::placeholder,
    body.dark-theme #createEventModal .form-control::placeholder,
    [data-bs-theme="dark"] #createEventModal .form-control::placeholder,
    html.dark #createEventModal .form-control::placeholder,
    html.dark-mode #createEventModal .form-control::placeholder {
        color: #94a3b8 !important;
    }

    body.dark-mode #createEventModal input[type="date"]::-webkit-calendar-picker-indicator,
    body.dark-theme #createEventModal input[type="date"]::-webkit-calendar-picker-indicator,
    [data-bs-theme="dark"] #createEventModal input[type="date"]::-webkit-calendar-picker-indicator,
    html.dark #createEventModal input[type="date"]::-webkit-calendar-picker-indicator,
    html.dark-mode #createEventModal input[type="date"]::-webkit-calendar-picker-indicator {
        filter: invert(1);
    }

    body.dark-mode #createEventModal .ce-input-icon-wrap__icon,
    body.dark-theme #createEventModal .ce-input-icon-wrap__icon,
    [data-bs-theme="dark"] #createEventModal .ce-input-icon-wrap__icon,
    html.dark #createEventModal .ce-input-icon-wrap__icon,
    html.dark-mode #createEventModal .ce-input-icon-wrap__icon {
        color: #fda4af !important;
    }

    body.dark-mode #createEventModal .ce-panel,
    body.dark-theme #createEventModal .ce-panel,
    [data-bs-theme="dark"] #createEventModal .ce-panel,
    html.dark #createEventModal .ce-panel,
    html.dark-mode #createEventModal .ce-panel {
        background: #1c212e !important;
        border-color: rgba(255, 255, 255, 0.1) !important;
    }

    body.dark-mode #createEventModal #proximityBadgePreview.bg-light,
    body.dark-theme #createEventModal #proximityBadgePreview.bg-light,
    [data-bs-theme="dark"] #createEventModal #proximityBadgePreview.bg-light,
    html.dark #createEventModal #proximityBadgePreview.bg-light,
    html.dark-mode #createEventModal #proximityBadgePreview.bg-light {
        background: rgba(148, 163, 184, 0.18) !important;
        color: #e2e8f0 !important;
        border-color: rgba(148, 163, 184, 0.3) !important;
    }

    body.dark-mode #createEventModal .ce-scout-card,
    body.dark-theme #createEventModal .ce-scout-card,
    [data-bs-theme="dark"] #createEventModal .ce-scout-card,
    html.dark #createEventModal .ce-scout-card,
    html.dark-mode #createEventModal .ce-scout-card {
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.08) 0%, #18181f 100%) !important;
        border-color: rgba(251, 191, 36, 0.28) !important;
    }

    body.dark-mode #createEventModal .ce-scout-toggle__action,
    body.dark-theme #createEventModal .ce-scout-toggle__action,
    [data-bs-theme="dark"] #createEventModal .ce-scout-toggle__action,
    html.dark #createEventModal .ce-scout-toggle__action,
    html.dark-mode #createEventModal .ce-scout-toggle__action {
        color: #fde68a !important;
    }

    body.dark-mode #createEventModal .ce-scout-body,
    body.dark-theme #createEventModal .ce-scout-body,
    [data-bs-theme="dark"] #createEventModal .ce-scout-body,
    html.dark #createEventModal .ce-scout-body,
    html.dark-mode #createEventModal .ce-scout-body {
        border-top-color: rgba(251, 191, 36, 0.18) !important;
    }

    body.dark-mode #createEventModal .scout-pill:not(.active):not(.btn-danger),
    body.dark-theme #createEventModal .scout-pill:not(.active):not(.btn-danger),
    [data-bs-theme="dark"] #createEventModal .scout-pill:not(.active):not(.btn-danger),
    html.dark #createEventModal .scout-pill:not(.active):not(.btn-danger),
    html.dark-mode #createEventModal .scout-pill:not(.active):not(.btn-danger) {
        background: #0f172a !important;
        color: #e2e8f0 !important;
        border-color: #334155 !important;
    }

    body.dark-mode #createEventModal #scoutCardsContainer > div,
    body.dark-theme #createEventModal #scoutCardsContainer > div,
    [data-bs-theme="dark"] #createEventModal #scoutCardsContainer > div,
    html.dark #createEventModal #scoutCardsContainer > div,
    html.dark-mode #createEventModal #scoutCardsContainer > div {
        background-color: #161b26 !important;
        border-color: rgba(255, 255, 255, 0.12) !important;
        color: #f8fafc !important;
    }

    body.dark-mode #createEventModal #scoutCardsContainer .text-dark,
    body.dark-theme #createEventModal #scoutCardsContainer .text-dark,
    [data-bs-theme="dark"] #createEventModal #scoutCardsContainer .text-dark,
    html.dark #createEventModal #scoutCardsContainer .text-dark,
    html.dark-mode #createEventModal #scoutCardsContainer .text-dark {
        color: #f8fafc !important;
    }

    body.dark-mode #createEventModal #scoutCardsContainer .text-secondary,
    body.dark-theme #createEventModal #scoutCardsContainer .text-secondary,
    [data-bs-theme="dark"] #createEventModal #scoutCardsContainer .text-secondary,
    html.dark #createEventModal #scoutCardsContainer .text-secondary,
    html.dark-mode #createEventModal #scoutCardsContainer .text-secondary {
        color: #cbd5e1 !important;
    }

    body.dark-mode #createEventModal .ce-studio-card,
    body.dark-theme #createEventModal .ce-studio-card,
    [data-bs-theme="dark"] #createEventModal .ce-studio-card,
    html.dark #createEventModal .ce-studio-card,
    html.dark-mode #createEventModal .ce-studio-card {
        background: linear-gradient(135deg, rgba(225, 29, 72, 0.12) 0%, rgba(245, 158, 11, 0.08) 100%) !important;
        border-color: rgba(251, 113, 133, 0.28) !important;
    }

    body.dark-mode #createEventModal .ce-btn-cancel,
    body.dark-theme #createEventModal .ce-btn-cancel,
    [data-bs-theme="dark"] #createEventModal .ce-btn-cancel,
    html.dark #createEventModal .ce-btn-cancel,
    html.dark-mode #createEventModal .ce-btn-cancel {
        border-color: #334155 !important;
        color: #e2e8f0 !important;
        background: transparent !important;
    }

    @media (prefers-reduced-motion: reduce) {
        #createEventModal .ce-close-btn,
        #createEventModal .form-control,
        #createEventModal .form-select,
        #createEventModal .ce-scout-toggle,
        #createEventModal .ce-scout-toggle__chevron,
        #createEventModal .scout-pill,
        #createEventModal .ce-scout-btn,
        #createEventModal .ce-btn-cancel,
        #createEventModal .ce-btn-save {
            transition: none !important;
        }
    }
</style>

<!-- Create Event Modal -->
<div class="modal fade"
     id="createEventModal"
     tabindex="-1"
     aria-labelledby="createEventModalLabel"
     aria-hidden="true"
     x-data="{ scoutOpen: false }"
     x-init="$el.addEventListener('hidden.bs.modal', () => { scoutOpen = false; })">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable ce-dialog">
        <div class="modal-content ce-content">
            <div class="ce-accent-bar" aria-hidden="true"></div>

            <div class="modal-header ce-header">
                <div class="ce-header__lead">
                    <span class="ce-header__icon" aria-hidden="true">
                        <svg width="22" height="22" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg" focusable="false" aria-hidden="true">
                            <defs>
                                <linearGradient id="ce-hdr-cal-body" x1="5" y1="6" x2="23" y2="24" gradientUnits="userSpaceOnUse">
                                    <stop offset="0%" stop-color="#ffffff"/>
                                    <stop offset="100%" stop-color="#fff1f2"/>
                                </linearGradient>
                                <linearGradient id="ce-hdr-cal-top" x1="5" y1="6" x2="23" y2="11" gradientUnits="userSpaceOnUse">
                                    <stop offset="0%" stop-color="#fef08a"/>
                                    <stop offset="100%" stop-color="#f59e0b"/>
                                </linearGradient>
                                <linearGradient id="ce-hdr-star" x1="11" y1="12" x2="17" y2="21" gradientUnits="userSpaceOnUse">
                                    <stop offset="0%" stop-color="#fb7185"/>
                                    <stop offset="100%" stop-color="#a31621"/>
                                </linearGradient>
                            </defs>
                            <rect x="4.5" y="6.2" width="19" height="17.8" rx="4" fill="url(#ce-hdr-cal-body)" stroke="#fef08a" stroke-width="0.65"/>
                            <path d="M4.5 10.2C4.5 7.99 6.29 6.2 8.5 6.2H19.5C21.71 6.2 23.5 7.99 23.5 10.2V11.2H4.5V10.2Z" fill="url(#ce-hdr-cal-top)"/>
                            <rect x="8.6" y="4" width="2.4" height="4.2" rx="1.2" fill="#ffffff"/>
                            <rect x="17" y="4" width="2.4" height="4.2" rx="1.2" fill="#ffffff"/>
                            <path d="M14 13L15.1 15.55L17.85 15.88L15.8 17.7L16.38 20.4L14 19L11.62 20.4L12.2 17.7L10.15 15.88L12.9 15.55L14 13Z" fill="url(#ce-hdr-star)"/>
                        </svg>
                    </span>
                    <div class="ce-header__copy">
                        <h5 class="modal-title ce-title" id="createEventModalLabel">Create Event</h5>
                        <p class="ce-subtitle">Plan a family gathering, milestone, or day out</p>
                    </div>
                </div>
                <button type="button" class="ce-close-btn" data-bs-dismiss="modal" aria-label="Close">
                    <i class="bi bi-x-lg" aria-hidden="true"></i>
                </button>
            </div>

            {{-- Deliberately OUTSIDE the form: FormHelper's massValidate() iterates
                 every named control inside #eventModalForm and treats an empty one
                 as a validation error, which a hidden "not editing" field would be. --}}
            <input type="hidden" id="editEventNo" value="">

            <form id="eventModalForm" class="eventModalForm ce-form">
                <div class="modal-body ce-body">
                    <div id="editEventNotice" class="ce-edit-notice d-none">
                        <i class="bi bi-pencil-square" aria-hidden="true"></i>
                        <span>Editing this event</span>
                    </div>

                    <div id="loader" class="text-center mb-2"></div>
                    <div id="eventModalForm_notification">
                        <p id="error" class="ce-error-banner mb-2"></p>
                    </div>

                    <!-- Section 1: Core Required Fields (MUST precede any <button type="button"> for FormHelper.massValidate()) -->
                    <div class="ce-section">
                        <div>
                            <label for="eventName" class="form-label">
                                <span>Event name <span class="ce-req" aria-hidden="true">*</span></span>
                            </label>
                            <input type="text"
                                   class="form-control form-control--lg"
                                   id="eventName"
                                   name="eventName"
                                   placeholder="Event name (e.g. Sunday Garden Barbecue)">
                            <small id="eventName_error" class="ce-field-error"></small>
                        </div>

                        <div class="row g-3">
                            <div class="col-12 col-sm-6">
                                <label for="eventDate" class="form-label">
                                    <span>Event date <span class="ce-req" aria-hidden="true">*</span></span>
                                </label>
                                <input type="date" class="form-control" name="eventDate" id="eventDate">
                                <small id="eventDate_error" class="ce-field-error"></small>
                            </div>

                            <div class="col-12 col-sm-6">
                                <label for="eventType" class="form-label">
                                    <span>Event type <span class="ce-req" aria-hidden="true">*</span></span>
                                </label>
                                <select name="eventType" id="eventType" class="form-select">
                                    <option value="" disabled selected>Choose event type</option>
                                    <option value="Birthday">Birthday</option>
                                    <option value="Anniversary">Anniversary</option>
                                    <option value="Barbecue">Barbecue / Cookout</option>
                                    <option value="Museum">Museum / Cultural Outing</option>
                                    <option value="Weekend_Drive">Weekend Drive / Scenic Route</option>
                                    <option value="Holiday">Holiday / Staycation</option>
                                    <option value="Reunion">Reunion</option>
                                    <option value="House_warming">House Warming</option>
                                    <option value="Wedding">Wedding</option>
                                    <option value="Party">Party</option>
                                    <option value="Meeting">Meeting</option>
                                    <option value="Memorial">Memorial</option>
                                    <option value="Graduation">Graduation</option>
                                    <option value="Other">Other</option>
                                </select>
                                <small id="eventType_error" class="ce-field-error"></small>
                            </div>
                        </div>

                        <div>
                            <label for="eventFrequency" class="form-label">
                                <span>Frequency <span class="ce-req" aria-hidden="true">*</span></span>
                            </label>
                            <select class="form-select" name="eventFrequency" id="eventFrequency">
                                <option value="" disabled selected>Choose frequency</option>
                                <option value="One-off">One-off</option>
                                <option value="Annually">Annually</option>
                                <option value="Monthly">Monthly</option>
                                <option value="Weekly">Weekly</option>
                            </select>
                            <small id="eventFrequency_error" class="ce-field-error"></small>
                        </div>

                        <div>
                            <label for="eventDescription" class="form-label">
                                <span>Description <span class="ce-req" aria-hidden="true">*</span></span>
                            </label>
                            <textarea class="form-control"
                                      id="eventDescription"
                                      name="eventDescription"
                                      rows="3"
                                      placeholder="Event description (time, what to bring, or note)"></textarea>
                            <small id="eventDescription_error" class="ce-field-error"></small>
                        </div>
                    </div>

                    <!-- Section 2: Location & Guests -->
                    <div class="ce-section ce-section--divided">
                        <h6 class="ce-section__heading">Location &amp; guests</h6>

                        <div>
                            <label for="eventLocation" class="form-label">
                                <span>Venue or location</span>
                                <span class="ce-optional-tag">Optional</span>
                            </label>
                            <div class="ce-input-icon-wrap">
                                <i class="bi bi-geo-alt-fill ce-input-icon-wrap__icon" aria-hidden="true"></i>
                                <input type="text"
                                       class="form-control"
                                       id="eventLocation"
                                       data-optional="true"
                                       placeholder="e.g. Richmond Park or postcode">
                            </div>
                        </div>

                        <div class="ce-panel">
                            <div class="ce-panel__header">
                                <span class="ce-panel__title">
                                    <i class="bi bi-geo-alt-fill text-danger" aria-hidden="true"></i>
                                    <span>Who should be notified?</span>
                                </span>
                                <span class="badge rounded-pill bg-light text-secondary border px-2 py-1" id="proximityBadgePreview">All Family</span>
                            </div>

                            <div class="row g-3">
                                <div class="col-12 col-sm-7">
                                    <label for="proximityModeSelect" class="visually-hidden">Notification audience</label>
                                    <select class="form-select" id="proximityModeSelect" data-optional="true">
                                        <option value="all_family" selected>All family members</option>
                                        <option value="proximity_only">Nearby relatives only</option>
                                    </select>
                                </div>
                                <div class="col-12 col-sm-5">
                                    <label for="targetRadiusMilesSelect" class="visually-hidden">Proximity radius</label>
                                    <select class="form-select" id="targetRadiusMilesSelect" data-optional="true" disabled>
                                        <option value="10">Within 10 miles</option>
                                        <option value="25" selected>Within 25 miles</option>
                                        <option value="50">Within 50 miles</option>
                                        <option value="100">Within 100 miles</option>
                                    </select>
                                </div>
                            </div>

                            <div id="proximityAudienceNote" class="ce-helper-note">
                                <i class="bi bi-info-circle" aria-hidden="true"></i>
                                <span>All registered family members will receive notifications for this event.</span>
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: Optional Extras -->
                    <div class="ce-section ce-section--divided">
                        <!-- Collapsible Outing Inspiration -->
                        <div class="ce-scout-card" id="aiActivityScoutCard">
                            <button type="button"
                                    class="ce-scout-toggle"
                                    @click="scoutOpen = !scoutOpen"
                                    :aria-expanded="scoutOpen ? 'true' : 'false'"
                                    aria-controls="ceScoutDrawerBody">
                                <span class="ce-scout-toggle__lead">
                                    <span class="ce-scout-toggle__badge" aria-hidden="true">
                                        <i class="bi bi-compass-fill"></i>
                                    </span>
                                    <span>
                                        <span class="ce-scout-toggle__title">Need venue inspiration?</span>
                                        <span class="ce-scout-toggle__sub">Discover family-friendly parks, museums, drives &amp; stays</span>
                                    </span>
                                </span>
                                <span class="ce-scout-toggle__action">
                                    <span x-text="scoutOpen ? 'Hide' : 'Explore'">Explore</span>
                                    <i class="bi bi-chevron-down ce-scout-toggle__chevron" :class="scoutOpen ? 'is-open' : ''" aria-hidden="true"></i>
                                </span>
                            </button>

                            <div id="ceScoutDrawerBody" class="ce-scout-body" x-show="scoutOpen" x-collapse x-cloak>
                                <div class="d-flex flex-wrap gap-2 mt-2 mb-3" id="scoutCategoryPills">
                                    <button type="button" class="btn scout-pill active" data-cat="barbecue">
                                        🍖 Barbecue &amp; Picnic
                                    </button>
                                    <button type="button" class="btn scout-pill" data-cat="museum">
                                        🏛️ Museum &amp; Culture
                                    </button>
                                    <button type="button" class="btn scout-pill" data-cat="weekend_drive">
                                        🚗 Weekend Drive
                                    </button>
                                    <button type="button" class="btn scout-pill" data-cat="holiday">
                                        🏖️ Holiday Staycation
                                    </button>
                                </div>

                                <div class="ce-scout-search">
                                    <div class="ce-input-icon-wrap flex-grow-1">
                                        <i class="bi bi-geo-alt-fill ce-input-icon-wrap__icon" aria-hidden="true"></i>
                                        <input type="text"
                                               class="form-control"
                                               id="scoutLocationInput"
                                               data-optional="true"
                                               placeholder="Town, city or postcode">
                                    </div>
                                    <button type="button" class="btn ce-scout-btn" id="btnRunScout">
                                        <i class="bi bi-compass" aria-hidden="true"></i>
                                        <span>Scout Outings</span>
                                    </button>
                                </div>

                                <div id="scoutResultsArea" class="d-none mt-3">
                                    <div id="scoutLoadingSpinner" class="text-center py-3 d-none">
                                        <div class="spinner-border spinner-border-sm text-danger" role="status"></div>
                                        <span class="ms-2 small">Scouting live verified ideas...</span>
                                    </div>
                                    <div id="scoutCardsContainer" class="d-flex flex-column gap-2"></div>
                                </div>
                            </div>
                        </div>

                        <!-- PartyPlatform Studio Escalation Toggle -->
                        <div class="ce-studio-card" id="partyPlatformToggleCard">
                            <div class="ce-studio-card__lead">
                                <span class="ce-studio-card__badge" aria-hidden="true">
                                    <i class="bi bi-stars"></i>
                                </span>
                                <div>
                                    <label for="enablePartyPlatformToggle" class="ce-studio-card__title">
                                        Plan with PartyPlatform Studio
                                    </label>
                                    <p class="ce-studio-card__desc">
                                        Pre-load your family tree into Guest Builder with interactive RSVPs, QR check-in and celebration cards.
                                    </p>
                                </div>
                            </div>
                            <div class="form-check form-switch m-0 ps-0 ce-switch-wrap">
                                <input class="form-check-input"
                                       type="checkbox"
                                       role="switch"
                                       id="enablePartyPlatformToggle"
                                       data-optional="true"
                                       aria-label="Plan with PartyPlatform Studio">
                            </div>
                        </div>
                    </div>

                    @include('csrf')
                </div>

                <div class="modal-footer ce-footer">
                    <button type="button" class="btn ce-btn-cancel" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" name="submit" class="btn ce-btn-save" id="submitEventModal">Create Event</button>
                </div>
            </form>

        </div>
    </div>
</div>
