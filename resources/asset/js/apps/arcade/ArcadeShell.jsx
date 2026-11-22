import { useState, useEffect, useRef } from 'preact/hooks';
import { apiFetch } from '../shared/apiClient';
import { notifyToast } from '../shared/bootstrap';
import { computeGhostProgress, formatElapsedMs } from './arcadeEngine';
import { KinZipGame } from './KinZipGame';
import { KinGridGame } from './KinGridGame';
import { KinWordGame } from './KinWordGame';
import { KinPulseGame } from './KinPulseGame';
import { TrophyStage3D } from './TrophyStage3D';
import { LeagueTablesPanel } from './LeagueTablesPanel';
import { VictoryReplayModal } from './VictoryReplayModal';
import styles from './ArcadeShell.module.css';

const GAMES = [
    { slug: 'kinzip', name: 'KinZip', tag: 'Hamiltonian Path', icon: 'bi-diagram-3-fill' },
    { slug: 'kingrid', name: 'KinGrid', tag: 'Logic Deduction', icon: 'bi-grid-3x3-gap-fill' },
    { slug: 'kinword', name: 'KinWord', tag: 'Word Quartet', icon: 'bi-fonts' },
    { slug: 'kinpulse', name: 'KinPulse', tag: '3D Memory', icon: 'bi-lightning-charge-fill' },
];

