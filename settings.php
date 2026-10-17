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
 * Theme settings: Site administration > Appearance > Themes > Synergy Learning.
 *
 * @package    theme_synergylearning
 * @copyright  2026 Kaori Nishimura
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    // Use Boost's tabbed settings page so the UI matches the parent theme.
    $settings = new theme_boost_admin_settingspage_tabs(
        'themesettingsynergylearning',
        get_string('configtitle', 'theme_synergylearning')
    );

    // General tab: keeps Boost's block restrictions, which Moodle reads from the *active* theme's settings.
    $page = new admin_settingpage(
        'theme_synergylearning_general',
        get_string('generalsettings', 'theme_synergylearning')
    );

    $setting = new admin_setting_configtext(
        'theme_synergylearning/unaddableblocks',
        get_string('unaddableblocks', 'theme_boost'),
        get_string('unaddableblocks_desc', 'theme_boost'),
        'navigation,settings,course_list',
        PARAM_TEXT
    );
    $page->add($setting);
    $settings->add($page);

    // Footer tab: the two custom settings required by the brief (logo, copyright), plus free footer content.
    $page = new admin_settingpage(
        'theme_synergylearning_footer',
        get_string('footersettings', 'theme_synergylearning')
    );

    // Footer logo. Falls back to core_admin | logocompact when empty (see core_renderer::footer_brand()).
    $setting = new admin_setting_configstoredfile(
        'theme_synergylearning/footerlogo',
        get_string('footerlogo', 'theme_synergylearning'),
        get_string('footerlogo_desc', 'theme_synergylearning'),
        'footerlogo',
        0,
        ['maxfiles' => 1, 'accepted_types' => ['.svg', '.png', '.jpg', '.jpeg', '.webp', '.gif']]
    );
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Copyright text. The © symbol is added by the footer template; empty means nothing is shown.
    $setting = new admin_setting_configtext(
        'theme_synergylearning/copyright',
        get_string('copyright', 'theme_synergylearning'),
        get_string('copyright_desc', 'theme_synergylearning'),
        '',
        PARAM_TEXT,
        50
    );
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Footer content: free HTML (address, contact details, links) shown in the footer band. Empty means nothing is shown.
    $setting = new admin_setting_confightmleditor(
        'theme_synergylearning/footercontent',
        get_string('footercontent', 'theme_synergylearning'),
        get_string('footercontent_desc', 'theme_synergylearning'),
        ''
    );
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    $settings->add($page);
}
