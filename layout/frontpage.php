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
 * Site home layout: Boost's drawers layout plus the MyLearning landing sections.
 *
 * The Boost context is built exactly as in theme/boost/layout/drawers.php so that blocks, drawers,
 * editing mode, the secondary navigation and the site home content all keep working.
 *
 * @package    theme_synergylearning
 * @copyright  2026 Kaori Nishimura
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/behat/lib.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->libdir . '/authlib.php');

// Boost drawers context (kept in step with theme/boost/layout/drawers.php).
$addblockbutton = $OUTPUT->addblockbutton();

if (isloggedin()) {
    $blockdraweropen = (get_user_preferences('drawer-open-block') == true);
} else {
    $blockdraweropen = false;
}
if (defined('BEHAT_SITE_RUNNING') && get_user_preferences('behat_keep_drawer_closed') != 1) {
    $blockdraweropen = true;
}

$extraclasses = ['uses-drawers', 'sl-landing'];

$blockshtml = $OUTPUT->blocks('side-pre');
$hasblocks = (strpos($blockshtml, 'data-block=') !== false || !empty($addblockbutton));
if (!$hasblocks) {
    $blockdraweropen = false;
}

$bodyattributes = $OUTPUT->body_attributes($extraclasses);
$forceblockdraweropen = $OUTPUT->firstview_fakeblocks();

$secondarynavigation = false;
$overflow = '';
if ($PAGE->has_secondary_navigation()) {
    $tablistnav = $PAGE->has_tablist_secondary_navigation();
    $moremenu = new \core\navigation\output\more_menu($PAGE->secondarynav, 'nav-tabs', true, $tablistnav);
    $secondarynavigation = $moremenu->export_for_template($OUTPUT);
    $overflowdata = $PAGE->secondarynav->get_overflow_menu_data();
    if (!is_null($overflowdata)) {
        $selectmenu = new \core\output\select_menu('tertiarynavigation', $overflowdata->urls, $overflowdata->selected);
        $selectmenu->set_label($overflowdata->label, $overflowdata->labelattributes);
        $overflow = $selectmenu->export_for_template($OUTPUT);
    }
}

$primary = new core\navigation\output\primary($PAGE);
$renderer = $PAGE->get_renderer('core');
$primarymenu = $primary->export_for_template($renderer);
$buildregionmainsettings = !$PAGE->include_region_main_settings_in_header_actions() && !$PAGE->has_secondary_navigation();
$regionmainsettingsmenu = $buildregionmainsettings ? $OUTPUT->region_main_settings_menu() : false;

$header = $PAGE->activityheader;
$headercontent = $header->export_for_template($renderer);

$syscontext = context_system::instance();

$templatecontext = [
    'sitename' => format_string($SITE->shortname, true, ['context' => context_course::instance(SITEID), 'escape' => false]),
    'output' => $OUTPUT,
    'sidepreblocks' => $blockshtml,
    'hasblocks' => $hasblocks,
    'bodyattributes' => $bodyattributes,
    'blockdraweropen' => $blockdraweropen,
    'primarymoremenu' => $primarymenu['moremenu'],
    'secondarymoremenu' => $secondarynavigation ?: false,
    'mobileprimarynav' => $primarymenu['mobileprimarynav'],
    'usermenu' => $primarymenu['user'],
    'langmenu' => $primarymenu['lang'],
    'forceblockdraweropen' => $forceblockdraweropen,
    'regionmainsettingsmenu' => $regionmainsettingsmenu,
    'hasregionmainsettingsmenu' => !empty($regionmainsettingsmenu),
    'overflow' => $overflow,
    'headercontent' => $headercontent,
    'addblockbutton' => $addblockbutton,
    'showfullheader' => $PAGE->user_is_editing()
        || has_capability('moodle/course:update', context_course::instance(SITEID)),
];

// MyLearning landing sections.
$isloggedin = isloggedin() && !isguestuser();
$imageurl = fn(string $name): string => $OUTPUT->image_url($name, 'theme_synergylearning')->out(false);

// Hero: log in form for visitors, a welcome back panel for signed-in users.
$hero = [
    'sitefullname' => format_string($SITE->fullname, true, ['context' => $syscontext, 'escape' => false]),
    'isloggedin' => $isloggedin,
    'banner' => [
        'src' => $imageurl('frontpage/intro-banner-1000'),
        'srcset' => implode(', ', array_map(
            fn(int $w): string => $imageurl("frontpage/intro-banner-{$w}") . " {$w}w",
            [640, 1000, 1600, 2400]
        )),
    ],
];

