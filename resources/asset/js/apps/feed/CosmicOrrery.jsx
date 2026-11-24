import { h } from 'preact';

/**
 * Pure GPU-composited Solar System & Spiral Galaxies backdrop layer.
 */
export default function CosmicOrrery() {
    return (
        <div className="fp-cosmos-orrery" aria-hidden="true">
            <div className="fp-cosmos-galaxy fp-cosmos-galaxy--primary" />
            <div className="fp-cosmos-galaxy fp-cosmos-galaxy--distant" />
            <div className="fp-cosmos-comet" />
            <div className="fp-cosmos-system">
                <div className="fp-cosmos-sun" />
                <div className="fp-cosmos-orbit fp-cosmos-orbit--1">
                    <div className="fp-cosmos-planet fp-cosmos-planet--mercury" />
                </div>
                <div className="fp-cosmos-orbit fp-cosmos-orbit--2">
                    <div className="fp-cosmos-planet fp-cosmos-planet--venus" />
                </div>
                <div className="fp-cosmos-orbit fp-cosmos-orbit--3">
                    <div className="fp-cosmos-planet fp-cosmos-planet--earth">
                        <div className="fp-cosmos-moon-orbit">
                            <div className="fp-cosmos-moon" />
                        </div>
                    </div>
                </div>
                <div className="fp-cosmos-orbit fp-cosmos-orbit--4">
                    <div className="fp-cosmos-planet fp-cosmos-planet--mars" />
                </div>
                <div className="fp-cosmos-orbit fp-cosmos-orbit--5">
                    <div className="fp-cosmos-planet fp-cosmos-planet--saturn">
                        <div className="fp-cosmos-saturn-ring" />
                    </div>
                </div>
                <div className="fp-cosmos-orbit fp-cosmos-orbit--6">
                    <div className="fp-cosmos-planet fp-cosmos-planet--neptune" />
                </div>
            </div>
        </div>
    );
}
