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

    // Moodle 5.3 ships with "Enable Home" off and "Force users to log in" on; either one hides the site home,
    // and with it this theme's landing page. Tell the admin, with links to both settings.
    if (empty($CFG->enablemyhome) || !empty($CFG->forcelogin)) {
        $page->add(new admin_setting_description(
            'theme_synergylearning/sitehomenotice',
            '',
            $OUTPUT->notification(get_string('sitehomenotice', 'theme_synergylearning', [
                'enablemyhome' => (new \core\url('/admin/settings.php', ['section' => 'navigation']))->out(false),
                'forcelogin' => (new \core\url('/admin/settings.php', ['section' => 'loginsettings']))->out(false),
            ]), \core\output\notification::NOTIFY_WARNING, false)
        ));
    }
    $setting = new admin_setting_configtext(
        'theme_synergylearning/unaddableblocks',
        get_string('unaddableblocks', 'theme_boost'),
        get_string('unaddableblocks_desc', 'theme_boost'),
        'navigation,settings,course_list',
        PARAM_TEXT
    );
    $page->add($setting);
    $settings->add($page);

    // Footer tab: the two custom settings required by the brief (logo, copyright), then the footer contents.
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

    // Footer contents, one setting per part so the markup and styling stay consistent whatever is typed.
    // Each part is hidden when its setting is empty (see core_renderer::footer_brand()).
    $page->add(new admin_setting_heading(
        'theme_synergylearning/footercontentsheading',
        get_string('footercontentsheading', 'theme_synergylearning'),
        get_string('footercontentsheading_desc', 'theme_synergylearning')
    ));

    // Contact us button: a label and a link (web address, site path, mailto: or tel:).
    $setting = new admin_setting_configtext(
        'theme_synergylearning/contactlabel',
        get_string('contactlabel', 'theme_synergylearning'),
        get_string('contactlabel_desc', 'theme_synergylearning'),
        get_string('contactus', 'theme_synergylearning'),
        PARAM_TEXT,
        30
    );
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    $setting = new admin_setting_configtext(
        'theme_synergylearning/contacturl',
        get_string('contacturl', 'theme_synergylearning'),
        get_string('contacturl_desc', 'theme_synergylearning'),
        '',
        PARAM_RAW_TRIMMED,
        50
    );
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Address: plain text, one line per address line.
    $setting = new admin_setting_configtextarea(
        'theme_synergylearning/address',
        get_string('address', 'theme_synergylearning'),
        get_string('address_desc', 'theme_synergylearning'),
        '',
        PARAM_TEXT,
        50,
        6
    );
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Footer links: one per line as "Label|URL", the same format as Moodle's Custom menu items.
    $setting = new admin_setting_configtextarea(
        'theme_synergylearning/footerlinks',
        get_string('footerlinks', 'theme_synergylearning'),
        get_string('footerlinks_desc', 'theme_synergylearning'),
        '',
        PARAM_RAW,
        50,
        6
    );
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Cookie settings button, which opens the theme's cookie preferences pop-up.
    $setting = new admin_setting_configcheckbox(
        'theme_synergylearning/cookiebutton',
        get_string('cookiebutton', 'theme_synergylearning'),
        get_string('cookiebutton_desc', 'theme_synergylearning'),
        1
    );
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Additional footer content: free HTML for anything the settings above don't cover. Empty means nothing is shown.
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