export function ArcadeShell({ bootstrap = {} }) {
    const [activeGame, setActiveGame] = useState(bootstrap.initialGame || 'kinzip');
    const [difficulty, setDifficulty] = useState('medium');
    const [mode, setMode] = useState(bootstrap.raceToken ? 'live_race' : 'daily');
    const [puzzle, setPuzzle] = useState(null);
    const [ghost, setGhost] = useState(null);
    const [personalBest, setPersonalBest] = useState({ score: 0, elapsedMs: 0 });
    const [loadingPuzzle, setLoadingPuzzle] = useState(true);
    const [submitting, setSubmitting] = useState(false);

    const [playerProgress, setPlayerProgress] = useState(0);
    const [playerElapsedMs, setPlayerElapsedMs] = useState(0);
    const [raceToken, setRaceToken] = useState(bootstrap.raceToken || '');
    const [raceShareUrl, setRaceShareUrl] = useState('');
    const [liveOpponent, setLiveOpponent] = useState(null);

    const [leagues, setLeagues] = useState({ familyCup: [], games: {} });
    const [champions, setChampions] = useState(bootstrap.champions || []);
    const [period, setPeriod] = useState('weekly');
    const [includeConnected, setIncludeConnected] = useState(false);

    const [victoryResult, setVictoryResult] = useState(null);
    const [lastReplay, setLastReplay] = useState([]);
    const [sharingToFeed, setSharingToFeed] = useState(false);

    const lastProgressSentRef = useRef(0);

    const loadPuzzle = async (gameSlug, diff, playMode, practiceSeed = '') => {
        setLoadingPuzzle(true);
        setPlayerProgress(0);
        setPlayerElapsedMs(0);
        try {
            const params = new URLSearchParams({
                game: gameSlug,
                difficulty: diff,
                mode: playMode,
            });
            if (practiceSeed) params.set('seed', practiceSeed);
            if (bootstrap.challengeToken) params.set('challenge', bootstrap.challengeToken);

            const res = await apiFetch(`/api/arcade/puzzle?${params.toString()}`);
            if (res?.status === 'success' && res.data) {
                setPuzzle(res.data.puzzle);
                setGhost(res.data.ghost || null);
                setPersonalBest(res.data.personalBest || { score: 0, elapsedMs: 0 });
            }
        } catch (err) {
            notifyToast(err?.message || 'Could not load puzzle.', 'error');
        } finally {
            setLoadingPuzzle(false);
        }
    };

    const loadLeagues = async (p = period, conn = includeConnected) => {
        try {
            const res = await apiFetch(`/api/arcade/leagues?period=${p}&connected=${conn ? '1' : '0'}`);
            if (res?.status === 'success' && res.data) {
                setLeagues(res.data.leagues || { familyCup: [], games: {} });
                if (Array.isArray(res.data.champions)) {
                    setChampions(res.data.champions);
                }
            }
        } catch {
            // Silent
        }
    };

    useEffect(() => {
        loadPuzzle(activeGame, difficulty, mode);
    }, [activeGame, difficulty, mode]);

    useEffect(() => {
        loadLeagues(period, includeConnected);
    }, [period, includeConnected]);

    useEffect(() => {
        if (!raceToken || !bootstrap.famCode || typeof window === 'undefined' || typeof EventSource === 'undefined') {
            return undefined;
        }
        const famSlug = String(bootstrap.famCode).replace(/[^a-zA-Z0-9_-]/g, '') || 'default';
        const url = new URL('/api/broadcasting/events', window.location.origin);
        url.searchParams.set('channel', `private-family-${famSlug}`);
        url.searchParams.set('since_now', '1');

        const es = new EventSource(url.toString(), { withCredentials: true });
        es.addEventListener('arcade.race_progress', (ev) => {
            try {
                const payload = JSON.parse(ev.data);
                if (
                    payload
                    && payload.raceToken === raceToken
                    && String(payload.userId || '') !== String(bootstrap.userId || '')
                ) {
                    setLiveOpponent({
                        userId: String(payload.userId || ''),
                        fullName: String(payload.fullName || 'Family Challenger'),
                        progress: Math.max(0, Math.min(100, Number(payload.progress || 0))),
                    });
                }
            } catch {
                // Ignore malformed SSE payload
            }
        });

        return () => {
            es.close();
        };
    }, [raceToken, bootstrap.famCode, bootstrap.userId]);

    const handleProgress = (pct, elapsed) => {
        setPlayerProgress(pct);
        setPlayerElapsedMs(elapsed);

        if (raceToken && (pct === 100 || Math.abs(pct - lastProgressSentRef.current) >= 10)) {
            lastProgressSentRef.current = pct;
            apiFetch('/api/arcade/race/progress', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    _token: bootstrap.csrfToken,
                    raceToken,
                    progress: pct,
                }),
            }).catch(() => {});
        }
    };

    const handleComplete = async (payload) => {
        if (!puzzle || submitting) return;
        setSubmitting(true);
        setLastReplay(payload.replay || []);
        try {
            const res = await apiFetch('/api/arcade/submit', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    _token: bootstrap.csrfToken,
                    game: activeGame,
                    difficulty,
                    mode,
                    seedKey: puzzle.seedKey,
                    elapsedMs: payload.elapsedMs,
                    movesCount: payload.movesCount,
                    hintsUsed: payload.hintsUsed,
                    mistakesCount: payload.mistakesCount,
                    submission: payload.submission,
                    replay: payload.replay || [],
                    raceToken,
                }),
            });

            if (res?.status === 'success' && res.data) {
                setVictoryResult(res.data);
                if (res.data.leagues) setLeagues(res.data.leagues);
                loadLeagues(period, includeConnected);
            } else {
                notifyToast(res?.message || 'Could not verify score.', 'error');
            }
        } catch (err) {
            notifyToast(err?.message || 'Score submission failed.', 'error');
        } finally {
            setSubmitting(false);
        }
    };

    const handleCreateLiveRace = async () => {
        try {
            const res = await apiFetch('/api/arcade/race/create', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    _token: bootstrap.csrfToken,
                    game: activeGame,
                    seedKey: puzzle?.seedKey || bootstrap.dailyDate,
                }),
            });
            if (res?.status === 'success' && res.data) {
                setRaceToken(res.data.raceToken);
                setMode('live_race');
                const fullUrl = `${window.location.origin}${res.data.raceUrl}`;
                setRaceShareUrl(fullUrl);
                try {
                    await navigator.clipboard.writeText(fullUrl);
                    notifyToast('Live Race room created & link copied!', 'success');
                } catch {
                    notifyToast('Live Race room created!', 'success');
                }
            }
        } catch {
            notifyToast('Could not start live race.', 'error');
        }
    };

    const handleShareToFeed = async () => {
        if (!victoryResult?.challengeToken || sharingToFeed) return;
        setSharingToFeed(true);
        try {
            const res = await apiFetch('/api/arcade/share-to-feed', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    _token: bootstrap.csrfToken,
                    challengeToken: victoryResult.challengeToken,
                }),
            });
            if (res?.status === 'success') {
                notifyToast('Challenge posted to your Family Feed!', 'success');
            }
        } catch {
            notifyToast('Could not post to feed.', 'error');
        } finally {
            setSharingToFeed(false);
        }
    };

    const ghostPct = computeGhostProgress(ghost, puzzle?.parMs, playerElapsedMs);
    const activeGameMeta = GAMES.find((g) => g.slug === activeGame) || GAMES[0];

    return (
        <div className={styles.arcadeShell}>
            {/* Hero Header */}
            <header className={styles.hero}>
                <div className={styles.heroText}>
                    <span className={styles.heroEyebrow}>FAMILY ARCADE &bull; DAILY SEED {bootstrap.dailyDate || ''}</span>
                    <h1 className={styles.heroTitle}>Compete, Outsmart &amp; Claim the Family Crown</h1>
                    <p className={styles.heroSub}>
                        Play today’s shared family puzzle, race the ATG Algorithm Bot or a relative’s ghost, and climb the Family Cup league table.
                    </p>
                </div>
                <TrophyStage3D champions={champions} />
            </header>

            {/* 4-Game Selector */}
            <nav className={styles.gameNav} aria-label="Select Family Arcade game">
                {GAMES.map((g) => (
                    <button
                        key={g.slug}
                        type="button"
                        className={`${styles.gameNavBtn} ${activeGame === g.slug ? styles.gameNavBtnActive : ''}`}
                        onClick={() => setActiveGame(g.slug)}
                        aria-current={activeGame === g.slug ? 'page' : undefined}
                    >
                        <i className={`bi ${g.icon}`} aria-hidden="true" />
                        <span className={styles.gameNavName}>{g.name}</span>
                        <span className={styles.gameNavTag}>{g.tag}</span>
                    </button>
                ))}
            </nav>

            {/* Main 2-Column Workspace */}
            <div className={styles.workspace}>
                <section className={styles.stageCard} aria-label={`${activeGameMeta.name} Arena`}>
                    {/* Mode & Difficulty Toolbar */}
                    <div className={styles.toolbar}>
                        <div className={styles.segmented} role="group" aria-label="Game mode">
                            <button
                                type="button"
                                className={`${styles.segBtn} ${mode === 'daily' ? styles.segBtnActive : ''}`}
                                onClick={() => setMode('daily')}
                            >
                                Daily Seed
                            </button>
                            <button
                                type="button"
                                className={`${styles.segBtn} ${mode === 'practice' ? styles.segBtnActive : ''}`}
                                onClick={() => {
                                    setMode('practice');
                                    loadPuzzle(activeGame, difficulty, 'practice', `p-${Date.now()}`);
                                }}
                            >
                                New Practice
                            </button>
                        </div>

                        <div className={styles.segmented} role="group" aria-label="Difficulty">
                            {['easy', 'medium', 'hard'].map((d) => (
                                <button
                                    key={d}
                                    type="button"
                                    className={`${styles.segBtn} ${difficulty === d ? styles.segBtnActive : ''}`}
                                    onClick={() => setDifficulty(d)}
                                >
                                    {d.charAt(0).toUpperCase() + d.slice(1)}
                                </button>
                            ))}
                        </div>

                        <button type="button" className={styles.raceBtn} onClick={handleCreateLiveRace}>
                            <i className="bi bi-broadcast" aria-hidden="true" />
                            <span>Live SSE Race</span>
                        </button>
                    </div>

                    {raceShareUrl && (
                        <div className={styles.raceBanner}>
                            <i className="bi bi-lightning-fill" aria-hidden="true" />
                            <span>Live Race active! Share link copied for family challengers.</span>
                        </div>
                    )}

                    {/* Ghost / Algorithm Live Race Progress Bar */}
                    <div className={styles.ghostBar} aria-label="Race pace comparison">
                        <div className={styles.ghostTrack}>
                            <div className={styles.ghostLabelRow}>
                                <span><strong>You</strong> ({playerProgress}%)</span>
                                <span>
                                    {liveOpponent
                                        ? <><strong>{liveOpponent.fullName}</strong> (Live Race {liveOpponent.progress}%)</>
                                        : ghost
                                            ? <><strong>{ghost.fullName} Ghost</strong> ({formatElapsedMs(ghost.elapsedMs)})</>
                                            : <><strong>ATG Algorithm Bot</strong> (Par {formatElapsedMs(puzzle?.parMs || 60000)})</>}
                                </span>
                            </div>
                            <div className={styles.progressStack}>
                                <div className={styles.progressYou} style={{ width: `${playerProgress}%` }} />
                                <div className={styles.progressGhost} style={{ width: `${liveOpponent ? liveOpponent.progress : ghostPct}%` }} />
                            </div>
                        </div>
                        {personalBest.score > 0 && (
                            <div className={styles.pbChip}>
                                PB: {personalBest.score} pts
                            </div>
                        )}
                    </div>

                    {/* Game Board */}
                    {loadingPuzzle ? (
                        <div className={styles.loadingState}>
                            <div className="spinner-border text-warning" role="status" />
                            <span>Generating deterministic puzzle...</span>
                        </div>
                    ) : (
                        <>
                            {activeGame === 'kinzip' && (
                                <KinZipGame puzzle={puzzle} onProgress={handleProgress} onComplete={handleComplete} submitting={submitting} />
                            )}
                            {activeGame === 'kingrid' && (
                                <KinGridGame puzzle={puzzle} onProgress={handleProgress} onComplete={handleComplete} submitting={submitting} />
                            )}
                            {activeGame === 'kinword' && (
                                <KinWordGame puzzle={puzzle} onProgress={handleProgress} onComplete={handleComplete} submitting={submitting} />
                            )}
                            {activeGame === 'kinpulse' && (
                                <KinPulseGame puzzle={puzzle} onProgress={handleProgress} onComplete={handleComplete} submitting={submitting} />
                            )}
                        </>
                    )}
                </section>

                <LeagueTablesPanel
                    leagues={leagues}
                    champions={champions}
                    period={period}
                    includeConnected={includeConnected}
                    onChangePeriod={setPeriod}
                    onToggleConnected={setIncludeConnected}
                    currentUserId={bootstrap.userId}
                />
            </div>

            {victoryResult && (
                <VictoryReplayModal
                    result={victoryResult}
                    gameLabel={activeGameMeta.name}
                    playerName={bootstrap.fullName || 'Family Member'}
                    replaySteps={lastReplay}
                    onClose={() => setVictoryResult(null)}
                    onShareToFeed={handleShareToFeed}
                    sharingToFeed={sharingToFeed}
                />
            )}
        </div>
    );
}
