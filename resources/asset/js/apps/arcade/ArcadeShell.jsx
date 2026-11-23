import { useState, useEffect, useRef } from 'preact/hooks';
import { apiFetch } from '../shared/apiClient';
import { notifyToast } from '../shared/bootstrap';
import { computeGhostProgress, formatElapsedMs } from './arcadeEngine';
import { KinZipGame } from './KinZipGame';
import { KinGridGame } from './KinGridGame';
import { KinWordGame } from './KinWordGame';
import { KinPulseGame } from './KinPulseGame';
import { WordieGame } from './WordieGame';
import { CheeseboardGame } from './CheeseboardGame';
import { AyoGame } from './AyoGame';
import { FamilyChessGame } from './FamilyChessGame';
import { MinesweeperGame } from './MinesweeperGame';
import { TrophyStage3D } from './TrophyStage3D';
import { LeagueTablesPanel } from './LeagueTablesPanel';
import { VictoryReplayModal } from './VictoryReplayModal';
import { HowToPlayModal } from './HowToPlayModal';
import styles from './ArcadeShell.module.css';

const GAMES = [
    { slug: 'kinzip', name: 'KinZip', tag: 'Connect the Dots', icon: 'bi-diagram-3-fill' },
    { slug: 'kingrid', name: 'KinGrid', tag: 'Number Puzzle', icon: 'bi-grid-3x3-gap-fill' },
    { slug: 'kinword', name: 'KinWord', tag: 'Word Groups', icon: 'bi-fonts' },
    { slug: 'kinpulse', name: 'KinPulse', tag: 'Memory Pattern', icon: 'bi-lightning-charge-fill' },
    { slug: 'wordie', name: 'Wordie', tag: '5-Letter Word', icon: 'bi-alphabet-uppercase' },
    { slug: 'cheeseboard', name: 'Cheeseboard', tag: 'Queens Territory (Not Chess)', icon: 'bi-grid-fill' },
    { slug: 'chess', name: 'Family Chess', tag: 'Classic 8x8 Strategy', icon: 'bi-suit-club-fill' },
    { slug: 'ayo', name: 'Ayò Olópon', tag: 'Yorùbá Board of Kings', icon: 'bi-circle-square' },
    { slug: 'minesweeper', name: 'Minesweeper', tag: 'Safe Path', icon: 'bi-gem' },
];

