import styles from '../AccountSettingsShell.module.css';

export function CsrfFields({ token }) {
    const val = token || '';
    return (
        <>
            <input type="hidden" name="token" value={val} />
            <input type="hidden" name="_token" value={val} />
            <input type="hidden" name="csrf_token" value={val} />
        </>
    );
}

export function StatusAlert({ id, errorId, message, type = 'info' }) {
    const hasMessage = typeof message === 'string' && message.trim() !== '';
    let alertClass = 'alert shadow-sm border-0 rounded-3 mb-3 ';
    let iconClass = 'bi bi-info-circle-fill fs-5 me-3';
    let inlineStyle = { display: hasMessage ? 'block' : 'none' };

    if (type === 'success') {
        alertClass += 'alert-success';
        iconClass = 'bi bi-check-circle-fill fs-5 me-3';
    } else if (type === 'error') {
        alertClass += 'alert-warning';
        iconClass = 'bi bi-exclamation-octagon-fill fs-5 me-3';
        inlineStyle = {
            display: hasMessage ? 'block' : 'none',
            backgroundColor: '#fef2f2',
            color: '#991b1b',
            border: '1px solid #fecaca',
        };
    } else {
        alertClass += 'alert-info';
    }

    return (
        <div
            className={alertClass}
            id={id}
            role={hasMessage ? 'status' : undefined}
            aria-live="polite"
            style={inlineStyle}
        >
            <div className="d-flex align-items-center">
                <i className={iconClass} aria-hidden="true" />
                <p id={errorId} className="mb-0 fw-medium">
                    {message || ''}
                </p>
            </div>
        </div>
    );
}

export function SubmitButton({
    id,
    loading = false,
    disabled = false,
    type = 'submit',
    variant = 'primary',
    className = '',
    onClick,
    title,
    children,
}) {
    const baseClass =
        variant === 'primary'
            ? styles.btnPrimaryBrand
            : `btn ${variant} ${styles.actionBtn}`;

    return (
        <button
            type={type}
            id={id}
            onClick={onClick}
            title={title}
            disabled={disabled || loading}
            data-spin="true"
            className={`${baseClass} ${className}`.trim()}
        >
            {loading && (
                <span
                    className="spinner-border spinner-border-sm"
                    role="status"
                    aria-hidden="true"
                />
            )}
            <span>{children}</span>
        </button>
    );
}

export function SettingsSwitch({
    id,
    name,
    label,
    description,
    checked,
    onChange,
    disabled = false,
    statusLabel,
    hintId,
    labelId,
    extraControl,
}) {
    const displayStatus = statusLabel !== undefined ? statusLabel : checked ? 'ON' : 'OFF';

    return (
        <div className={styles.switchRow}>
            <div className={styles.switchInfo}>
                <h6>{label}</h6>
                <p id={hintId}>{description}</p>
            </div>
            {extraControl || (
                <div className={styles.toggleGroup}>
                    <span className={styles.toggleLabel} id={labelId}>
                        {displayStatus}
                    </span>
                    <label className={styles.switchControl}>
                        <input
                            type="checkbox"
                            id={id}
                            name={name}
                            value={checked ? 'on' : 'off'}
                            checked={Boolean(checked)}
                            disabled={disabled}
                            onChange={(e) => onChange(e.currentTarget.checked)}
                            aria-label={label}
                        />
                        <span className={styles.switchSlider} />
                    </label>
                </div>
            )}
        </div>
    );
}

export function LineageScopePicker({
    prefix,
    primaryId,
    maternalId,
    famCode,
    otherFamCode,
    scope,
    onChange,
    subjectLabel,
}) {
    const isMaternal = scope === 'maternal';
    const primaryCode = famCode || 'Primary';
    const resolvedPrimaryId = primaryId || `${prefix}-scope-primary`;
    const resolvedMaternalId = maternalId || `${prefix}-scope-maternal`;

    return (
        <div className={styles.lineageBanner} id={`${prefix}-lineage-banner`}>
            <div className="fw-bold mb-2" style={{ color: '#a31621' }}>
                <i className="bi bi-diagram-3-fill me-2" aria-hidden="true" />
                Which family tree do these {subjectLabel} belong to?
            </div>
            <p className="small mb-3" id={`${prefix}-lineage-hint`} style={{ color: 'var(--as-muted)' }}>
                {isMaternal ? (
                    <>
                        You have both a marital and a birth family code. Your {subjectLabel} default to your{' '}
                        <strong>Birth / Maiden Family (#{otherFamCode})</strong>, and any invitation sent will use that code.
                    </>
                ) : (
                    <>
                        Currently saving {subjectLabel} into your{' '}
                        <strong>Marital / Household Tree (#{primaryCode})</strong>. Switch anytime below.
                    </>
                )}
            </p>
            <div
                className="d-flex flex-column flex-sm-row gap-3"
                role="group"
                aria-label={`Lineage scope for ${subjectLabel}`}
            >
                <label
                    id={`${prefix}-scope-primary-label`}
                    className={`lineage-scope-pill d-flex align-items-center gap-2 px-4 py-3 fw-semibold ${styles.lineagePill} ${
                        !isMaternal ? styles.lineagePillSelected : ''
                    }`}
                >
                    <input
                        type="radio"
                        name={`${prefix}_lineage_ui`}
                        id={resolvedPrimaryId}
                        value="primary"
                        checked={!isMaternal}
                        onChange={() => onChange('primary')}
                        className="form-check-input m-0"
                    />
                    <span>
                        <i className="bi bi-house-heart-fill text-primary me-1" aria-hidden="true" />
                        Marital / Household Tree
                        <span className="badge bg-primary ms-2 text-uppercase" style={{ fontSize: '0.68rem' }}>
                            {primaryCode}
                        </span>
                    </span>
                </label>

                <label
                    id={`${prefix}-scope-maternal-label`}
                    className={`lineage-scope-pill d-flex align-items-center gap-2 px-4 py-3 fw-semibold ${styles.lineagePill} ${
                        isMaternal ? styles.lineagePillMaternalSelected : ''
                    }`}
                >
                    <input
                        type="radio"
                        name={`${prefix}_lineage_ui`}
                        id={resolvedMaternalId}
                        value="maternal"
                        checked={isMaternal}
                        onChange={() => onChange('maternal')}
                        className="form-check-input m-0"
                    />
                    <span>
                        <i className="bi bi-tree-fill text-success me-1" aria-hidden="true" />
                        My Birth / Maiden Family
                        <span className="badge bg-success ms-2 text-uppercase" style={{ fontSize: '0.68rem' }}>
                            {otherFamCode}
                        </span>
                    </span>
                </label>
            </div>
        </div>
    );
}
