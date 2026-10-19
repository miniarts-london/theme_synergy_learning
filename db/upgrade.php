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

/**
 * Upgrade steps for theme_synergylearning.
 *
 * @package    theme_synergylearning
 * @copyright  2026 Kaori Nishimura
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Run the upgrade steps between the installed version and this one.
 *
 * @param int $oldversion The version being upgraded from.
 * @return bool
 */
function xmldb_theme_synergylearning_upgrade($oldversion) {
    if ($oldversion < 2026101900) {
        // Version 2 adds a "Show Cookie settings button" setting, on by default. Sites that already put a Cookie
        // settings button in the free Footer content box would show it twice, so start them with the new one off.
        $content = (string) get_config('theme_synergylearning', 'footercontent');
        if (str_contains($content, 'btn-sl-cookie') || str_contains($content, 'sl-cookie-settings')) {
            set_config('cookiebutton', 0, 'theme_synergylearning');
        }

        upgrade_plugin_savepoint(true, 2026101900, 'theme', 'synergylearning');
    }

    return true;
}
