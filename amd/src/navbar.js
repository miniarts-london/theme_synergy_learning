// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Shrinks the top bar once the page is scrolled (the sizes are in scss/navbar.scss).
 *
 * @module     theme_synergylearning/navbar
 * @copyright  2026 Kaori Nishimura
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

export const init = () => {
    // Watching a 1px marker 24px down the page is cheaper than a scroll listener.
    // Once it scrolls out of view, the bar shrinks.
    const marker = document.createElement('div');
    marker.setAttribute('aria-hidden', 'true');
    marker.style.cssText = 'position:absolute;top:24px;left:0;width:1px;height:1px;pointer-events:none;';
    document.body.prepend(marker);

    new IntersectionObserver(([entry]) => {
        document.body.classList.toggle('sl-navbar-compact', !entry.isIntersecting);
    }).observe(marker);
};
