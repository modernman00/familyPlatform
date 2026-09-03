/**
 * Native Web Haptics Engine
 * Provides subtle tactile feedback for App-Store parity via Web Vibration API
 */

'use strict';

export const HAPTIC_PATTERNS = {
  selection: 10,       // Tab switches, subtle toggles
  impact: 15,          // Reactions, like clicks, button presses
  success: [10, 40, 15], // Saved post, completed action
  warning: [20, 60, 20]  // Dismissals, alerts
};

/**
 * Trigger subtle haptic vibration safely
 * Gracefully no-ops on devices without vibration support (e.g. iOS Safari)
 * 
 * @param {'selection' | 'impact' | 'success' | 'warning'} type 
 */
export function triggerHaptic(type = 'selection') {
  if (typeof navigator !== 'undefined' && 'vibrate' in navigator && typeof navigator.vibrate === 'function') {
    try {
      const pattern = HAPTIC_PATTERNS[type] || 10;
      navigator.vibrate(pattern);
    } catch {
      // Ignore vibration errors silently
    }
  }
}

export default triggerHaptic;
