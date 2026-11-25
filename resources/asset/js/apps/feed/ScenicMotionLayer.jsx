import { h } from 'preact';

export const LIVING_SCENIC_SLUGS = ['ocean_waves', 'moving_sunset', 'family_gathering'];

/**
 * Pure GPU-composited living scenic backdrop layer for Ocean Waves,
 * Golden Sunset, and Family Fireside.
 */
export default function ScenicMotionLayer({ variant }) {
    if (!LIVING_SCENIC_SLUGS.includes(variant)) {
        return null;
    }

    return (
        <div className={`fp-scenic-layer fp-scenic-layer--${variant}`} aria-hidden="true">
            {variant === 'ocean_waves' && (
                <>
                    <div className="fp-ocean-moon" />
                    <div className="fp-ocean-shimmer" />
                    <div className="fp-ocean-wave fp-ocean-wave--back" />
                    <div className="fp-ocean-wave fp-ocean-wave--mid" />
                    <div className="fp-ocean-wave fp-ocean-wave--front" />
                </>
            )}

            {variant === 'moving_sunset' && (
                <>
                    <div className="fp-sunset-cloud fp-sunset-cloud--a" />
                    <div className="fp-sunset-cloud fp-sunset-cloud--b" />
                    <div className="fp-sunset-sun" />
                    <div className="fp-sunset-reflection" />
                    <div className="fp-sunset-horizon" />
                </>
            )}

            {variant === 'family_gathering' && (
                <>
                    <div className="fp-family-hearth-glow" />
                    <div className="fp-family-sparks" />
                    <div className="fp-family-silhouettes" />
                </>
            )}
        </div>
    );
}