export function ArcadeShell({ bootstrap = {} }) {
    const isGuest = Boolean(bootstrap.guestMode);
    const challenger = bootstrap.challenger || null;

    const [activeGame, setActiveGame] = useState(() => {
        if (bootstrap.initialGame) return bootstrap.initialGame;
        try {
            return localStorage.getItem('fp_arcade_last_game') || 'kinzip';
        } catch {
            return 'kinzip';
        }
    });

    useEffect(() => {
        try {
            localStorage.setItem('fp_arcade_last_game', activeGame);
        } catch {
            // Non-blocking fallback
        }
    }, [activeGame]);
    const [difficulty, setDifficulty] = useState(bootstrap.initialDifficulty || 'medium');
    const [mode, setMode] = useState(bootstrap.raceToken ? 'live_race' : 'daily');
    const [puzzle, setPuzzle] = useState(isGuest ? (bootstrap.initialPuzzle || null) : null);
    const [ghost, setGhost] = useState(
        isGuest && challenger
            ? { fullName: challenger.name, elapsedMs: challenger.elapsedMs || 60000 }
            : null
    );
    const [personalBest, setPersonalBest] = useState({ score: 0, elapsedMs: 0 });
    const [loadingPuzzle, setLoadingPuzzle] = useState(!isGuest);
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
    const [engagementFunnel, setEngagementFunnel] = useState(null);

    const [victoryResult, setVictoryResult] = useState(null);
    const [guestResult, setGuestResult] = useState(null);
    const [lastReplay, setLastReplay] = useState([]);
    const [sharingToFeed, setSharingToFeed] = useState(false);
    const [showHowToPlay, setShowHowToPlay] = useState(false);
    const [showShareModal, setShowShareModal] = useState(false);
    const [shareCopied, setShareCopied] = useState(false);

    const lastProgressSentRef = useRef(0);
    const guestStartedRef = useRef(false);

    const trackPublicEvent = async (eventType, extra = {}) => {
        if (!isGuest) return null;
        try {
            const res = await fetch(bootstrap.trackUrl || '/api/arcade/public/track', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    eventType,
                    game: activeGame,
                    difficulty,
                    challengeToken: bootstrap.challengeToken || '',
                    seedKey: puzzle?.seedKey || bootstrap.dailyDate || '',
                    ...extra,
                }),
            });
            return await res.json();
        } catch {
            return null;
        }
    };

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
        if (isGuest) return;
        loadPuzzle(activeGame, difficulty, mode);
    }, [activeGame, difficulty, mode, isGuest]);

    useEffect(() => {
        if (isGuest) return;
        loadLeagues(period, includeConnected);
    }, [period, includeConnected, isGuest]);

    useEffect(() => {
        if (isGuest) return;
        apiFetch('/api/arcade/engagements')
            .then((res) => {
                if (res?.status === 'success' && res.data?.funnel) {
                    setEngagementFunnel(res.data.funnel);
                }
            })
            .catch(() => {});
    }, [isGuest]);

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

        if (isGuest && pct > 0 && !guestStartedRef.current) {
            guestStartedRef.current = true;
            trackPublicEvent('start');
        }

        if (!isGuest && raceToken && (pct === 100 || Math.abs(pct - lastProgressSentRef.current) >= 10)) {
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

        if (isGuest) {
            try {
                const res = await trackPublicEvent('complete', {
                    elapsedMs: payload.elapsedMs,
                    hintsUsed: payload.hintsUsed,
                    mistakesCount: payload.mistakesCount,
                    submission: payload.submission,
                });
                const verifiedScore = res?.data?.score || Math.max(400, 1400 - Math.round((payload.elapsedMs || 0) / 150));
                setGuestResult({
                    score: verifiedScore,
                    elapsedMs: payload.elapsedMs,
                    beatChallenger: Boolean(res?.data?.beatChallenger),
                    challengerScore: res?.data?.challengerScore || challenger?.score || 0,
                });
            } finally {
                setSubmitting(false);
            }
            return;
        }

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

    const getShareMeta = () => {
        const origin = typeof window !== 'undefined' ? window.location.origin : '';
        const token = victoryResult?.challengeToken || bootstrap.challengeToken || '';
        const path = token
            ? `/arcade/share/${encodeURIComponent(token)}`
            : `/play/${encodeURIComponent(activeGame)}?difficulty=${encodeURIComponent(difficulty)}`;
        const cardPath = token
            ? `/arcade/card/${encodeURIComponent(token)}.jpg?v=3&difficulty=${encodeURIComponent(difficulty)}`
            : `/arcade/card/game-${encodeURIComponent(activeGame)}.jpg?v=3&difficulty=${encodeURIComponent(difficulty)}`;
        const shareUrl = `${origin}${path}`;
        const cardUrl = `${origin}${cardPath}`;
        const shareTitle = `Play ${activeGameMeta.name} (${activeGameMeta.tag}) — FamilyPlatform Arcade`;
        const shareText = token
            ? `🏆 Think you can beat my ${activeGameMeta.name} score on FamilyPlatform? Tap to race my ghost!`
            : `🎮 Try today's ${activeGameMeta.name} (${activeGameMeta.tag}) puzzle on FamilyPlatform Arcade!`;

        return { shareUrl, cardUrl, shareTitle, shareText };
    };

    const handleCopyShareLink = async () => {
        const { shareUrl } = getShareMeta();
        try {
            await navigator.clipboard.writeText(shareUrl);
            setShareCopied(true);
            notifyToast(`${activeGameMeta.name} link copied!`, 'success');
            window.setTimeout(() => setShareCopied(false), 2000);
        } catch {
            notifyToast(`Share link: ${shareUrl}`, 'info');
        }
    };

    const handleNativeShare = async () => {
        const { shareUrl, shareTitle, shareText } = getShareMeta();
        if (typeof navigator !== 'undefined' && typeof navigator.share === 'function') {
            try {
                await navigator.share({ title: shareTitle, text: shareText, url: shareUrl });
            } catch {
                // Cancelled
            }
        }
    };

    const ghostPct = computeGhostProgress(ghost, puzzle?.parMs, playerElapsedMs);
    const activeGameMeta = GAMES.find((g) => g.slug === activeGame) || GAMES[0];

    return (
        <div className={styles.arcadeShell}>
            {/* Hero Header */}
            <header className={styles.hero}>
                <div className={styles.heroText}>
                    <span className={styles.heroEyebrow}>
                        {isGuest
                            ? (challenger ? `${challenger.name.toUpperCase()} CHALLENGED YOU` : 'GUEST CHALLENGE • PLAY FREE')
                            : `FAMILY ARCADE • DAILY SEED ${bootstrap.dailyDate || ''}`}
                    </span>
                    <h1 className={styles.heroTitle}>
                        {isGuest && challenger
                            ? `Can You Beat ${challenger.name}’s ${challenger.score} pts in ${activeGameMeta.name}?`
                            : 'Compete, Outsmart & Claim the Family Crown'}
                    </h1>
                    <p className={styles.heroSub}>
                        {isGuest
                            ? 'Play the exact same puzzle right now in your browser — no sign-up required. Race their ghost and see how you compare!'
                            : 'Play today’s shared family puzzle, race the ATG Algorithm Bot or a relative’s ghost, and climb the Family Cup league table.'}
                    </p>
                </div>
                <TrophyStage3D champions={champions} />
            </header>

            {/* 7-Game Selector (hidden in single-challenge guest mode) */}
            {!isGuest && (
                <nav className={styles.gameNav} data-scroll-affordance="true" aria-label="Select Family Arcade game">
                    {GAMES.map((g) => (
                        <button
                            key={g.slug}
                            type="button"
                            className={`${styles.gameNavBtn} ${activeGame === g.slug ? styles.gameNavBtnActive : ''}`}
                            onClick={() => {
                            if (isGuest) {
                                window.location.href = `/play/${g.slug}`;
                                return;
                            }
                            setActiveGame(g.slug);
                        }}
                            aria-current={activeGame === g.slug ? 'page' : undefined}
                        >
                            <i className={`bi ${g.icon}`} aria-hidden="true" />
                            <span className={styles.gameNavName}>{g.name}</span>
                            <span className={styles.gameNavTag}>{g.tag}</span>
                        </button>
                    ))}
                </nav>
            )}

            {/* Main 2-Column Workspace */}
            <div className={styles.workspace}>
                <section className={styles.stageCard} aria-label={`${activeGameMeta.name} Arena`}>
                    {/* Mode & Difficulty Toolbar */}
                    <div className={styles.toolbar}>
                        {!isGuest ? (
                            <>
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

                                <div className={styles.toolbarActions}>
                                    <button type="button" className={styles.raceBtn} onClick={handleCreateLiveRace} title="Create a live head-to-head race link for family members">
                                        <span className={styles.liveDot} aria-hidden="true" />
                                        <i className="bi bi-lightning-charge-fill" aria-hidden="true" />
                                        <span>Race Live</span>
                                    </button>
                                    <button
                                        type="button"
                                        className={styles.howToBtn}
                                        onClick={() => {
                                            setShowHowToPlay(true);
                                            trackPublicEvent('how_to_play');
                                        }}
                                        aria-label={`How to play ${activeGameMeta.name}`}
                                    >
                                        <i className="bi bi-play-circle-fill" aria-hidden="true" />
                                        <span>How to Play</span>
                                    </button>
                                    <button
                                        type="button"
                                        className={styles.shareBtn}
                                        onClick={() => {
                                            setShareCopied(false);
                                            setShowShareModal(true);
                                        }}
                                        aria-label={`Share ${activeGameMeta.name}`}
                                    >
                                        <i className="bi bi-share-fill" aria-hidden="true" />
                                        <span>Share<span className={styles.shareLabelGame}> {activeGameMeta.name}</span></span>
                                    </button>
                                </div>
                            </>
                        ) : (
                            <>
                                <div className={styles.metaBadge}>
                                    <i className={`bi ${activeGameMeta.icon} text-warning`} aria-hidden="true" />
                                    <span><strong>{activeGameMeta.name}</strong> &bull; {activeGameMeta.tag}</span>
                                </div>
                                <div className={styles.toolbarActions}>
                                    <button
                                        type="button"
                                        className={styles.howToBtn}
                                        onClick={() => {
                                            setShowHowToPlay(true);
                                            trackPublicEvent('how_to_play');
                                        }}
                                        aria-label={`How to play ${activeGameMeta.name}`}
                                    >
                                        <i className="bi bi-play-circle-fill" aria-hidden="true" />
                                        <span>How to Play</span>
                                    </button>
                                    <button
                                        type="button"
                                        className={styles.shareBtn}
                                        onClick={() => {
                                            setShareCopied(false);
                                            setShowShareModal(true);
                                        }}
                                        aria-label={`Share ${activeGameMeta.name}`}
                                    >
                                        <i className="bi bi-share-fill" aria-hidden="true" />
                                        <span>Share<span className={styles.shareLabelGame}> {activeGameMeta.name}</span></span>
                                    </button>
                                </div>
                            </>
                        )}
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

                    {/* First-step How to Play Nudge */}
                    {playerProgress === 0 && !loadingPuzzle && (
                        <div className={styles.howToNudge}>
                            <span>
                                <i className="bi bi-lightbulb-fill text-warning me-1" aria-hidden="true" />
                                <strong>Not sure how to play {activeGameMeta.name}?</strong> Watch the platform play a quick step-by-step demo.
                            </span>
                            <button type="button" className={styles.howToNudgeBtn} onClick={() => setShowHowToPlay(true)}>
                                <i className="bi bi-play-fill" aria-hidden="true" /> Watch Platform Play
                            </button>
                        </div>
                    )}

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
                            {activeGame === 'wordie' && (
                                <WordieGame puzzle={puzzle} onProgress={handleProgress} onComplete={handleComplete} submitting={submitting} />
                            )}
                            {activeGame === 'cheeseboard' && (
                                <CheeseboardGame puzzle={puzzle} onProgress={handleProgress} onComplete={handleComplete} submitting={submitting} />
                            )}
                            {activeGame === 'chess' && (
                                <FamilyChessGame puzzle={puzzle} onProgress={handleProgress} onComplete={handleComplete} submitting={submitting} />
                            )}
                            {activeGame === 'ayo' && (
                                <AyoGame puzzle={puzzle} onProgress={handleProgress} onComplete={handleComplete} submitting={submitting} />
                            )}
                            {activeGame === 'minesweeper' && (
                                <MinesweeperGame puzzle={puzzle} onProgress={handleProgress} onComplete={handleComplete} submitting={submitting} />
                            )}
                        </>
                    )}
                </section>

                {isGuest ? (
                    <aside className={styles.leaguePanel} aria-label="Why join FamilyPlatform Arcade">
                        <h2 className={styles.leagueTitle}>
                            <i className="bi bi-stars text-warning me-2" aria-hidden="true" />
                            Save Your Score &amp; Join the Family Cup
                        </h2>
                        <p className="small mb-3" style={{ color: 'var(--arc-muted)', lineHeight: 1.5 }}>
                            Members unlock all 7 daily brain games, live family races, weekly Family Cup trophies, and dynamic crowns on their Family Tree.
                        </p>
                        {challenger && (
                            <div className={styles.ghostBar} style={{ marginBottom: '14px' }}>
                                <div>
                                    <div className="small fw-bold">{challenger.name}’s Target</div>
                                    <div className="h5 fw-bold mb-0 text-warning">
                                        {challenger.score.toLocaleString()} pts &bull; {formatElapsedMs(challenger.elapsedMs)}
                                    </div>
                                </div>
                            </div>
                        )}
                        <a
                            href={bootstrap.registerUrl || '/register'}
                            className={`${styles.ctrlBtn} ${styles.ctrlBtnPrimary} text-decoration-none w-100 justify-content-center`}
                            onClick={() => trackPublicEvent('signup_click')}
                        >
                            <i className="bi bi-person-plus-fill" aria-hidden="true" />
                            <span>Join Free &amp; Challenge Back</span>
                        </a>
                    </aside>
                ) : (
                    <div className={styles.sideColumn}>
                        <LeagueTablesPanel
                            leagues={leagues}
                            champions={champions}
                            period={period}
                            includeConnected={includeConnected}
                            onChangePeriod={setPeriod}
                            onToggleConnected={setIncludeConnected}
                            currentUserId={bootstrap.userId}
                        />
                        {engagementFunnel && (engagementFunnel.views > 0 || engagementFunnel.starts > 0 || engagementFunnel.completions > 0) && (
                            <section className={styles.leaguePanel} aria-label="Your shared challenge engagement">
                                <h2 className={styles.leagueTitle} style={{ fontSize: '1rem', marginBottom: '10px' }}>
                                    <i className="bi bi-graph-up-arrow text-warning me-2" aria-hidden="true" />
                                    Shared Challenge Engagement
                                </h2>
                                <div className={styles.engagementGrid}>
                                    <div className={styles.metaBadge} style={{ flexDirection: 'column', alignItems: 'flex-start', padding: '10px' }}>
                                        <span className="small" style={{ color: 'var(--arc-muted)' }}>Opens</span>
                                        <strong className="fs-5">{engagementFunnel.views}</strong>
                                    </div>
                                    <div className={styles.metaBadge} style={{ flexDirection: 'column', alignItems: 'flex-start', padding: '10px' }}>
                                        <span className="small" style={{ color: 'var(--arc-muted)' }}>Played</span>
                                        <strong className="fs-5">{engagementFunnel.starts}</strong>
                                    </div>
                                    <div className={styles.metaBadge} style={{ flexDirection: 'column', alignItems: 'flex-start', padding: '10px' }}>
                                        <span className="small" style={{ color: 'var(--arc-muted)' }}>Finished</span>
                                        <strong className="fs-5">{engagementFunnel.completions}</strong>
                                    </div>
                                    <div className={styles.metaBadge} style={{ flexDirection: 'column', alignItems: 'flex-start', padding: '10px' }}>
                                        <span className="small" style={{ color: 'var(--arc-muted)' }}>Beat You</span>
                                        <strong className="fs-5">{engagementFunnel.beatChallenger}</strong>
                                    </div>
                                    <div className={styles.metaBadge} style={{ flexDirection: 'column', alignItems: 'flex-start', padding: '10px' }}>
                                        <span className="small" style={{ color: 'var(--arc-muted)' }}>Demo Views</span>
                                        <strong className="fs-5">{engagementFunnel.howToPlay}</strong>
                                    </div>
                                    <div className={styles.metaBadge} style={{ flexDirection: 'column', alignItems: 'flex-start', padding: '10px' }}>
                                        <span className="small" style={{ color: 'var(--arc-muted)' }}>Sign-Up Clicks</span>
                                        <strong className="fs-5 text-warning">{engagementFunnel.signupClicks}</strong>
                                    </div>
                                </div>
                            </section>
                        )}
                    </div>
                )}
            </div>

            {guestResult && (
                <div className={styles.modalBackdrop} role="dialog" aria-modal="true" aria-labelledby="guest-victory-title" onClick={() => setGuestResult(null)}>
                    <div className={styles.modalCard} onClick={(e) => e.stopPropagation()}>
                        <div className={styles.modalHeader}>
                            <div>
                                <span className={styles.heroEyebrow} style={{ color: '#b45309' }}>
                                    {guestResult.beatChallenger ? 'YOU BEAT THE CHALLENGER!' : 'PUZZLE COMPLETE!'}
                                </span>
                                <h2 id="guest-victory-title" className={styles.modalTitle}>
                                    You Scored {guestResult.score.toLocaleString()} pts ({formatElapsedMs(guestResult.elapsedMs)})
                                </h2>
                            </div>
                            <button type="button" className={styles.closeBtn} onClick={() => setGuestResult(null)} aria-label="Close result">
                                <i className="bi bi-x-lg" aria-hidden="true" />
                            </button>
                        </div>
                        <p className="mb-3" style={{ color: 'var(--arc-muted)' }}>
                            {guestResult.challengerScore > 0
                                ? (guestResult.beatChallenger
                                    ? `Amazing! You beat ${challenger?.name || 'the challenger'}’s ${guestResult.challengerScore.toLocaleString()} pts.`
                                    : `${challenger?.name || 'The challenger'} scored ${guestResult.challengerScore.toLocaleString()} pts — create a free account to save your score and send a rematch!`)
                                : 'Create a free account to save your score, unlock all 7 daily games, and challenge your family!'}
                        </p>
                        <div className={styles.modalActions}>
                            <a
                                href={`${bootstrap.registerUrl || '/register'}&guest_score=${guestResult.score || 0}`}
                                className={`${styles.ctrlBtn} ${styles.ctrlBtnPrimary} text-decoration-none`}
                                onClick={() => trackPublicEvent('signup_click')}
                            >
                                <i className="bi bi-person-plus-fill" aria-hidden="true" />
                                <span>Join Free to Save Score &amp; Rematch</span>
                            </a>
                            <a
                                href={`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(bootstrap.shareUrl || window.location.href)}`}
                                target="_blank"
                                rel="noopener noreferrer"
                                className={styles.ctrlBtn}
                            >
                                <i className="bi bi-facebook" aria-hidden="true" />
                                <span>Facebook</span>
                            </a>
                            <a
                                href={`https://api.whatsapp.com/send?text=${encodeURIComponent(`🏆 I scored ${guestResult.score.toLocaleString()} pts in ${activeGameMeta.name} (${formatElapsedMs(guestResult.elapsedMs)}) on FamilyPlatform! Can you beat me? ${bootstrap.shareUrl || window.location.href}`)}`}
                                target="_blank"
                                rel="noopener noreferrer"
                                className={styles.ctrlBtn}
                            >
                                <i className="bi bi-whatsapp" aria-hidden="true" />
                                <span>WhatsApp</span>
                            </a>
                            <button
                                type="button"
                                className={styles.ctrlBtn}
                                onClick={async () => {
                                    const txt = `🏆 ${activeGameMeta.name} — ${guestResult.score.toLocaleString()} pts in ${formatElapsedMs(guestResult.elapsedMs)}!\n${bootstrap.shareUrl || window.location.href}`;
                                    try {
                                        await navigator.clipboard.writeText(txt);
                                        notifyToast('Spoiler-free result copied!', 'success');
                                    } catch {
                                        // Silent
                                    }
                                }}
                            >
                                <i className="bi bi-clipboard-check" aria-hidden="true" />
                                <span>Copy Result</span>
                            </button>
                            <button type="button" className={styles.ctrlBtn} onClick={() => setGuestResult(null)}>
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            )}

            {showShareModal && (() => {
                const { shareUrl, cardUrl, shareText } = getShareMeta();
                const whatsappHref = `https://api.whatsapp.com/send?text=${encodeURIComponent(`${shareText}\n\n${shareUrl}`)}`;
                const facebookHref = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shareUrl)}`;
                const smsHref = `sms:?&body=${encodeURIComponent(`${shareText} ${shareUrl}`)}`;
                const xHref = `https://twitter.com/intent/tweet?text=${encodeURIComponent(shareText)}&url=${encodeURIComponent(shareUrl)}`;
                const hasNativeShare = typeof navigator !== 'undefined' && typeof navigator.share === 'function';

                return (
                    <div className={styles.modalBackdrop} role="dialog" aria-modal="true" aria-labelledby="share-game-title" onClick={() => setShowShareModal(false)}>
                        <div className={styles.modalCard} onClick={(e) => e.stopPropagation()}>
                            <div className={styles.modalHeader}>
                                <div>
                                    <span className={`${styles.heroEyebrow} ${styles.shareModalEyebrow}`}>SHARE {activeGameMeta.name.toUpperCase()}</span>
                                    <h2 id="share-game-title" className={styles.modalTitle}>Invite Friends &amp; Family to Play {activeGameMeta.name}</h2>
                                </div>
                                <button type="button" className={styles.closeBtn} onClick={() => setShowShareModal(false)} aria-label="Close share dialog">
                                    <i className="bi bi-x-lg" aria-hidden="true" />
                                </button>
                            </div>

                            <div className={styles.sharePreviewBox}>
                                <img
                                    src={cardUrl}
                                    alt={`${activeGameMeta.name} (${activeGameMeta.tag}) — ${difficulty} difficulty share card preview`}
                                    className={styles.sharePreviewImg}
                                />
                                <div className={styles.shareUrlRow}>
                                    <span className={styles.shareUrlText} title={shareUrl}>{shareUrl}</span>
                                    <button type="button" className={styles.demoCtrlBtn} onClick={handleCopyShareLink}>
                                        <i className={`bi ${shareCopied ? 'bi-check2' : 'bi-clipboard'}`} aria-hidden="true" />
                                        <span>{shareCopied ? 'Copied!' : 'Copy'}</span>
                                    </button>
                                </div>
                            </div>

                            <div className={styles.shareChannelGrid}>
                                <a href={whatsappHref} target="_blank" rel="noopener noreferrer" className={`${styles.shareChannelBtn} ${styles.shareWhatsapp}`}>
                                    <i className="bi bi-whatsapp" aria-hidden="true" />
                                    <span>WhatsApp</span>
                                </a>
                                <a href={facebookHref} target="_blank" rel="noopener noreferrer" className={`${styles.shareChannelBtn} ${styles.shareFacebook}`}>
                                    <i className="bi bi-facebook" aria-hidden="true" />
                                    <span>Facebook</span>
                                </a>
                                <a href={smsHref} className={`${styles.shareChannelBtn} ${styles.shareMessages}`}>
                                    <i className="bi bi-chat-dots-fill" aria-hidden="true" />
                                    <span>Messages / SMS</span>
                                </a>
                                <a href={xHref} target="_blank" rel="noopener noreferrer" className={`${styles.shareChannelBtn} ${styles.shareX}`}>
                                    <i className="bi bi-twitter-x" aria-hidden="true" />
                                    <span>X / Twitter</span>
                                </a>
                            </div>

                            <div className={styles.modalActions} style={{ marginTop: '12px', justifyContent: hasNativeShare ? 'space-between' : 'flex-end' }}>
                                {hasNativeShare && (
                                    <button type="button" className={styles.ctrlBtn} onClick={handleNativeShare}>
                                        <i className="bi bi-box-arrow-up" aria-hidden="true" />
                                        <span>More Apps…</span>
                                    </button>
                                )}
                                <button type="button" className={styles.ctrlBtn} onClick={() => setShowShareModal(false)}>
                                    <span>Done</span>
                                </button>
                            </div>
                        </div>
                    </div>
                );
            })()}

            {showHowToPlay && (
                <HowToPlayModal
                    initialGame={activeGame}
                    onClose={() => setShowHowToPlay(false)}
                />
            )}

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
