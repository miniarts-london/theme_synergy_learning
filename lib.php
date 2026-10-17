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
    foreach (['post', 'navbar', 'frontpage', 'login', 'footer'] as $partial) {
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
 * Extra-SCSS callback, appended last. Reuses Boost's (raw SCSS setting), then puts the MyLearning banner
 * in the login page's image panel instead of Boost's default photo.
 *
 * @param theme_config $theme The theme config object.
 * @return string SCSS
 */
function theme_synergylearning_get_extra_scss(theme_config $theme): string {
    $banner = $theme->image_url('frontpage/intro-banner-1600', 'theme_synergylearning')->out(false);

    // The banner is drawn by scss/login.scss; only its URL has to come from here.
    // Boost's own photo and its "AI-generated image" label are removed.
    return theme_boost_get_extra_scss($theme) . "\n"
        . "body.pagelayout-login #page .login-layout-left {"
        . " background-image: none; --sl-login-banner: url('{$banner}'); }\n"
        . "body.pagelayout-login #page .login-layout-left::after { content: none; }\n";
}

/**
 * Serves files uploaded through this theme's settings (the footer logo).
 *
 * @param stdClass $course Course object.
 * @param stdClass|null $cm Course module object.
 * @param context $context Context.
 * @param string $filearea File area.
 * @param array $args Extra arguments.
 * @param bool $forcedownload Whether to force a download.
 * @param array $options Additional options affecting the file serving.
 * @return bool False if the file is not found; otherwise the file is sent and the script exits.
 */
function theme_synergylearning_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []) {
    if ($context->contextlevel == CONTEXT_SYSTEM && $filearea === 'footerlogo') {
        $theme = theme_config::load('synergylearning');
        // Theme files are public and safe for browsers and proxies to cache.
        if (!array_key_exists('cacheability', $options)) {
            $options['cacheability'] = 'public';
        }
        return $theme->setting_file_serve($filearea, $args, $forcedownload, $options);
    }
    send_file_not_found();
}
