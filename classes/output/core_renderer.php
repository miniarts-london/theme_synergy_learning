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

namespace theme_synergylearning\output;

use context_system;

/**
 * Core renderer: extends Boost's so every Boost behaviour is kept, and adds footer branding data.
 *
 * @package    theme_synergylearning
 * @copyright  2026 Kaori Nishimura
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class core_renderer extends \theme_boost\output\core_renderer {
    /**
     * Footer branding for the footer template, read as {{#output.footer_brand}} … {{/output.footer_brand}}.
     *
     * @return array|false Template data, or false when there is no logo, copyright text or footer content.
     */
    public function footer_brand(): array|false {
        $logourl = $this->get_footer_logo_url();
        $copyright = $this->get_footer_copyright();
        $content = $this->get_footer_content();

        if ($logourl === null && $copyright === '' && $content === '') {
            return false;
        }

        return [
            'logourl' => $logourl,
            'logoalt' => $this->get_footer_logo_alt(),
            'copyright' => $copyright,
            'content' => $content,
        ];
    }

    /**
     * URL of the footer logo: the theme's own setting, else the site's compact logo, else none.
     *
     * @return string|null
     */
    public function get_footer_logo_url(): ?string {
        $url = $this->page->theme->setting_file_url('footerlogo', 'footerlogo');
        if (!empty($url)) {
            return $url;
        }

        // Fall back to Site administration > Appearance > Logos > Compact logo (core_admin | logocompact).
        $compact = $this->get_compact_logo_url(300, 300);
        return $compact ? $compact->out(false) : null;
    }

    /**
     * Copyright text from the theme setting, formatted for output and ready to follow a static "©".
     *
     * Multi-language filters are applied. A © or (c) typed by the admin is removed so that it is never doubled.
     *
     * @return string Formatted text, or an empty string when the setting is empty.
     */
    public function get_footer_copyright(): string {
        $text = trim((string) ($this->page->theme->settings->copyright ?? ''));
        $text = trim(preg_replace('/^(©|&copy;|\(c\))\s*/iu', '', $text));
        if ($text === '') {
            return '';
        }

        return format_string($text, true, ['context' => context_system::instance()]);
    }

    /**
     * Free footer content (for example an address or contact details) from the theme's HTML editor setting.
     *
     * Only site administrators can edit theme settings, and they can already add raw HTML through
     * Appearance > Additional HTML, so the content is treated as trusted: filters apply, but it is not passed
     * through the HTML cleaner. Cleaning would strip `tel:` links (used by the concept's Contact us button)
     * and ARIA attributes from icons.
     *
     * @return string Formatted HTML, or an empty string when the setting has no visible content.
     */
    public function get_footer_content(): string {
        $html = (string) ($this->page->theme->settings->footercontent ?? '');
        // An HTML editor saves "<p><br></p>" or similar when cleared, so test for visible text or images.
        if (trim(strip_tags($html, '<img>')) === '') {
            return '';
        }

        return format_text($html, FORMAT_HTML, ['context' => context_system::instance(), 'noclean' => true]);
    }

    /**
     * Alternative text for the footer logo: the site's full name.
     *
     * @return string
     */
    protected function get_footer_logo_alt(): string {
        global $SITE;
        return format_string($SITE->fullname, true, ['context' => context_system::instance(), 'escape' => false]);
    }
}
