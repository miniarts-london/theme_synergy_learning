// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Navigation bar: tall at the top of the page, compact once the page scrolls.
 *
 * Adds body.sl-navbar-compact when the page is scrolled; scss/navbar.scss does the rest. Boost's own scroll class
 * only appears after a full screen of scrolling, so the theme sets its own.
 *
 * @module     theme_synergylearning/navbar
 * @copyright  2026 Kaori Nishimura
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

const COMPACT_CLASS = 'sl-navbar-compact';

// How far the page must scroll, in pixels, before the bar shrinks.
const THRESHOLD = 24;

/**
 * Start watching the scroll position.
 */
export const init = () => {
    const body = document.body;
    if (body.dataset.slNavbar) {
        return;
    }
    body.dataset.slNavbar = 'true';

    let queued = false;
    const update = () => {
        body.classList.toggle(COMPACT_CLASS, window.scrollY > THRESHOLD);
        queued = false;
    };

    window.addEventListener('scroll', () => {
        if (!queued) {
            queued = true;
            window.requestAnimationFrame(update);
        }
    }, {passive: true});

    update();
};
