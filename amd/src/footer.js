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
 * Footer enhancements: cookie preferences and the current year.
 *
 * @module     theme_synergylearning/footer
 * @copyright  2026 Kaori Nishimura
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {Modal} from 'bootstrap';
import {add as addToast} from 'core/toast';
import {getString} from 'core/str';

const SELECTORS = {
    cookieButton: '.btn-sl-cookie, [data-action="sl-cookie-settings"]',
    cookieForm: '[data-cookie-form]',
    cookieModal: '#cookie-modal',
    analytics: '#cookie-analytics',
    year: '[data-year]',
};

const STORAGE_KEY = 'ml-cookie-prefs';

/**
 * Run one feature without letting an error stop the others.
 *
 * @param {Function} feature The feature to run.
 */
const safely = (feature) => {
    try {
        feature();
    } catch (error) {
        window.console.error(error);
    }
};

/**
 * Cookie preferences pop-up, saved per browser.
 *
 * The Cookie settings button is a normal link to the site policies, so it still works without JavaScript.
 * Here it opens the pop-up instead.
 */
const initCookies = () => {
    const modalElement = document.querySelector(SELECTORS.cookieModal);
    const form = modalElement?.querySelector(SELECTORS.cookieForm);
    const analytics = form?.querySelector(SELECTORS.analytics);
    if (!analytics) {
        return;
    }

    // Move the pop-up to the end of <body> so no footer styles (overflow, stacking) can clip it.
    document.body.append(modalElement);
    const modal = Modal.getOrCreateInstance(modalElement);

    const loadSaved = () => {
        try {
            const saved = JSON.parse(localStorage.getItem(STORAGE_KEY) || 'null');
            analytics.checked = !!saved?.analytics;
        } catch (error) {
            // Storage unavailable, so the defaults apply.
        }
    };

    // The button that opened the pop-up, so focus can go back to it on close (WCAG 2.4.3).
    let opener = null;

    document.addEventListener('click', (e) => {
        const button = e.target.closest(SELECTORS.cookieButton);
        if (!button) {
            return;
        }
        e.preventDefault();
        opener = button;
        loadSaved();
        modal.show(button);
    });

    modalElement.addEventListener('hidden.bs.modal', () => {
        opener?.focus();
        opener = null;
    });

    form.addEventListener('submit', async(e) => {
        e.preventDefault();
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify({analytics: analytics.checked, savedAt: Date.now()}));
        } catch (error) {
            // Storage unavailable: the choice applies to this page only.
        }
        modal.hide();
        const message = await getString(analytics.checked ? 'cookie_saved_on' : 'cookie_saved_off', 'theme_synergylearning');
        addToast(message);
    });
};

/**
 * Keep any [data-year] element showing the current year.
 */
const initYear = () => {
    const year = String(new Date().getFullYear());
    document.querySelectorAll(SELECTORS.year).forEach((el) => {
        el.textContent = year;
    });
};

/**
 * Initialise the footer enhancements.
 */
export const init = () => {
    safely(initCookies);
    safely(initYear);
};
