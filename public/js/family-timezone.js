/**
 * FamilyTimezone (v1.0)
 *
 * Universal client-side timezone & localized datetime engine.
 * Converts server timestamps to each family member's local device timezone.
 *
 * Zero external dependencies. Uses standard Intl.DateTimeFormat & Intl.RelativeTimeFormat.
 */
(function(window, document) {
    'use strict';

    const FamilyTimezone = {
        getUserTimezone: function() {
            try {
                return Intl.DateTimeFormat().resolvedOptions().timeZone || 'UTC';
            } catch (e) {
                return 'UTC';
            }
        },

        parseDate: function(dateInput) {
            if (!dateInput) return null;
            if (dateInput instanceof Date) return isNaN(dateInput.getTime()) ? null : dateInput;

            let s = String(dateInput).trim();
            if (!s) return null;

            // If string is YYYY-MM-DD HH:MM:SS without explicit offset:
            // Since backend default timezone is Europe/London, determine if that date is BST (+01:00) or GMT (+00:00)
            if (/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/.test(s)) {
                const parts = s.match(/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/);
                if (parts) {
                    const y = parseInt(parts[1], 10);
                    const m = parseInt(parts[2], 10) - 1;
                    const d = parseInt(parts[3], 10);
                    const hh = parseInt(parts[4], 10);
                    const mm = parseInt(parts[5], 10);
                    const ss = parseInt(parts[6], 10);

                    // Test with UTC date probe in Europe/London timezone
                    try {
                        const probeDate = new Date(Date.UTC(y, m, d, hh, mm, ss));
                        const londonParts = new Intl.DateTimeFormat('en-GB', { timeZone: 'Europe/London', timeZoneName: 'short' }).formatToParts(probeDate);
                        const tzEntry = londonParts.find(function(p) { return p.type === 'timeZoneName'; });
                        const isBst = tzEntry && (tzEntry.value === 'BST' || tzEntry.value.includes('+1'));
                        s = s.replace(' ', 'T') + (isBst ? '+01:00' : '+00:00');
                    } catch (e) {
                        s = s.replace(' ', 'T') + 'Z';
                    }
                } else {
                    s = s.replace(' ', 'T') + 'Z';
                }
            } else if (/^\d{4}-\d{2}-\d{2}$/.test(s)) {
                s = s + 'T00:00:00Z';
            }

            const d = new Date(s);
            return isNaN(d.getTime()) ? null : d;
        },

        format: function(dateInput, formatType) {
            const date = this.parseDate(dateInput);
            if (!date) return String(dateInput || '');

            const tz = this.getUserTimezone();
            formatType = formatType || 'datetime';

            try {
                if (formatType === 'relative') {
                    return this.formatRelative(date);
                }

                let options = {};
                switch (formatType) {
                    case 'date':
                        options = { timeZone: tz, day: 'numeric', month: 'short', year: 'numeric' };
                        break;
                    case 'time':
                        options = { timeZone: tz, hour: '2-digit', minute: '2-digit', timeZoneName: 'short' };
                        break;
                    case 'full':
                        options = { timeZone: tz, weekday: 'short', day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit', timeZoneName: 'short' };
                        break;
                    case 'datetime':
                    default:
                        options = { timeZone: tz, day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit', timeZoneName: 'short' };
                        break;
                }

                return new Intl.DateTimeFormat(navigator.language || 'en-GB', options).format(date);
            } catch (e) {
                return date.toLocaleString();
            }
        },

        formatRelative: function(date) {
            const now = new Date();
            const elapsedSec = Math.round((date.getTime() - now.getTime()) / 1000);

            // If Intl.RelativeTimeFormat is supported
            if (typeof Intl !== 'undefined' && Intl.RelativeTimeFormat) {
                try {
                    const rtf = new Intl.RelativeTimeFormat(navigator.language || 'en-GB', { numeric: 'auto' });
                    const absSec = Math.abs(elapsedSec);

                    if (absSec < 60) {
                        return elapsedSec < 0 ? 'just now' : 'in moments';
                    }
                    const minutes = Math.round(elapsedSec / 60);
                    if (Math.abs(minutes) < 60) {
                        return rtf.format(minutes, 'minute');
                    }
                    const hours = Math.round(minutes / 60);
                    if (Math.abs(hours) < 24) {
                        return rtf.format(hours, 'hour');
                    }
                    const days = Math.round(hours / 24);
                    if (Math.abs(days) < 30) {
                        return rtf.format(days, 'day');
                    }
                    const months = Math.round(days / 30);
                    if (Math.abs(months) < 12) {
                        return rtf.format(months, 'month');
                    }
                    const years = Math.round(days / 365);
                    return rtf.format(years, 'year');
                } catch (e) {
                    // Fall back
                }
            }

            return this.format(date, 'date');
        },

        localizeAll: function(root) {
            root = root || document;
            const elements = root.querySelectorAll('.local-time, [data-utc]');
            const userTz = this.getUserTimezone();

            elements.forEach(function(el) {
                const rawUtc = el.getAttribute('datetime') || el.getAttribute('data-utc');
                if (!rawUtc) return;

                const formatType = el.getAttribute('data-format') || 'datetime';
                const formatted = FamilyTimezone.format(rawUtc, formatType);

                el.textContent = formatted;
                el.setAttribute('title', rawUtc + ' UTC (' + userTz + ')');
                el.classList.add('time-localized');
            });

            // Update any timezone badges
            const tzLabels = root.querySelectorAll('.user-tz-name');
            tzLabels.forEach(function(label) {
                label.textContent = userTz;
            });
        },

        init: function() {
            const self = this;
            // Record detected timezone in cookie for server awareness
            try {
                const tz = self.getUserTimezone();
                document.cookie = 'user_timezone=' + encodeURIComponent(tz) + '; path=/; max-age=31536000; SameSite=Lax';
            } catch (e) {}

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', function() {
                    self.localizeAll();
                    self.observeMutations();
                });
            } else {
                self.localizeAll();
                self.observeMutations();
            }
        },

        observeMutations: function() {
            const self = this;
            if (typeof MutationObserver === 'undefined') return;

            const observer = new MutationObserver(function(mutations) {
                let shouldLocalize = false;
                for (let i = 0; i < mutations.length; i++) {
                    const m = mutations[i];
                    if (m.addedNodes && m.addedNodes.length > 0) {
                        shouldLocalize = true;
                        break;
                    }
                }
                if (shouldLocalize) {
                    self.localizeAll();
                }
            });

            observer.observe(document.body, { childList: true, subtree: true });
        }
    };

    window.FamilyTimezone = FamilyTimezone;
    FamilyTimezone.init();

})(window, document);
