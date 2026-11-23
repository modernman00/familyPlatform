import { useEffect, useState } from 'preact/hooks';
import { postAccountForm, toastMessage } from '../api/accountSettingsApi';
import {
    createEmptyFamilyRow,
    FamilyMemberRows,
} from '../components/FamilyMemberRows';
import { CsrfFields, StatusAlert, SubmitButton } from '../components/SettingsControls';
import styles from '../AccountSettingsShell.module.css';

export function ChildrenTab({ accountData, csrfToken, bootstrap, onDirtyChange }) {
    const [count, setCount] = useState(0);
    const [rows, setRows] = useState([]);
    const [saving, setSaving] = useState(false);
    const [status, setStatus] = useState({ type: 'info', message: '' });

    useEffect(() => {
        if (typeof onDirtyChange === 'function') {
            onDirtyChange('children', count > 0);
        }
    }, [count, onDirtyChange]);

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
                action: 'updateChildren',
                children: count,
            };
            for (let i = 0; i < count; i++) {
                const no = i + 1;
                const r = rows[i] || createEmptyFamilyRow();
                payload[`children_option${no}`] = r.option;
                payload[`children_first_name${no}`] = r.firstName.trim();
                payload[`children_last_name${no}`] = r.lastName.trim();
                payload[`children_gender${no}`] = r.gender;
                payload[`children_other_parent${no}`] = r.otherParent.trim();
                payload[`children_union_status${no}`] = r.unionStatus;
                payload[`children_email${no}`] = r.email.trim();
            }

            const res = await postAccountForm(payload, bootstrap);
            if (res.ok) {
                setCount(0);
                setRows([]);
                if (typeof onDirtyChange === 'function') {
                    onDirtyChange('children', false);
                }
                setStatus({ type: 'success', message: res.message || 'Children information successfully updated.' });
                toastMessage(res.message || 'Children information successfully updated.', 'success');
            } else {
                setStatus({ type: 'error', message: res.message || 'Could not save children information.' });
            }
        } catch (err) {
            setStatus({
                type: 'error',
                message: (err && err.message) || 'Network error while saving children information.',
            });
        } finally {
            setSaving(false);
        }
    };

    return (
        <div>
            <div className={styles.sectionTitle}>Children Information</div>
            <div className={styles.sectionSubtitle}>
                Add and manage children connections in your family tree.
            </div>
            <div className={styles.sectionDivider} />

            <StatusAlert
                id="childrenForm_notification"
                errorId="childrenForm_notification_error"
                message={status.message}
                type={status.type}
            />

            <form id="childrenForm" onSubmit={handleSubmit}>
                <input type="hidden" name="action" value="updateChildren" />
                <CsrfFields token={csrfToken} />

                <div className="mb-4">
                    <label htmlFor="children" className={styles.formLabel}>
                        Select Number of Children
                    </label>
                    <select
                        className={`form-select ${styles.formSelect}`}
                        id="children"
                        name="children"
                        value={String(count)}
                        onChange={(e) => handleCountChange(e.currentTarget.value)}
                    >
                        <option value="0">0 Children</option>
                        {[1, 2, 3, 4, 5, 6, 7, 8, 9, 10].map((num) => (
                            <option key={num} value={String(num)}>
                                {num} {num === 1 ? 'Child' : 'Children'}
                            </option>
                        ))}
                    </select>
                    {count > 0 && (
                        <div id="children_help" className={`${styles.formHelp} fw-bold`}>
                            {count > 1 ? 'Please, provide details for each below:' : 'Please, provide details below:'}
                        </div>
                    )}
                    <div id="children_div">
                        <FamilyMemberRows
                            kind="children"
                            count={count}
                            rows={rows}
                            onChangeRow={handleChangeRow}
                            yourFirstName={accountData.firstName || ''}
                            inviteFamCode={accountData.famCode || ''}
                            bootstrap={bootstrap}
                        />
                    </div>
                </div>

                <SubmitButton
                    id="childrenBtn"
                    type="submit"
                    loading={saving}
                    className="w-100 mt-2"
                >
                    Save Children Details
                </SubmitButton>
            </form>
        </div>
    );
}
