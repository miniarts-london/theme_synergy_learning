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

/**
 * Tests for the footer branding added by the theme's core renderer.
 *
 * @package    theme_synergylearning
 * @category   test
 * @copyright  2026 Kaori Nishimura
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[\PHPUnit\Framework\Attributes\CoversClass(core_renderer::class)]
final class core_renderer_test extends \advanced_testcase {
    #[\Override]
    protected function setUp(): void {
        global $CFG;
        parent::setUp();
        $this->resetAfterTest();
        $CFG->theme = 'synergylearning';
    }

    /**
     * A renderer for a fresh page, so theme settings changed in a test are re-read.
     *
     * @return core_renderer
     */
    private function get_renderer(): core_renderer {
        $page = new \moodle_page();
        $page->set_context(\context_system::instance());
        $page->set_url('/');
        // Ask for the general (HTML) target: PHPUnit runs on the CLI, which would otherwise give the CLI renderer.
        $renderer = $page->get_renderer('core', null, RENDERER_TARGET_GENERAL);
        $this->assertInstanceOf(core_renderer::class, $renderer);
        return $renderer;
    }

    /**
     * Store a file in a setting's file area and point the setting at it.
     *
     * @param string $component
     * @param string $filearea
     * @param string $setting
     */
    private function add_setting_file(string $component, string $filearea, string $setting): void {
        $syscontext = \context_system::instance();
        get_file_storage()->create_file_from_string([
            'contextid' => $syscontext->id,
            'component' => $component,
            'filearea' => $filearea,
            'itemid' => 0,
            'filepath' => '/',
            'filename' => 'logo.png',
        ], 'not really a png');
        set_config($setting, '/logo.png', $component);
        theme_reset_all_caches();
    }

    public function test_footer_brand_is_false_when_every_footer_setting_is_empty(): void {
        set_config('cookiebutton', 0, 'theme_synergylearning');
        $this->assertFalse($this->get_renderer()->footer_brand());
    }

    public function test_cookie_button_is_on_by_default(): void {
        $renderer = $this->get_renderer();
        $this->assertTrue($renderer->show_cookie_button());

        $html = $renderer->render_from_template('theme_boost/footer', ['output' => $renderer]);
        $this->assertStringContainsString('data-action="sl-cookie-settings"', $html);
    }

    public function test_copyright_is_empty_when_setting_is_empty_or_whitespace(): void {
        set_config('copyright', '   ', 'theme_synergylearning');
        $this->assertSame('', $this->get_renderer()->get_footer_copyright());
    }

    public function test_copyright_is_formatted_and_typed_symbol_is_not_doubled(): void {
        set_config('copyright', '© 2026 Nikon <script>x</script>& Co', 'theme_synergylearning');
        $copyright = $this->get_renderer()->get_footer_copyright();

        // The template prepends ©, so a typed one is removed; tags are stripped and & is escaped.
        $this->assertStringStartsWith('2026 Nikon', $copyright);
        $this->assertStringNotContainsString('<script>', $copyright);
        $this->assertStringContainsString('&amp; Co', $copyright);
    }

    public function test_footer_logo_falls_back_to_compact_logo(): void {
        $this->assertNull($this->get_renderer()->get_footer_logo_url());

        $this->add_setting_file('core_admin', 'logocompact', 'logocompact');
        $url = $this->get_renderer()->get_footer_logo_url();
        $this->assertStringContainsString('/core_admin/logocompact/', $url);
    }

    public function test_footer_logo_setting_takes_precedence_over_compact_logo(): void {
        $this->add_setting_file('core_admin', 'logocompact', 'logocompact');
        $this->add_setting_file('theme_synergylearning', 'footerlogo', 'footerlogo');

        $url = $this->get_renderer()->get_footer_logo_url();
        $this->assertStringContainsString('/theme_synergylearning/footerlogo/', $url);
    }

    public function test_footer_brand_renders_static_copyright_symbol(): void {
        set_config('copyright', '2026 Nikon Optical Ltd.', 'theme_synergylearning');
        $renderer = $this->get_renderer();

        $html = $renderer->render_from_template('theme_boost/footer', ['output' => $renderer]);
        $this->assertStringContainsString('<p class="sl-footer__copyright">&copy; 2026 Nikon Optical Ltd.</p>', $html);
    }

    public function test_footer_renders_no_copyright_markup_when_empty(): void {
        $this->add_setting_file('core_admin', 'logocompact', 'logocompact');
        $renderer = $this->get_renderer();

        $html = $renderer->render_from_template('theme_boost/footer', ['output' => $renderer]);
        $this->assertStringContainsString('sl-footer__logo', $html);
        $this->assertStringNotContainsString('sl-footer__copyright', $html);
        $this->assertStringNotContainsString('&copy;', $html);
    }

    public function test_footer_content_is_empty_for_a_cleared_editor(): void {
        set_config('cookiebutton', 0, 'theme_synergylearning');
        set_config('footercontent', '<p><br></p>', 'theme_synergylearning');
        $this->assertSame('', $this->get_renderer()->get_footer_content());
        $this->assertFalse($this->get_renderer()->footer_brand());
    }

    public function test_footer_content_keeps_admin_html_and_renders_in_the_footer(): void {
        // Admin-only, trusted HTML: tel: links and ARIA attributes must survive (the cleaner would strip them).
        set_config('footercontent', '<p>3 Tanners Drive</p><a class="btn btn-primary" href="tel:+441632960000">'
            . '<i class="fa fa-phone" aria-hidden="true"></i> Contact us</a>', 'theme_synergylearning');
        $renderer = $this->get_renderer();

        $content = $renderer->get_footer_content();
        $this->assertStringContainsString('3 Tanners Drive', $content);
        $this->assertStringContainsString('href="tel:+441632960000"', $content);
        $this->assertStringContainsString('aria-hidden="true"', $content);

        $html = $renderer->render_from_template('theme_boost/footer', ['output' => $renderer]);
        $this->assertStringContainsString('class="sl-footer__content"', $html);
        $this->assertStringContainsString('3 Tanners Drive', $html);
    }

    public function test_contact_button_gets_an_icon_and_hidden_text_for_phone_and_email(): void {
        set_config('contacturl', 'tel:+441632960000', 'theme_synergylearning');
        $contact = $this->get_renderer()->get_footer_contact();
        $this->assertSame('tel:+441632960000', $contact['url']);
        $this->assertSame('fa-phone', $contact['icon']);
        $this->assertSame('by phone', $contact['via']);
        $this->assertSame('Contact us', $contact['label']);

        set_config('contacturl', 'mailto:help@example.com', 'theme_synergylearning');
        set_config('contactlabel', 'Email us', 'theme_synergylearning');
        $contact = $this->get_renderer()->get_footer_contact();
        $this->assertSame('fa-envelope', $contact['icon']);
        $this->assertSame('Email us', $contact['label']);
    }

    public function test_contact_button_resolves_site_paths_and_rejects_unsafe_links(): void {
        global $CFG;
        set_config('contacturl', '/user/contactsitesupport.php', 'theme_synergylearning');
        $contact = $this->get_renderer()->get_footer_contact();
        $this->assertSame($CFG->wwwroot . '/user/contactsitesupport.php', $contact['url']);
        $this->assertSame('fa-arrow-right', $contact['icon']);
        $this->assertSame('', $contact['via']);

        foreach (['javascript:alert(1)', '//evil.example.com', 'www.example.com', ''] as $url) {
            set_config('contacturl', $url, 'theme_synergylearning');
            $this->assertNull($this->get_renderer()->get_footer_contact(), $url);
        }
    }

    public function test_address_is_split_into_lines_without_html(): void {
        set_config('address', "Nikon Optical Ltd\r\n\r\n  3 Tanners Drive  \n<b>Bucks</b> & Co", 'theme_synergylearning');
        $renderer = $this->get_renderer();
        $address = $renderer->get_footer_address();
        $this->assertSame(['Nikon Optical Ltd', '3 Tanners Drive', 'Bucks &amp; Co'], $address);

        $html = $renderer->render_from_template('theme_boost/footer', ['output' => $renderer]);
        $this->assertStringContainsString('<strong>Nikon Optical Ltd</strong><br>3 Tanners Drive<br>', $html);
        $this->assertStringNotContainsString('<b>Bucks</b>', $html);
    }

    public function test_footer_links_are_parsed_and_bad_lines_skipped(): void {
        global $CFG;
        set_config('footerlinks', implode("\n", [
            'Policies|/admin/tool/policy/viewall.php',
            'Nikon | https://www.nikon.com ',
            'No URL here',
            '|/missing-label.php',
            'Bad|javascript:alert(1)',
            '',
        ]), 'theme_synergylearning');
        $renderer = $this->get_renderer();

        $this->assertSame([
            ['label' => 'Policies', 'url' => $CFG->wwwroot . '/admin/tool/policy/viewall.php'],
            ['label' => 'Nikon', 'url' => 'https://www.nikon.com'],
        ], $renderer->get_footer_links());

        $html = $renderer->render_from_template('theme_boost/footer', ['output' => $renderer]);
        $this->assertStringContainsString('class="sl-footer__links', $html);
        $this->assertStringContainsString('>Nikon</a>', $html);
        $this->assertStringNotContainsString('javascript:', $html);
    }

    public function test_empty_parts_render_no_markup(): void {
        set_config('copyright', '2026 Nikon Optical Ltd.', 'theme_synergylearning');
        set_config('cookiebutton', 0, 'theme_synergylearning');
        $renderer = $this->get_renderer();
        $html = $renderer->render_from_template('theme_boost/footer', ['output' => $renderer]);

        $this->assertStringContainsString('sl-footer__copyright', $html);
        $markers = ['sl-footer__contact', 'sl-footer__address', 'sl-footer__links', 'data-action="sl-cookie-settings"',
            'sl-footer__content'];
        foreach ($markers as $marker) {
            $this->assertStringNotContainsString($marker, $html);
        }
    }
}
