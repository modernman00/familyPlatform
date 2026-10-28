/**
 * FamilyPlatform Universal Icon & Button Explanatory Tooltip Engine
 * Exported ES module for Webpack bundling.
 */
import '../../../../public/js/family-tooltip.js';

export function initFamilyTooltip() {
    if (window.FamilyTooltip && typeof window.FamilyTooltip.scan === 'function') {
        window.FamilyTooltip.scan();
    }
}

export default initFamilyTooltip;
