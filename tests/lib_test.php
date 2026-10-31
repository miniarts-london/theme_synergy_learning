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

/**
 * Tests for the Brand colour setting in lib.php.
 *
 * @package    theme_synergylearning
 * @copyright  2026 Kaori Nishimura
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[\PHPUnit\Framework\Attributes\CoversFunction('theme_synergylearning_get_pre_scss')]
#[\PHPUnit\Framework\Attributes\CoversFunction('theme_synergylearning_brand_colour')]
final class lib_test extends \advanced_testcase {
    protected function setUp(): void {
        global $CFG;
        parent::setUp();
        $this->resetAfterTest();
        require_once($CFG->dirroot . '/theme/synergylearning/lib.php');
    }

    public function test_default_colour_adds_nothing(): void {
        $theme = \theme_config::load('synergylearning');
        $theme->settings->brandcolour = '';
        $this->assertStringNotContainsString('$sl-brand', theme_synergylearning_get_pre_scss($theme));
    }

    public function test_chosen_colour_is_passed_to_scss(): void {
        $theme = \theme_config::load('synergylearning');
        $theme->settings->brandcolour = '#1a3c8f';
        $this->assertStringContainsString('$sl-brand: #1a3c8f;', theme_synergylearning_get_pre_scss($theme));
    }

    public function test_unsafe_value_is_ignored(): void {
        $theme = \theme_config::load('synergylearning');
        $theme->settings->brandcolour = 'red; } body { display: none';
        $this->assertNull(theme_synergylearning_brand_colour($theme));
        $this->assertStringNotContainsString('display', theme_synergylearning_get_pre_scss($theme));
    }

    public function test_compiled_css_uses_readable_text_colour(): void {
        set_config('brandcolour', '#1a3c8f', 'theme_synergylearning');
        $css = \theme_config::load('synergylearning')->get_css_content_debug('scss', null, null);
        $this->assertStringContainsString('--sl-brand: #1a3c8f', $css);
        $this->assertStringContainsString('--sl-on-brand: #fff', $css);
    }
}
