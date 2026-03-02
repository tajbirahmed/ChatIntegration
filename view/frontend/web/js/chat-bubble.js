/**
 * BS23 Chat Integration – Chat Bubble JS
 *
 * Standalone vanilla JS. No jQuery, no RequireJS, no external dependencies.
 * Loaded via layout XML; uses readyState guard so it works whether Magento
 * places the script in <head>, defers it, or moves it to end-of-body.
 *
 * Responsibilities:
 *   1. Toggle .is-active on the wrapper when the bubble button is clicked.
 *   2. Update aria-expanded / aria-hidden for accessibility.
 *   3. Restore / remove tab-index on hidden links.
 *   4. Close panel on outside click or Escape key.
 *   5. Position circular-layout items using CSS custom properties
 *      (--bs23-ring-x, --bs23-ring-y) computed via trigonometry.
 *
 * The actual visual transitions are 100 % CSS-driven.
 * Minified target: < 1.5 kb.
 */
(function () {
    'use strict';

    function init() {
        /* ── Element references ─────────────────────────────────────────── */
        var wrapper = document.getElementById('bs23ChatWrapper');
        var btn     = document.getElementById('bs23BubbleBtn');
        var panel   = document.getElementById('bs23ChatOptions');

        /* Guard – single-entry bubbles have no toggle logic */
        if (!wrapper || !btn || !panel) { return; }

        var layout = (wrapper.getAttribute('data-layout') || 'vertical').trim();
        var count  = parseInt(wrapper.getAttribute('data-count') || '0', 10);

        /* ── Smart circular ring positioning ─────────────────────────────────
         * Measures the bubble's real pixel position, computes the direction
         * with the most available viewport space (atan2), and fans icons in a
         * dynamic arc centered on that inward direction — so icons never clip
         * outside the viewport edge regardless of where the bubble is placed.
         *
         * Arc span = 30° × item count, capped at 150°.
         * Recalculated on window resize / orientation change.
         * ─────────────────────────────────────────────────────────────────── */
        function positionCircularItems() {
            if (layout !== 'circular' || count < 2) { return; }

            var items    = panel.querySelectorAll('.bs23-chat-option');
            var bubblePx = parseInt(
                getComputedStyle(wrapper).getPropertyValue('--bs23-bubble-size'), 10
            ) || 56;
            var radius   = Math.max(bubblePx * 1.6, 80);

            var rect = wrapper.getBoundingClientRect();
            var cx   = rect.left + rect.width  / 2;
            var cy   = rect.top  + rect.height / 2;
            var W    = window.innerWidth  || document.documentElement.clientWidth;
            var H    = window.innerHeight || document.documentElement.clientHeight;

            // Negate normalised position so the angle points AWAY from the corner
            var centerAngle = Math.atan2(-(cy / H * 2 - 1), -(cx / W * 2 - 1));

            var span       = Math.min(count * (Math.PI / 6), Math.PI * 5 / 6);
            var startAngle = centerAngle - span / 2;

            items.forEach(function (item, i) {
                var angle = count > 1
                    ? startAngle + (i / (count - 1)) * span
                    : centerAngle;
                item.style.setProperty('--bs23-ring-x', Math.round(Math.cos(angle) * radius) + 'px');
                item.style.setProperty('--bs23-ring-y', Math.round(Math.sin(angle) * radius) + 'px');
            });
        }

        window.addEventListener('resize', positionCircularItems);

        /* ── Open / close helpers ─────────────────────────────────────────── */
        function openPanel() {
            wrapper.classList.add('is-active');
            btn.setAttribute('aria-expanded', 'true');
            panel.setAttribute('aria-hidden', 'false');

            panel.querySelectorAll('.bs23-chat-link').forEach(function (link) {
                link.setAttribute('tabindex', '0');
            });
        }

        function closePanel() {
            wrapper.classList.remove('is-active');
            btn.setAttribute('aria-expanded', 'false');
            panel.setAttribute('aria-hidden', 'true');

            panel.querySelectorAll('.bs23-chat-link').forEach(function (link) {
                link.setAttribute('tabindex', '-1');
            });
        }

        /* ── Toggle ──────────────────────────────────────────────────────── */
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            if (wrapper.classList.contains('is-active')) {
                closePanel();
            } else {
                openPanel();
            }
        });

        /* ── Close on outside click ──────────────────────────────────────── */
        document.addEventListener('click', function (e) {
            if (wrapper && !wrapper.contains(e.target)) {
                closePanel();
            }
        });

        /* ── Close on Escape key; return focus to trigger button ─────────── */
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && wrapper.classList.contains('is-active')) {
                closePanel();
                btn.focus();
            }
        });

        /* ── Initialise ─────────────────────────────────────────────────── */
        positionCircularItems();
    }

    /* Run init() once the DOM is ready, regardless of when this script loads:
     * - 'loading'     → script is in <head>, DOM not yet parsed → wait for event
     * - 'interactive' → deferred / bottom-of-body script, DOM is ready → run now
     * - 'complete'    → async script loaded after page fully parsed → run now    */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

}());
