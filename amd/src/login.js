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
 * Login page: the logo above the form links back to the site home.
 *
 * The logo comes from Moodle's own login form (Appearance > Logos > Logo), so it is linked here rather than by
 * copying that whole template into the theme.
 *
 * @module     theme_synergylearning/login
 * @copyright  2026 Kaori Nishimura
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Config from 'core/config';

export const init = () => {
    const logo = document.getElementById('logoimage');
    if (!logo || logo.closest('a')) {
        return;
    }

    const link = document.createElement('a');
    link.href = Config.wwwroot + '/';
    link.className = 'sl-login-logo';
    logo.replaceWith(link);
    link.append(logo);
};
