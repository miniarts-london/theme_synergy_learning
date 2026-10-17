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
 * Library callbacks for the Synergy Learning theme.
 *
 * @package    theme_synergylearning
 * @copyright  2026 Kaori Nishimura
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Returns the main SCSS: Boost's default preset wrapped by this theme's variables and styles.
 *
 * Pre-SCSS runs before the preset so that Bootstrap's `!default` variables pick up the brand values.
 * Post-SCSS runs after it so that component styles can use every Bootstrap mixin and variable.
 *
 * @param theme_config $theme The theme config object.
 * @return string SCSS
 */
function theme_synergylearning_get_main_scss_content(theme_config $theme): string {
    global $CFG;

    $pre = file_get_contents(__DIR__ . '/scss/pre.scss');
    $preset = file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/default.scss');
    $post = '';
    // Each step of the build adds its own partial here (frontpage, login, footer).
    foreach (['post', 'navbar'] as $partial) {
        $post .= file_get_contents(__DIR__ . "/scss/{$partial}.scss") . "\n";
    }

    return $pre . "\n" . $preset . "\n" . $post;
}

/**
 * Pre-SCSS callback: Boost's configurable variables, then anything specific to this theme.
 *
 * @param theme_config $theme The theme config object.
 * @return string SCSS
 */
function theme_synergylearning_get_pre_scss(theme_config $theme): string {
    return theme_boost_get_pre_scss($theme);
}

/**
 * Extra-SCSS callback, appended last. Reuses Boost's, which adds the "Raw SCSS" theme setting.
 *
 * @param theme_config $theme The theme config object.
 * @return string SCSS
 */
function theme_synergylearning_get_extra_scss(theme_config $theme): string {
    return theme_boost_get_extra_scss($theme);
}
