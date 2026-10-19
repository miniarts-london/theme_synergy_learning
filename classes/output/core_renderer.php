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
     * @return array|false Template data, or false when the footer has nothing to show.
     */
    public function footer_brand(): array|false {
        $logourl = $this->get_footer_logo_url();
        $copyright = $this->get_footer_copyright();
        $contact = $this->get_footer_contact();
        $address = $this->get_footer_address();
        $links = $this->get_footer_links();
        $cookiebutton = $this->show_cookie_button();
        $content = $this->get_footer_content();

        $hascontents = $contact !== null || $address || $links || $cookiebutton || $content !== '';
        if ($logourl === null && $copyright === '' && !$hascontents) {
            return false;
        }

        return [
            'logourl' => $logourl,
            'logoalt' => $this->get_footer_logo_alt(),
            'copyright' => $copyright,
            'contact' => $contact,
            'address' => $address ? ['firstline' => $address[0], 'lines' => array_slice($address, 1)] : null,
            'links' => $links,
            'haslinks' => !empty($links),
            'cookiebutton' => $cookiebutton,
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
     * Additional footer content from the theme's HTML editor setting, for anything the other footer settings
     * don't cover.
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
     * The Contact button: its text, link and an icon matching the kind of link.
     *
     * @return array|null Template data, or null when no link is set or the link isn't usable.
     */
    public function get_footer_contact(): ?array {
        $settings = $this->page->theme->settings;
        $url = $this->footer_url((string) ($settings->contacturl ?? ''));
        if ($url === null) {
            return null;
        }

        $label = trim((string) ($settings->contactlabel ?? ''));
        if ($label === '') {
            $label = get_string('contactus', 'theme_synergylearning');
        }

        // A phone or envelope icon for tel: and mailto: links, plus hidden text saying how, for screen readers.
        $scheme = strtolower((string) strstr($url, ':', true));
        $icons = ['tel' => 'fa-phone', 'mailto' => 'fa-envelope'];

        return [
            'label' => format_string($label, true, ['context' => context_system::instance()]),
            'url' => $url,
            'icon' => $icons[$scheme] ?? 'fa-arrow-right',
            'via' => isset($icons[$scheme]) ? get_string('contactvia_' . $scheme, 'theme_synergylearning') : '',
        ];
    }

    /**
     * The address, one entry per non-empty line, formatted for output.
     *
     * @return string[]
     */
    public function get_footer_address(): array {
        $lines = preg_split('/\R/u', (string) ($this->page->theme->settings->address ?? ''));
        $lines = array_values(array_filter(array_map('trim', $lines), fn(string $line): bool => $line !== ''));

        return array_map(
            fn(string $line): string => format_string($line, true, ['context' => context_system::instance()]),
            $lines
        );
    }

    /**
     * The footer links, from "Label|URL" lines. Lines without a label or a usable URL are skipped.
     *
     * @return array[] Each with 'label' and 'url'.
     */
    public function get_footer_links(): array {
        $links = [];
        foreach (preg_split('/\R/u', (string) ($this->page->theme->settings->footerlinks ?? '')) as $line) {
            [$label, $url] = array_map('trim', explode('|', $line, 2)) + [1 => ''];
            $url = $this->footer_url($url);
            if ($label === '' || $url === null) {
                continue;
            }
            $links[] = [
                'label' => format_string($label, true, ['context' => context_system::instance()]),
                'url' => $url,
            ];
        }
        return $links;
    }

    /**
     * Whether to show the Cookie settings button. On by default.
     *
     * @return bool
     */
    public function show_cookie_button(): bool {
        return (bool) ($this->page->theme->settings->cookiebutton ?? true);
    }

    /**
     * Turn a link typed into a footer setting into a URL for output.
     *
     * Allowed: http(s) web addresses, mailto: and tel: links, paths on this site starting with "/", and "#" fragments.
     * Anything else (javascript: links, for example) is rejected, so a typing mistake can't break the footer.
     *
     * @param string $url As typed by the admin.
     * @return string|null The URL, or null when it is empty or not allowed.
     */
    protected function footer_url(string $url): ?string {
        $url = trim($url);
        if ($url === '') {
            return null;
        }
        if (str_starts_with($url, '/') && !str_starts_with($url, '//')) {
            return (new \core\url($url))->out(false);
        }
        if (str_starts_with($url, '#')) {
            return $url;
        }
        if (preg_match('/^(https?:\/\/|mailto:|tel:)/i', $url)) {
            return $url;
        }
        return null;
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
