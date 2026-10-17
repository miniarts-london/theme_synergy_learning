<?php
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

namespace theme_synergylearning;

use core\hook\navigation\primary_extend;

/**
 * Hook callbacks for the Synergy Learning theme.
 *
 * @package    theme_synergylearning
 * @copyright  2026 Kaori Nishimura
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class hook_callbacks {
    /**
     * Adjusts the primary navigation (the top menu) while this theme is in use.
     *
     * - Adds "Course catalogue", which goes to the catalogue section of the site home (and, on the site home,
     *   scrolls to it: see amd/src/frontpage.js). It is added here rather than as an admin "Custom menu item",
     *   so every site that installs the theme gets it.
     * - Removes "Home" for visitors who are not logged in (and guests): the site home is the log in page for them,
     *   and the logo already links to it.
     *
     * Hook callbacks run whichever theme is active, so nothing happens unless this theme is in use.
     *
     * @param primary_extend $hook The primary navigation hook.
     */
    public static function primary_extend(primary_extend $hook): void {
        global $PAGE;

        if ($PAGE->theme->name !== 'synergylearning') {
            return;
        }
        $primary = $hook->get_primaryview();

        if (!isloggedin() || isguestuser()) {
            $home = $primary->get('home');
            if ($home) {
                $home->remove();
            }
        }

        // The redirect=0 parameter stops Moodle sending logged-in users whose home page is the dashboard away from the site home.
        $primary->add(
            get_string('catalogue_title', 'theme_synergylearning'),
            new \core\url('/', ['redirect' => 0], 'catalogue'),
            \navigation_node::TYPE_ROOTNODE,
            null,
            'sl-catalogue'
        );
    }
}
