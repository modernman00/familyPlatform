import { useEffect, useState } from 'preact/hooks';
import { postAccountForm, toastMessage } from '../api/accountSettingsApi';
import {
    createEmptyFamilyRow,
    FamilyMemberRows,
} from '../components/FamilyMemberRows';
import {
    CsrfFields,
    LineageScopePicker,
    StatusAlert,
    SubmitButton,
} from '../components/SettingsControls';
import styles from '../AccountSettingsShell.module.css';

export function SiblingsTab({ accountData, csrfToken, bootstrap, onDirtyChange }) {
    const hasMaternalCode = Boolean(accountData.hasMaternalCode && accountData.otherFamCode);

    const [lineageScope, setLineageScope] = useState(hasMaternalCode ? 'maternal' : 'primary');
    const [count, setCount] = useState(0);
    const [rows, setRows] = useState([]);
    const [saving, setSaving] = useState(false);
    const [status, setStatus] = useState({ type: 'info', message: '' });

    useEffect(() => {
        setLineageScope(accountData.hasMaternalCode && accountData.otherFamCode ? 'maternal' : 'primary');
    }, [accountData.hasMaternalCode, accountData.otherFamCode]);

    useEffect(() => {
        if (typeof onDirtyChange === 'function') {
            onDirtyChange('siblings', count > 0);
        }
    }, [count, onDirtyChange]);

    const lineageCode =
        lineageScope === 'maternal' && accountData.otherFamCode
            ? accountData.otherFamCode
            : accountData.famCode || '';

    const handleCountChange = (val) => {
        const nextCount = Number(val) || 0;
        setCount(nextCount);
        setRows((prev) =>
            Array.from({ length: nextCount }, (_, idx) => prev[idx] || createEmptyFamilyRow())
        );
    };

    const handleChangeRow = (idx, field, val) => {
        setRows((prev) =>
            prev.map((row, i) => (i === idx ? { ...row, [field]: val } : row))
        );
    };

    const handleSubmit = async (e) => {
        if (e && e.preventDefault) e.preventDefault();
        if (saving) return;

        setSaving(true);
        setStatus({ type: 'info', message: '' });

        try {
            const payload = {
                action: 'updateSiblings',
                lineage_scope: lineageScope,
                lineage_code: lineageCode,
                sibling: count,
            };
            for (let i = 0; i < count; i++) {
                const no = i + 1;
                const r = rows[i] || createEmptyFamilyRow();
                payload[`sibling_option${no}`] = r.option;
                payload[`sibling_first_name${no}`] = r.firstName.trim();
                payload[`sibling_last_name${no}`] = r.lastName.trim();
                payload[`sibling_gender${no}`] = r.gender;
                payload[`sibling_other_parent${no}`] = r.otherParent.trim();
                payload[`sibling_email${no}`] = r.email.trim();
            }

            const res = await postAccountForm(payload, bootstrap);
            if (res.ok) {
                setCount(0);
                setRows([]);
                if (typeof onDirtyChange === 'function') {
                    onDirtyChange('siblings', false);
                }
                setStatus({ type: 'success', message: res.message || 'Siblings information successfully updated.' });
                toastMessage(res.message || 'Siblings information successfully updated.', 'success');
            } else {
                setStatus({ type: 'error', message: res.message || 'Could not save siblings information.' });
            }
        } catch (err) {
            setStatus({
                type: 'error',
                message: (err && err.message) || 'Network error while saving siblings information.',
            });
        } finally {
            setSaving(false);
        }
    };

    return (
        <div>
            <div className={styles.sectionTitle}>Siblings Information</div>
            <div className={styles.sectionSubtitle}>
                Add and manage your brothers and sisters in your family tree.
            </div>
            <div className={styles.sectionDivider} />

            {hasMaternalCode && (
                <LineageScopePicker
                    prefix="siblings"
                    primaryId="siblings-scope-primary"
                    maternalId="siblings-scope-maternal"
                    famCode={accountData.famCode}
                    otherFamCode={accountData.otherFamCode}
                    scope={lineageScope}
                    onChange={setLineageScope}
                    subjectLabel="siblings"
                />
            )}

            <StatusAlert
                id="siblingsForm_notification"
                errorId="siblingsForm_notification_error"
                message={status.message}
                type={status.type}
            />

            <form id="siblingsForm" onSubmit={handleSubmit}>
                <input type="hidden" name="action" value="updateSiblings" />
                <CsrfFields token={csrfToken} />
                <input type="hidden" name="lineage_scope" id="siblingsLineageScope" value={lineageScope} />
                <input type="hidden" name="lineage_code" id="siblingsLineageCode" value={lineageCode} />

                <div className="mb-4">
                    <label htmlFor="sibling" className={styles.formLabel}>
                        Select Number of Siblings
                    </label>
                    <select
                        className={`form-select ${styles.formSelect}`}
                        id="sibling"
                        name="sibling"
                        value={String(count)}
                        onChange={(e) => handleCountChange(e.currentTarget.value)}
                    >
                        <option value="0">0 Siblings</option>
                        {[1, 2, 3, 4, 5, 6, 7, 8, 9, 10].map((num) => (
                            <option key={num} value={String(num)}>
                                {num} {num === 1 ? 'Sibling' : 'Siblings'}
                            </option>
                        ))}
                    </select>
                    {count > 0 && (
                        <div id="sibling_help" className={`${styles.formHelp} fw-bold`}>
                            {count > 1 ? 'Please, provide details for each below:' : 'Please, provide details below:'}
                        </div>
                    )}
                    <div id="sibling_div">
                        <FamilyMemberRows
                            kind="sibling"
                            count={count}
                            rows={rows}
                            onChangeRow={handleChangeRow}
                            yourFirstName={accountData.firstName || ''}
                            inviteFamCode={lineageCode}
                            bootstrap={bootstrap}
                        />
                    </div>
                </div>

                <SubmitButton
                    id="siblingsBtn"
                    type="submit"
                    loading={saving}
                    className="w-100 mt-2"
                >
                    Save Siblings Details
                </SubmitButton>
            </form>
        </div>
    );
}
