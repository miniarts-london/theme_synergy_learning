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
     *   scrolls to it: see amd/src/frontpage.js), then "My badges" (when badges are enabled) and "Events" (the
     *   upcoming events page). They are added here rather than as admin "Custom menu items", so every site that
     *   installs the theme gets the concept's menu.
     * - Moves "Site administration" to the end of the menu.
     * - Removes "Home" for visitors who are not logged in (and guests): the site home is the log in page for them,
     *   and the logo already links to it.
     *
     * Hook callbacks run whichever theme is active, so nothing happens unless this theme is in use.
     *
     * @param primary_extend $hook The primary navigation hook.
     */
    public static function primary_extend(primary_extend $hook): void {
        global $CFG, $PAGE;

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

        // My badges, when badges are enabled on the site.
        if (!empty($CFG->enablebadges)) {
            $primary->add(
                get_string('mybadges', 'theme_synergylearning'),
                new \core\url('/badges/mybadges.php'),
                \navigation_node::TYPE_ROOTNODE,
                null,
                'sl-mybadges'
            );
        }

        // Mark these items as the current one on their pages (Moodle can't match them by URL on its own).
        $path = $PAGE->has_set_url() ? $PAGE->url->get_path() : '';
        if (str_contains($path, '/calendar/')) {
            $PAGE->set_primary_active_tab('sl-events');
        } else if (str_contains($path, '/badges/')) {
            $PAGE->set_primary_active_tab('sl-mybadges');
        }

        // Events: the calendar's upcoming events page. Visitors who are not logged in are asked to log in first,
        // and Moodle then takes them on to the events page.
        $primary->add(
            get_string('events', 'calendar'),
            new \core\url('/calendar/view.php', ['view' => 'upcoming']),
            \navigation_node::TYPE_ROOTNODE,
            null,
            'sl-events'
        );

        // Move "Site administration" (admins only) to the end, after the items above.
        $siteadmin = $primary->get('siteadminnode');
        if ($siteadmin) {
            $siteadmin->remove();
            $primary->add_node($siteadmin);
        }
    }
}
