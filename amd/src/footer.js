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
 * Cookie settings pop-up for the footer.
 *
 * The Cookie settings button is a plain link to the site policies; with JavaScript it opens the pop-up instead.
 * The choice is only remembered in this browser.
 *
 * @module     theme_synergylearning/footer
 * @copyright  2026 Kaori Nishimura
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {Modal} from 'bootstrap';
import {add as addToast} from 'core/toast';
import {getString} from 'core/str';

const STORAGE_KEY = 'ml-cookie-prefs';

// Some private browsing modes make localStorage throw, so treat it as optional.
const readPrefs = () => {
    try {
        return JSON.parse(localStorage.getItem(STORAGE_KEY)) || {};
    } catch (e) {
        return {};
    }
};

const savePrefs = (prefs) => {
    try {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(prefs));
    } catch (e) {
        // Not saved, but the toast still confirms the choice for this visit.
    }
};

export const init = () => {
    const popup = document.getElementById('cookie-modal');
    if (!popup) {
        return;
    }

    // The footer can clip it otherwise.
    document.body.append(popup);
    const modal = Modal.getOrCreateInstance(popup);
    const analytics = popup.querySelector('#cookie-analytics'); //popup content
    let openedFrom = null;

    document.addEventListener('click', (e) => {
        const button = e.target.closest('.btn-sl-cookie, [data-action="sl-cookie-settings"]');
        if (button) {
            e.preventDefault();
            openedFrom = button;
            analytics.checked = !!readPrefs().analytics;
            modal.show();
        }
    });

    // Send keyboard users back to where they were.
    popup.addEventListener('hidden.bs.modal', () => openedFrom?.focus());

    popup.querySelector('form').addEventListener('submit', async(e) => {
        e.preventDefault();
        savePrefs({analytics: analytics.checked, savedAt: Date.now()});
        modal.hide();
        addToast(await getString(analytics.checked ? 'cookie_saved_on' : 'cookie_saved_off', 'theme_synergylearning'));
    });
};
