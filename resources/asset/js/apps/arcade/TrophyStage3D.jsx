import { useEffect, useRef, useState } from 'preact/hooks';
import styles from './ArcadeShell.module.css';

export function TrophyStage3D({ champions = [] }) {
    const mountRef = useRef(null);
    const [webglOk, setWebglOk] = useState(true);

    useEffect(() => {
        const container = mountRef.current;
        if (!container || typeof window === 'undefined') return undefined;
        if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            setWebglOk(false);
            return undefined;
        }

        let cancelled = false;
        let renderer = null;
        let animId = 0;

        import(/* webpackChunkName: "three-engine" */ 'three')
            .then((THREE) => {
                if (cancelled || !mountRef.current) return;
                try {
                    const width = container.clientWidth || 110;
                    const height = container.clientHeight || width;
                    const scene = new THREE.Scene();
                    const camera = new THREE.PerspectiveCamera(42, width / height, 0.1, 100);
                    camera.position.set(0, 1.1, 4.2);

                    renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true });
                    renderer.setSize(width, height);
                    renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));
                    container.innerHTML = '';
                    container.appendChild(renderer.domElement);

                    const ambient = new THREE.AmbientLight(0xffffff, 0.8);
                    scene.add(ambient);

                    const keyLight = new THREE.DirectionalLight(0xfbbf24, 2.2);
                    keyLight.position.set(3, 5, 4);
                    scene.add(keyLight);

                    const rimLight = new THREE.PointLight(0xef4444, 1.5, 12);
                    rimLight.position.set(-3, 2, 2);
                    scene.add(rimLight);

                    const goldMat = new THREE.MeshStandardMaterial({
                        color: 0xfbbf24,
                        metalness: 0.78,
                        roughness: 0.22,
                    });
                    const rubyMat = new THREE.MeshStandardMaterial({
                        color: 0xb91c1c,
                        metalness: 0.35,
                        roughness: 0.3,
                    });

                    const trophyGroup = new THREE.Group();

                    const base = new THREE.Mesh(new THREE.CylinderGeometry(0.62, 0.76, 0.35, 24), rubyMat);
                    base.position.y = -0.85;
                    trophyGroup.add(base);

                    const stem = new THREE.Mesh(new THREE.CylinderGeometry(0.16, 0.24, 0.65, 16), goldMat);
                    stem.position.y = -0.35;
                    trophyGroup.add(stem);

                    const cup = new THREE.Mesh(new THREE.CylinderGeometry(0.72, 0.32, 0.95, 24, 1, false), goldMat);
                    cup.position.y = 0.42;
                    trophyGroup.add(cup);

                    const crownRing = new THREE.Mesh(new THREE.TorusGeometry(0.74, 0.07, 12, 32), goldMat);
                    crownRing.rotation.x = Math.PI / 2;
                    crownRing.position.y = 0.88;
                    trophyGroup.add(crownRing);

                    scene.add(trophyGroup);

                    const sparkGeo = new THREE.OctahedronGeometry(0.07, 0);
                    const sparks = [];
                    for (let i = 0; i < 10; i++) {
                        const m = new THREE.Mesh(sparkGeo, goldMat);
                        const angle = (i / 10) * Math.PI * 2;
                        m.userData = { angle, radius: 1.25 + (i % 2) * 0.25, y: -0.2 + (i % 3) * 0.45 };
                        scene.add(m);
                        sparks.push(m);
                    }

                    let t = 0;
                    const animate = () => {
                        if (cancelled) return;
                        animId = window.requestAnimationFrame(animate);
                        t += 0.016;
                        trophyGroup.rotation.y += 0.012;
                        trophyGroup.position.y = Math.sin(t * 2) * 0.06;
                        sparks.forEach((s, idx) => {
                            const a = s.userData.angle + t * 0.7;
                            s.position.x = Math.cos(a) * s.userData.radius;
                            s.position.z = Math.sin(a) * s.userData.radius;
                            s.position.y = s.userData.y + Math.sin(t * 2.5 + idx) * 0.12;
                            s.rotation.y += 0.04;
                        });
                        renderer.render(scene, camera);
                    };
                    animate();
                } catch {
                    setWebglOk(false);
                }
            })
            .catch(() => {
                setWebglOk(false);
            });

        return () => {
            cancelled = true;
            if (animId) window.cancelAnimationFrame(animId);
            if (renderer && typeof renderer.dispose === 'function') {
                renderer.dispose();
            }
        };
    }, []);

    const cupChamp = champions.find((c) => c.badgeKey === 'family_cup_champion') || champions[0];

    return (
        <div className={styles.trophyCard}>
            <div className={styles.trophyStage}>
                {webglOk ? (
                    <div ref={mountRef} className={styles.trophyCanvasMount} aria-label="3D Family Cup Trophy" />
                ) : (
                    <div className={styles.trophyFallback} aria-hidden="true">
                        <i className="bi bi-trophy-fill" />
                    </div>
                )}
            </div>
            <div className={styles.trophyMeta}>
                <span className={styles.trophyPill}>WEEKLY FAMILY CUP</span>
                <h3 className={styles.trophyTitle}>
                    {cupChamp ? `${cupChamp.fullName}` : 'Claim the Family Crown'}
                </h3>
                <p className={styles.trophySub}>
                    {cupChamp
                        ? `${cupChamp.badgeTitle} • ${cupChamp.score.toLocaleString()} pts`
                        : 'Top the leaderboard across all 7 games to earn the Family Cup Trophy.'}
                </p>
            </div>
        </div>
    );
}
