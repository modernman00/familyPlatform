import { useState } from 'preact/hooks';
import { formatElapsedMs } from './arcadeEngine';
import styles from './ArcadeShell.module.css';

const TABS = [
    { key: 'family_cup', label: 'Family Cup' },
    { key: 'kinzip', label: 'KinZip' },
    { key: 'kingrid', label: 'KinGrid' },
    { key: 'kinword', label: 'KinWord' },
    { key: 'kinpulse', label: 'KinPulse' },
];

export function LeagueTablesPanel({
    leagues,
    champions = [],
    period,
    includeConnected,
    onChangePeriod,
    onToggleConnected,
    currentUserId,
}) {
    const [activeTab, setActiveTab] = useState('family_cup');

    const rows = activeTab === 'family_cup'
        ? (leagues?.familyCup || [])
        : (leagues?.games?.[activeTab] || []);

    return (
        <aside className={styles.leaguePanel} aria-label="Family Arcade League Tables">
            <div className={styles.leagueHeader}>
                <h2 className={styles.leagueTitle}>
                    <i className="bi bi-trophy-fill text-warning" aria-hidden="true" />
                    <span>Family League Tables</span>
                </h2>
                <div className={styles.leagueToggles}>
                    <div className={styles.segmented} role="group" aria-label="League timeframe">
                        <button
                            type="button"
                            className={`${styles.segBtn} ${period === 'weekly' ? styles.segBtnActive : ''}`}
                            onClick={() => onChangePeriod('weekly')}
                        >
                            Weekly
                        </button>
                        <button
                            type="button"
                            className={`${styles.segBtn} ${period === 'all_time' ? styles.segBtnActive : ''}`}
                            onClick={() => onChangePeriod('all_time')}
                        >
                            All-Time
                        </button>
                    </div>
                    <button
                        type="button"
                        className={`${styles.connBtn} ${includeConnected ? styles.connBtnActive : ''}`}
                        onClick={() => onToggleConnected(!includeConnected)}
                        aria-pressed={includeConnected}
                    >
                        <i className="bi bi-people-fill" aria-hidden="true" />
                        <span>{includeConnected ? 'Connected Families: On' : 'My Family Only'}</span>
                    </button>
                </div>
            </div>

            {champions.length > 0 && (
                <div className={styles.championsStrip} aria-label="Current Family Title Holders">
                    {champions.map((c) => (
                        <div key={c.badgeKey} className={styles.championChip}>
                            <i className="bi bi-award-fill" aria-hidden="true" />
                            <span><strong>{c.badgeTitle}:</strong> {c.fullName}</span>
                        </div>
                    ))}
                </div>
            )}

            <div className={styles.leagueTabs} role="tablist" aria-label="Game leaderboards">
                {TABS.map((t) => (
                    <button
                        key={t.key}
                        type="button"
                        role="tab"
                        aria-selected={activeTab === t.key}
                        className={`${styles.leagueTabBtn} ${activeTab === t.key ? styles.leagueTabActive : ''}`}
                        onClick={() => setActiveTab(t.key)}
                    >
                        {t.label}
                    </button>
                ))}
            </div>

            {rows.length === 0 ? (
                <div className={styles.emptyLeague}>
                    <i className="bi bi-stars" aria-hidden="true" />
                    <p>No scores recorded yet for this period. Complete a puzzle to claim #1!</p>
                </div>
            ) : (
                <ol className={styles.leagueList}>
                    {rows.map((row, idx) => {
                        const isMe = String(row.userId) === String(currentUserId);
                        return (
                            <li key={`${row.userId}-${idx}`} className={`${styles.leagueRow} ${isMe ? styles.leagueRowMe : ''}`}>
                                <span className={styles.rankNum}>
                                    {idx === 0 ? '👑' : `#${idx + 1}`}
                                </span>
                                <div className={styles.rankInfo}>
                                    <div className={styles.rankName} title={row.fullName}>
                                        {row.fullName} {isMe ? '(You)' : ''}
                                    </div>
                                    <div className={styles.rankSub}>
                                        {activeTab === 'family_cup'
                                            ? `${row.gamesMastered} games • ${row.gamesPlayed} runs`
                                            : `Best time ${formatElapsedMs(row.bestTimeMs)} • ${row.gamesPlayed} runs`}
                                    </div>
                                </div>
                                <span className={styles.rankScore}>
                                    {(activeTab === 'family_cup' ? row.totalScore : row.bestScore).toLocaleString()} pts
                                </span>
                            </li>
                        );
                    })}
                </ol>
            )}
        </aside>
    );
}