if ($isloggedin) {
    $hero['firstname'] = format_string($USER->firstname, true, ['context' => $syscontext, 'escape' => false]);
    $hero['dashboardurl'] = (new \core\url('/my/'))->out(false);
    $hero['catalogueurl'] = (new \core\url('/course/index.php'))->out(false);
} else {
    $hero['loginurl'] = get_login_url();
    $hero['logintoken'] = \core\session\manager::get_login_token();
    $hero['forgotpasswordurl'] = empty($CFG->forgottenpasswordurl)
        ? (new \core\url('/login/forgot_password.php'))->out(false)
        : $CFG->forgottenpasswordurl;

    // The Contact us link goes to the site's support page or form when visitors are allowed to use it
    // (Server > Support contact > Support availability). Otherwise it jumps to the contact details
    // in the footer, as on the static page.
    if (isset($CFG->supportavailability) && $CFG->supportavailability == CONTACT_SUPPORT_ANYONE) {
        $hero['contacturl'] = !empty($CFG->supportpage)
            ? $CFG->supportpage
            : (new \core\url('/user/contactsitesupport.php'))->out(false);
    } else {
        $hero['contacturl'] = '#contact';
    }

    // Single sign-on buttons (OAuth 2, etc.) configured on the site, as on the standard login page.
    $idps = \auth_plugin_base::get_identity_providers(get_enabled_auth_plugins());
    $hero['identityproviders'] = \auth_plugin_base::prepare_identity_providers_for_output($idps, $OUTPUT);
    $hero['hasidentityproviders'] = !empty($hero['identityproviders']);
}

// Catalogue: the top-level course categories this user can see.
// Each card's image is, in order of preference:
// 1. the first image in the category's description (Edit category > Description), so admins can upload one per category;
// 2. the theme's built-in icon matched on the category ID number ($iconnames below);
// 3. a generic icon.
$iconnames = ['product', 'business-development', 'optics', 'cpd', 'business-management'];
$fs = get_file_storage();
$categories = [];
foreach (core_course_category::top()->get_children() as $category) {
    $iconurl = null;
    $catcontext = \core\context\coursecat::instance($category->id);
    $images = array_filter(
        $fs->get_area_files($catcontext->id, 'coursecat', 'description', 0, 'filepath, filename', false),
        fn($file) => str_starts_with((string) $file->get_mimetype(), 'image/'),
    );
    if ($images) {
        // Prefer the image that comes first in the description text; files carry no order of their own.
        $image = reset($images);
        if (preg_match('~@@PLUGINFILE@@(/[^"\'?#]+)~', (string) $category->description, $match)) {
            $wanted = rawurldecode($match[1]);
            foreach ($images as $candidate) {
                if ($candidate->get_filepath() . $candidate->get_filename() === $wanted) {
                    $image = $candidate;
                    break;
                }
            }
        }
        // Category description files are served by core without an item id in the URL.
        $iconurl = \core\url::make_pluginfile_url(
            $catcontext->id,
            'coursecat',
            'description',
            null,
            $image->get_filepath(),
            $image->get_filename(),
        )->out(false);
    }
    $customicon = $iconurl !== null;
    if (!$customicon) {
        $icon = in_array($category->idnumber, $iconnames, true) ? $category->idnumber : 'default';
        $iconurl = $imageurl('categories/' . $icon);
    }
    $name = $category->get_formatted_name();
    $categories[] = [
        'id' => $category->id,
        'name' => $name,
        'url' => (new \core\url('/course/index.php', ['categoryid' => $category->id]))->out(false),
        'iconurl' => $iconurl,
        'customicon' => $customicon,
        'srlabel' => get_string('viewcoursesin', 'theme_synergylearning', $name),
    ];
}

$templatecontext['landing'] = [
    'hero' => $hero,
    'catalogue' => [
        'categories' => $categories,
        'hascategories' => !empty($categories),
        'allurl' => (new \core\url('/course/index.php'))->out(false),
    ],
    'about' => [
        'poster' => $imageurl('frontpage/video-poster'),
        // Drop the video into the theme's pix/frontpage folder (or point this at a media URL) to enable playback.
        'videourl' => (new \core\url('/theme/synergylearning/pix/frontpage/about-nikon-lenswear-uk.mp4'))->out(false),
    ],
];

echo $OUTPUT->render_from_template('theme_synergylearning/frontpage', $templatecontext);
